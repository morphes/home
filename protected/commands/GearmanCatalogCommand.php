<?php

/**
 * Поддержание актуальности рейтинга спецов
 *
 * @author alexsh
 */
class GearmanCatalogCommand extends CConsoleCommand
{
    // Список полей, которые выбираются из базы, и сохраняются затем в csv
    private $exportColumns = array(
        'id' => 'PID',
        'barcode' => 'Артикул',
        'name' => 'Название',
        'category_id' => 'Категория',
        'price' => 'Цена'
    );

    public function actionWorker()
    {
        $worker = Yii::app()->gearman->worker();
        $worker->addFunction('catalogExportCsv', array($this, 'exportCsv'));
        $worker->addFunction('catalogImportCsv', array($this, 'importCsv'));
        $worker->addFunction('catCsv:exportForVendors', array($this, 'exportForVendors'));
        $worker->addFunction('catCsv:importStore', array($this, 'importStore'));
        $worker->addFunction('catCsv:exportStore', array($this, 'exportStore'));
        $worker->addFunction('catXml:importXml', array($this, 'importXml'));
        $worker->addFunction('assign_products', array($this, 'assignProducts'));

        while ($worker->work()) {
            if (GEARMAN_SUCCESS != $worker->returnCode()) {
                echo "Worker failed: " . $worker->error();
            }
            echo "\n";
        }
    }


    /* ---------------------------------------------------------------------------------------------------
     *  ОБЩИЕ ФУНКЦИИ ДЛЯ ВСЕХ ИМПОРТОВ
     * ---------------------------------------------------------------------------------------------------
     */

    /**
     * Устанавливает для задачи параметры прогресса
     * @param $taskId integer ID задачи на экспорт
     * @param $totalItems integer Общее кол-во элементов, которые надо экспортировать в csv.
     * @param $doneItems integer Уже обработанное кол-во элементов.
     * @param $file string Путь к экспортированному файлу относительно корня сайта
     */
    private function setProgressParams($taskId, $totalItems, $doneItems, $status, $file = null)
    {
        $params = array(
            'status' => $status,
            'progress' => serialize(array('totalItems' => $totalItems, 'doneItems' => $doneItems)),
            'update_time' => time(),
        );
        if (!is_null($file))
            $params['file'] = $file;

        CatCsv::model()->updateByPk($taskId, $params);
    }

    /**
     * Сохраняет набор данных в файл.
     * @param $fp указатель на файл, в который будем сохранять
     * @param $result Массив массивов данных для сохранения
     */
    private function saveCsvRow($fp, $result)
    {

        foreach ($result as $d) {
            // Конвертим в кодировку cp1251
            $d_cp1251 = array_map(function ($n) {
                return iconv('UTF-8', 'cp1251//TRANSLIT', $n);
            }, $d);
            fwrite($fp, implode(';', $d_cp1251) . "\r\n");
        }
    }

    /**
     * Возвращает небольшую порцию данных по товарам
     * @param $columns array Ассоциативный массив полей для экспорта
     * @param $vendor_ids array Массив ID производителей, к которым относятся товары
     * @param $limit integer Количество элементов, которое нужно взять
     * @param $offset integer Смщение в выборке
     * @return array|null Ассоциативный массив данных
     */
    private function getProductsVendorRangeData($columns, $vendor_ids, $limit, $offset)
    {
        $result = Yii::app()->db->createCommand(array(
            'select' => implode(',', array_keys($columns)),
            'from' => 'cat_product',
            'where' => 'status = :st AND vendor_id IN (' . implode(',', $vendor_ids) . ')',
            'params' => array(':st' => Product::STATUS_ACTIVE),
            'limit' => $limit,
            'offset' => $offset
        ))->queryAll();

        return $result;
    }

    private function getProductsStoreRangeData($columns, $store_id, $limit, $offset)
    {
        $result = array();

        // Получаем список всех ID товаров через запятую, который мы используем в запросе
        // на получение данных по товару
        $storePrice = Yii::app()->db->createCommand()
            ->select("product_id")
            ->from("cat_store_price")
            ->where('store_id = :sid AND by_vendor = 0', array(':sid' => $store_id))
            ->limit($limit)
            ->offset($offset)
            ->queryAll();
        $ids = array();
        foreach ($storePrice as $item) {
            $ids[] = $item['product_id'];
        }

        // Получаем данные по товарам
        if ($ids) {
            $result = Yii::app()->db->createCommand(array(
                'select' => implode(',', array_keys($columns)),
                'from' => 'cat_product',
                'where' => 'status = :st AND id IN (' . implode(',', $ids) . ')',
                'params' => array(':st' => Product::STATUS_ACTIVE),
            ))->queryAll();
        }

        return $result;
    }

    /*
     * ---------------------------------------------------------------------------------------------------
     *  Э К С П О Р Т  по производителям
     * ---------------------------------------------------------------------------------------------------
     */

    public function exportForVendors(GearmanJob $job)
    {
        echo date('[Y-m-d H:i:s] ');

        // Хак для того, чтобы восстановить оборванное соединение с базой данных
        Yii::app()->db->active = false;
        Yii::app()->db->active = true;

        Yii::import('application.modules.catalog.models.*');
        Yii::import('application.components.interfaces.*');

        $data = $job->workload();
        try {

            $params = unserialize($data);

            // Директория, в которую будут складывать экспортируемые файлы
            $basePath = Yii::app()->basePath . '/..';
            $folder = '/uploads/protected/catalog/exportForVendors';
            // Имя csv файла
            $fileName = date('Y-m-d_hi') . '_' . rand(1, 100) . '.csv';
            // Список колонок для экспортного csv файла, которые берутся из БД
            $exportColumns = array(
                'id' => 'PID',
                'barcode' => 'Артикул',
                'vendor_id' => 'Производитель',
                'name' => 'Название',
                'category_id' => 'Категория',
            );
            // Список дополнительных колонок
            $exportColumnsEx = array(
                'price' => 'Цена',
                'url' => 'URL',
            );
            // Ограничение на одновременное количество строк, выбираемых из базы для экспорта
            $selectLimit = 1;


            // Проверяем есть ли в базе задача на эскпорт.
            $task = CatCsv::model()->findByPk($params['task_id']);
            if (!$task)
                throw new CHttpException(500, 'Задача на экспорт товаров по производителям была не найдена.');

            // Получаем данные по задаче
            $taskParams = unserialize($task->data);

            // Считаем кол-во товаров, которые предстоит сохранить в файл
            $count = Yii::app()->db->createCommand(array(
                'select' => 'COUNT(*)',
                'from' => 'cat_product',
                'where' => 'status = :st AND vendor_id IN (' . implode(",", $taskParams['vendor_ids']) . ')',
                'params' => array(':st' => Product::STATUS_ACTIVE)
            ))->queryScalar();

            if ($count > 0) {
                // Ставим статус "В процессе" и указываем общее кол-во элементов для обработки
                $this->setProgressParams($task->id, $count, 0, CatCsv::STATUS_IN_PROGRESS);

                // Создаем файл для записи
                if (!file_exists($basePath . $folder))
                    mkdir($basePath . $folder, 0755, true);

                $fp = fopen($basePath . $folder . '/' . $fileName, 'w');

                // Сохраняем строку с названиями полей
                $this->saveCsvRow($fp, array(array_values($exportColumns + $exportColumnsEx)));


                $i = 0;
                while (($result = $this->getProductsVendorRangeData($exportColumns, $taskParams['vendor_ids'], $selectLimit, $i * $selectLimit))) {
                    $this->correctFieldsForVendors($result);

                    // Сохраняем полученную порцию данных в файл
                    $this->saveCsvRow($fp, $result);

                    // Обновляем текущий прогресс
                    $this->setProgressParams($task->id, $count, $i * $selectLimit, CatCsv::STATUS_IN_PROGRESS);

                    $i++;
                }


                // Закрываем файл, после записи всех строк
                fclose($fp);

                // Отмечаем, что процесс завершился
                $this->setProgressParams($task->id, $count, $count, CatCsv::STATUS_FINISHED, $folder . '/' . $fileName);


                echo "$count products has been exported for vendor #" . implode(', #', $taskParams['vendor_ids']);

            } else {

                $this->setProgressParams($task->id, 0, 0, CatCsv::STATUS_FINISHED);

                echo "Nothing to export for vendor #" . implode(', #', $taskParams['vendor_ids']);
            }


        } catch (Exception $e) {
            echo 'Error data: ' . print_r($params, true);
            echo mysql_error();
            echo $e->getMessage();
        }
    }


    /*
     * ---------------------------------------------------------------------------------------------------
     *  Э К С П О Р Т  товаров привязанных к магазину
     * ---------------------------------------------------------------------------------------------------
     */

    public function exportStore(GearmanJob $job)
    {
        echo date('[Y-m-d H:i:s] ');

        // Хак для того, чтобы восстановить оборванное соединение с базой данных
        Yii::app()->db->active = false;
        Yii::app()->db->active = true;

        Yii::import('application.modules.catalog.models.*');
        Yii::import('application.components.interfaces.*');

        $data = $job->workload();
        try {

            $params = unserialize($data);

            // Директория, в которую будут складывать экспортируемые файлы
            $basePath = Yii::app()->basePath . '/..';
            $folder = '/uploads/protected/catalog/exportStore';
            // Имя csv файла
            $fileName = date('Y-m-d_hi') . '_' . rand(1, 100) . '.csv';
            // Список колонок для экспортного csv файла, которые берутся из БД
            $exportColumns = array(
                'id' => 'PID',
                'barcode' => 'Артикул',
                'vendor_id' => 'Производитель',
                'name' => 'Название',
                'category_id' => 'Категория',
            );
            // Список дополнительных колонок
            $exportColumnsEx = array(
                'price' => 'Цена',
                'url' => 'URL',
            );
            // Ограничение на одновременное количество строк, выбираемых из базы для экспорта
            $selectLimit = 100;


            // Проверяем есть ли в базе задача на эскпорт.
            $task = CatCsv::model()->findByPk($params['task_id']);
            if (!$task)
                throw new CHttpException(500, 'Задача на экспорт товаров по производителям была не найдена.');

            // Получаем данные по задаче
            $taskParams = unserialize($task->data);

            // Считаем кол-во товаров, которые предстоит сохранить в файл
            $count = Yii::app()->db->createCommand(array(
                'select' => 'COUNT(*)',
                'from' => 'cat_store_price',
                'where' => 'store_id = :sid AND by_vendor = 0',
                'params' => array(':sid' => (int)$taskParams['store_id'])
            ))->queryScalar();

            if ($count > 0) {
                // Ставим статус "В процессе" и указываем общее кол-во элементов для обработки
                $this->setProgressParams($task->id, $count, 0, CatCsv::STATUS_IN_PROGRESS);

                // Создаем файл для записи
                if (!file_exists($basePath . $folder))
                    mkdir($basePath . $folder, 0755, true);

                $fp = fopen($basePath . $folder . '/' . $fileName, 'w');

                // Сохраняем строку с названиями полей
                $this->saveCsvRow($fp, array(array_values($exportColumns + $exportColumnsEx)));


                $i = 0;
                while ($result = $this->getProductsStoreRangeData($exportColumns, $taskParams['store_id'], $selectLimit, $i * $selectLimit)) {
                    $this->correctFieldsForStore($result, $taskParams['store_id']);

                    // Сохраняем полученную порцию данных в файл
                    $this->saveCsvRow($fp, $result);

                    // Обновляем текущий прогресс
                    $this->setProgressParams($task->id, $count, $i * $selectLimit, CatCsv::STATUS_IN_PROGRESS);

                    $i++;
                }


                // Закрываем файл, после записи всех строк
                fclose($fp);

                // Отмечаем, что процесс завершился
                $this->setProgressParams($task->id, $count, $count, CatCsv::STATUS_FINISHED, $folder . '/' . $fileName);


                echo "$count products has been exported for store #" . $taskParams['store_id'];

            } else {

                $this->setProgressParams($task->id, 0, 0, CatCsv::STATUS_FINISHED);

                echo "Nothing to export for store #" . $taskParams['store_id'];
            }


        } catch (Exception $e) {
            echo 'Error data: ' . print_r($params, true);
            echo mysql_error();
            echo $e->getMessage();
        }
    }

    public function importXml(GearmanJob $job)
    {
        echo date('[Y-m-d H:i:s] ');

        // Хак для того, чтобы восстановить оборванное соединение с базой данных
        Yii::app()->dbcatalog2->active = false;
        Yii::app()->dbcatalog2->active = true;

        Yii::import('application.modules.catalog2.models.*');
        Yii::import('application.components.interfaces.*');
        Yii::import('application.components.XmlParser.*');

        $data = $job->workload();

        try {
            $params = unserialize($data);

            // Проверяем есть ли в базе задача на импорт.
            /** @var CatXml $task */
            $task = CatXml::model()->findByPk($params['task_id']);
            if (!$task) {
                throw new CException('Xml import task not found');
            }

            echo "Begin xml import for store #$task->store_id\n";
$task->status = CatXml::STATUS_IN_PROGRESS;
            $task->save();
            $filePath = Yii::app()->basePath . '/../' . $task->file;
            $parserId = Yii::app()->dbcatalog2->createCommand()->select('xml_parser_id')->from('cat_store')
                ->where('id=:id', [':id' => $task->store_id])->queryScalar();
            if (!$parserId) {
                throw new CException('Store has not parser id');
            }

            $oldTasks = CatXml::model()->findAll('store_id=:sid AND (status=:new OR status=:progress)', [
                ':sid' => $task->store_id,
                ':new' => CatXml::STATUS_NEW,
                ':progress' => CatXml::STATUS_IN_PROGRESS
            ]);

            /** @var CatXml $oldTask */
            foreach ($oldTasks as $oldTask) {
                $oldTask->status = CatXml::STATUS_CANCELED;
                $oldTask->save(false, ['status']);
            }

            /** @var XmlParserInterface $parser */
            $parser = XmlParser::build($parserId, $filePath);
	    try {
        	$parser->parse($task->store_id);
            } catch (Exception $e) {
		echo $task->store_id . ' skipped: ' . $e->getMessage();
	    }
	    $task->status = CatXml::STATUS_FINISHED;
            $task->save(false, ['status']);

        } catch (Exception $e) {
            echo 'Error data: ' . print_r($params, true);
            echo $e->getMessage();
        }
    }

    /**
     * Обновляет цены привязанных товаров к магазину.
     *
     * @param GearmanJob $job
     * @throws CHttpException
     * @throws Exception
     */
    public function importStore(GearmanJob $job)
    {
        echo date('[Y-m-d H:i:s] ');

        // Хак для того, чтобы восстановить оборванное соединение с базой данных
        Yii::app()->db->active = false;
        Yii::app()->db->active = true;

        Yii::import('application.modules.catalog.models.*');
        Yii::import('application.components.interfaces.*');

        $data = $job->workload();
        try {
            $params = unserialize($data);

            $basePath = Yii::app()->basePath . '/..';

            // Проверяем есть ли в базе задача на импорт.
            $task = CatCsv::model()->findByPk($params['task_id']);
            if (!$task)
                throw new CHttpException(500, 'Задача на импорт цен товаров к магазину, не найдена');

            echo "Begin import for store #$task->item_id\n";

            if (($handle = fopen($basePath . '/' . $task->file, "r")) !== false) {
                // Обновляем данные о том, что мы начали обработку
                $progress = unserialize($task->progress);
                $this->setProgressParams($task->id, $progress['totalItems'], 0, CatCsv::STATUS_IN_PROGRESS);


                // Кол-во продуктов, которым обновлена цена
                $countUpdate = 0;
                $countUpdateError = 0;


                // Читаем csv и обновляем цены.
                while (($line = fgets($handle)) !== false) {
                    $values = explode(';', $line);

                    if ($values[0] == 'pid' || $values[0] == 'PID')
                        continue;

                    $values = array_map(function ($n) {
                        return iconv('cp1251', 'UTF-8', $n);
                    }, $values);

                    // Идентификатор товара
                    $pid = (int)$values[0];

                    // $values[1] - лежит Артикул

                    // Цена товара
                    $price = (float)$values[5];
                    // Название товара
                    $product_name = $values[3];
                    // Производитель
                    $vendor_name = $values[2];
                    // Категория
                    $category_name = $values[4];


                    // Если товар есть существует
                    $prod = Product::model()->findByPk(($pid));

                    if ($prod) {
                        $countUpdate++;


                        /** @var $storePrice StorePrice */
                        $storePrice = StorePrice::model()->findByAttributes(array('store_id' => $task->item_id, 'product_id' => $prod->id));
                        if (!$storePrice) {
                            $storePrice = new StorePrice();
                            $storePrice->setAttributes(array(
                                'store_id' => $task->item_id,
                                'product_id' => $pid,
                                'price' => $price,
                                'status' => StorePrice::STATUS_AVAILABLE,
                                'price_type' => StorePrice::PRICE_TYPE_EQUALLY,
                                'product_name' => $product_name,
                                'vendor_name' => $vendor_name,
                                'category_name' => $category_name,
                                'create_time' => time(),
                                'update_time' => time(),
                            ));
                            $storePrice->save();

                        } else {
                            StorePrice::model()->updateByPk(array('store_id' => $task->item_id, 'product_id' => $prod->id), array(
                                'by_vendor' => 0,
                                'price' => $price
                            ));
                        }

                        Yii::app()->gearman->appendJob('sphinx:product',
                            array('product_id' => $prod->id, 'action' => 'update')
                        );


                    } else {
                        $countUpdateError++;
                        echo 'Error update price for product #' . $pid . "\n";
                    }


                    // Обновляем данные по задаче на импорт
                    $this->setProgressParams($task->id, $progress['totalItems'], $countUpdate + $countUpdateError, CatCsv::STATUS_IN_PROGRESS);

                }
                fclose($handle);


                // Отмечаем статус заверешния
                $this->setProgressParams($task->id, $progress['totalItems'], $progress['totalItems'], CatCsv::STATUS_FINISHED);

                echo 'Updating price finished for store #' . $task->getStore()->id . '. Success: ' . $countUpdate . ', errors: ' . $countUpdateError;


            } else {
                throw new Exception('failed open file csv ' . $task->import_file);
            }


        } catch (Exception $e) {
            echo 'Error data: ' . print_r($params, true);
            echo $e->getMessage();
        }
    }


    /**
     * Корректирует поля в переданном массиве $result
     * @param $result
     */
    private function correctFieldsForVendors(&$result)
    {
        foreach ($result as $key => $item) {
            // Заменяем ID категории на ее имя.
            $cat = Category::model()->findByPk((int)$item['category_id']);
            if ($cat)
                $result[$key]['category_id'] = $cat->name;

            // Заменяем ID производителя на имя
            $vendor = Vendor::model()->findByPk((int)$item['vendor_id']);
            if ($vendor)
                $result[$key]['vendor_id'] = $vendor->name;

            // Добавляем колонку price на страницу товара
            $result[$key]['price'] = '';

            // Добавляем колонку URL на страницу товара
            $result[$key]['url'] = Yii::app()->homeUrl . '/catalog/product/index/id/' . $item['id'];
        }
    }

    /**
     * Корректирует поля в переданном массиве $result
     * @param $result
     */
    private function correctFieldsForStore(&$result, $store_id)
    {
        foreach ($result as $key => $item) {
            // Заменяем ID категории на ее имя.
            $cat = Category::model()->findByPk((int)$item['category_id']);
            if ($cat)
                $result[$key]['category_id'] = $cat->name;

            // Заменяем ID производителя на имя
            $vendor = Vendor::model()->findByPk((int)$item['vendor_id']);
            if ($vendor)
                $result[$key]['vendor_id'] = $vendor->name;

            // Добавляем колонку price на страницу товара
            $result[$key]['price'] = (float)Yii::app()->db->createCommand()
                ->select('price')
                ->from('cat_store_price')
                ->where('store_id = :sid AND product_id = :pid', array(':sid' => $store_id, ':pid' => $item['id']))
                ->queryScalar();

            // Добавляем колонку URL на страницу товара
            $result[$key]['url'] = Yii::app()->homeUrl . '/catalog/product/index/id/' . $item['id'];
        }
    }



    /*
     * ---------------------------------------------------------------------------------------------------
     *  Э К С П О Р Т
     * ---------------------------------------------------------------------------------------------------
     */

    /**
     * Экспорт товаров конкретного производителя в csv файл.
     * Воркер состояние по текущей задаче на экспорт отмечает
     * в таблице cat_export_csv
     * @param GearmanJob $job
     */
    public function exportCsv(GearmanJob $job)
    {
        echo date('[Y-m-d H:i:s] ');

        // Хак для того, чтобы восстановить оборванное соединение с базой данных
        Yii::app()->db->active = false;
        Yii::app()->db->active = true;

        Yii::import('application.modules.catalog.models.*');
        Yii::import('application.components.interfaces.*');

        $data = $job->workload();
        try {
            $params = unserialize($data);

            // Директория, в которую будут складывать экспортируемые файлы
            $basePath = Yii::app()->basePath . '/..';
            $folder = '/uploads/protected/catalog/exportCsv';
            // Имя csv файла
            $fileName = date('Y-m-d_hi') . '_' . Amputate::rus2translit($params['vendor_name']) . '.csv';
            // Ограничение на одновременное количество строк, выбираемых из базы для экспорта
            $selectLimit = 1000;


            // Проверяем есть ли в базе задача на эскпорт.
            $task = CatExportCsv::model()->findByAttributes(array(
                'user_id' => $params['user_id'],
                'vendor_id' => $params['vendor_id'],
            ));
            if ($task)
                $taskId = $task->id;
            else
                throw new CHttpException(500, 'Задача на экспорт товара по производителю была не найдена.');


            // Считаем кол-во товаров, которые предстоит сохранить в файл
            $count = Yii::app()->db->createCommand(array('select' => 'COUNT(*)', 'from' => 'cat_product', 'where' => 'status = :st AND vendor_id = :vid', 'params' => array(':st' => Product::STATUS_ACTIVE, ':vid' => $params['vendor_id'])))
                ->queryScalar();


            if ($count > 0) {
                // Ставим статус "В процессе" и указываем общее кол-во элементов для обработки
                $this->exportSetProgressParams($taskId, $count, 0, CatExportCsv::STATUS_IN_PROGRESS);


                // Создаем файл для записи
                if (!file_exists($basePath . $folder))
                    mkdir($basePath . $folder, 0755, true);

                $fp = fopen($basePath . $folder . '/' . $fileName, 'w');

                // Сохраняем строку с названиями полей
                $this->exportSaveRow($fp, array(array_values($this->exportColumns + array('url' => 'URL'))));


                for ($i = 0, $ci = intval($count / $selectLimit); $i < $ci; $i++) {
                    // Получаем порцию данных
                    $result = $this->exportGetRangeData($params['vendor_id'], $selectLimit, $i * $selectLimit);

                    $this->exportCorrectFields($result);

                    // Сохраняем полученную порцию данных в файл
                    $this->exportSaveRow($fp, $result);


                    // Обновляем текущий прогресс
                    $this->exportSetProgressParams($taskId, $count, $i * $selectLimit, CatExportCsv::STATUS_IN_PROGRESS);
                }


                // Получаем оставшуюся порцию данных, которая не вошла в предыдущее количество равных кусков
                $result = $this->exportGetRangeData($params['vendor_id'], $count % $selectLimit, intval($count / $selectLimit) * $selectLimit);

                $this->exportCorrectFields($result);

                // Сохраняем полученную порцию данных в файл
                $this->exportSaveRow($fp, $result);


                // Закрываем файл, после записи всех строк
                fclose($fp);

                // Отмечаем, что процесс завершился
                $this->exportSetProgressParams($taskId, $count, $count, CatExportCsv::STATUS_FINISHED, $folder . '/' . $fileName);


                echo "$count products has been exported for vendor #{$params['vendor_id']}";

            } else {
                $this->exportSetProgressParams($taskId, 0, 0, CatExportCsv::STATUS_FINISHED);

                echo "Nothing to export for vendor #{$params['vendor_id']}";
            }


        } catch (Exception $e) {
            echo 'Error data: ' . $data;
            echo $e->getMessage();
        }
    }


    /**
     * Корректирует поля в переданном массиве $result
     * @param $result
     */
    private function exportCorrectFields(&$result)
    {
        foreach ($result as $key => $item) {
            // Заменяем ID категории на ее имя.
            $cat = Category::model()->findByPk((int)$item['category_id']);
            if ($cat)
                $result[$key]['category_id'] = $cat->name;

            // Добавляем колнонку URL на страницу товара
            $result[$key]['url'] = Yii::app()->homeUrl . '/catalog/product/index/id/' . $item['id'];
        }
    }

    /**
     * Возвращает небольшую порцию данных по товарам
     * @param $vendor_id integer ID производителя, к которому относятся товары
     * @param $limit integer Количество элементов, которое нужно взять
     * @param $offset integer Смщение в выборке
     * @return array|null Ассоциативный массив данных
     */
    private function exportGetRangeData($vendor_id, $limit, $offset)
    {
        $result = Yii::app()->db->createCommand(array(
            'select' => implode(',', array_keys($this->exportColumns)),
            'from' => 'cat_product',
            'where' => 'status = :st AND vendor_id = :vid',
            'params' => array(':st' => Product::STATUS_ACTIVE, ':vid' => $vendor_id),
            'limit' => $limit,
            'offset' => $offset
        ))->queryAll();

        return $result;
    }

    /**
     * Устанавливает для задачи на экспорт параметры.
     * @param $taskId integer ID задачи на экспорт
     * @param $totalItems integer Общее кол-во элементов, которые надо экспортировать в csv.
     * @param $doneItems integer Уже обработанное кол-во элементов.
     * @param $download_file string Путь к экспортированному файлу относительно корня сайта
     */
    private function exportSetProgressParams($taskId, $totalItems, $doneItems, $status, $download_file = null)
    {
        CatExportCsv::model()->updateByPk($taskId, array(
            'status' => $status,
            'progress' => serialize(array('totalItems' => $totalItems, 'doneItems' => $doneItems)),
            'update_time' => time(),
            'download_file' => $download_file
        ));
    }

    /**
     * Сохраняет набор данных в файл.
     * @param $fp указатель на файл, в который будем сохранять
     * @param $result Массив массивов данных для сохранения
     */
    private function exportSaveRow($fp, $result)
    {
        foreach ($result as $d) {
            // Конвертим в кодировку cp1251
            $d_cp1251 = array_map(function ($n) {
                return iconv('UTF-8', 'cp1251//TRANSLIT', $n);
            }, $d);
            fwrite($fp, implode(';', $d_cp1251) . "\r\n");
        }
    }


    /*
     * ---------------------------------------------------------------------------------------------------
     *  И М П О Р Т
     * ---------------------------------------------------------------------------------------------------
     */

    public function importCsv(GearmanJob $job)
    {
        echo date('[Y-m-d H:i:s] ');

        // Хак для того, чтобы восстановить оборванное соединение с базой данных
        Yii::app()->db->active = false;
        Yii::app()->db->active = true;

        Yii::import('application.modules.catalog.models.*');
        Yii::import('application.components.interfaces.*');

        $data = $job->workload();
        try {
            $params = unserialize($data);

            // Список id категорий, у которых нужно пересчитать максимальные цены
            $categoryIdList = array();

            $basePath = Yii::app()->basePath . '/..';

            $task = CatImportCsv::model()->findByAttributes(array(
                'user_id' => $params['user_id'],
                'vendor_id' => $params['vendor_id']
            ));

            if ($task)
                $taskId = $task->id;
            else
                throw new CHttpException(500, 'Задача на импорт товара по производителю была не найдена.');


            if (($handle = fopen($basePath . '/' . $task->import_file, "r")) !== false) {
                // Обновляем данные о том, что мы начали обработку
                $progress = unserialize($task->progress);
                $this->importSetProgressParams($taskId, $progress['totalItems'], 0, CatImportCsv::STATUS_IN_PROGRESS);


                // Кол-во продуктов, которым обновлена цена
                $countUpdate = 0;
                $countUpdateError = 0;

                // Максимальное число запросов, попадающих в транзакцию
                $counterTransactionLimit = 1000;
                // Текущее кол-во запросов, добавленных в транзакцию
                $counterTransactionCurrent = 0;


                // Читаем csv и обновляем цены.
                while (($line = fgets($handle)) !== false) {
                    $values = explode(';', $line);

                    if ($values[0] == 'pid' || $values[0] == 'PID')
                        continue;

                    // Идентификатор товара
                    $pid = (int)$values[0];
                    // Цена товара
                    $price = (float)$values[4];


                    if ($counterTransactionCurrent == 0)
                        $transaction = Yii::app()->db->beginTransaction();

                    try {
                        // Если товар есть и относится к нужному производителю
                        $prod = Product::model()->findByPk(($pid), 'vendor_id = :vid', array(':vid' => $params['vendor_id']));

                        if ($prod) {
                            $countUpdate++;
                            Product::model()->updateByPk($pid, array('price' => $price, 'update_time' => time()));
                            $categoryIdList[] = (int)$prod->category_id;
                        } else {
                            $countUpdateError++;
                            echo 'Error update price for product #' . $pid . "\n";
                        }

                        $counterTransactionCurrent++;
                    } catch (Exception $e) {
                        $transaction->rollback();
                        echo "Error in transaction\n";
                        break;
                    }


                    if ($counterTransactionCurrent == $counterTransactionLimit) {
                        // Комитим накопленные запросы
                        $transaction->commit();
                        // Сбрасываем счетчик запросов, добавляемых в транзакцию
                        $counterTransactionCurrent = 0;
                        // Обновляем данные по задаче на импорт
                        $this->importSetProgressParams($taskId, $progress['totalItems'], $countUpdate + $countUpdateError, CatImportCsv::STATUS_IN_PROGRESS);
                    }

                }
                fclose($handle);


                // Делаем завершающий коммит, который обновит все остатки запросов, если они есть.
                if ($transaction) {
                    $transaction->commit();
                    $this->importSetProgressParams($taskId, $progress['totalItems'], $countUpdate + $countUpdateError, CatImportCsv::STATUS_IN_PROGRESS);
                }


                // Обновляем максимальные цены для категорий обновелнных товаров
                if (!empty($categoryIdList)) {
                    $transaction = Yii::app()->db->beginTransaction();
                    try {
                        foreach ($categoryIdList as $item) {
                            Category::setMaxPrice($item);
                        }
                        $transaction->commit();
                    } catch (Exception $e) {
                        $transaction->rollback();
                    }
                }


                // Отмечаем статус заверешния
                $this->importSetProgressParams($taskId, $progress['totalItems'], $progress['totalItems'], CatImportCsv::STATUS_FINISHED);

                echo 'Updating price finished for vendor #' . $params['vendor_id'] . '. Success: ' . $countUpdate . ', errors: ' . $countUpdateError;


            } else {
                throw new Exception('failed open file csv ' . $task->import_file);
            }


        } catch (Exception $e) {
            echo 'Error: ' . $data . "\n";
            echo $e->getMessage();
        }
    }


    /**
     * Устанавливает для задачи на импорт параметры.
     * @param $taskId integer ID задачи на импорт
     * @param $totalItems integer Общее кол-во элементов, которые надо импоритровать в csv.
     * @param $doneItems integer Уже обработанное кол-во элементов.
     */
    private function importSetProgressParams($taskId, $totalItems, $doneItems, $status)
    {
        CatImportCsv::model()->updateByPk($taskId, array(
            'status' => $status,
            'progress' => serialize(array('totalItems' => $totalItems, 'doneItems' => $doneItems)),
            'update_time' => time(),
        ));
    }

    /**
     * Воркер добавляет все товары производителя в магазин
     * Задача "assign_products", передаваемые параметры store_id и vendor_id
     * @param GearmanJob $job
     * @throws Exception
     */
    public function assignProducts(GearmanJob $job)
    {
        echo date('[Y-m-d H:i:s] ');

        try {
            // валидация переданных параметров
            $params = unserialize($job->workload());
            if (!$params['store_id'] && !$params['vendor_id'])
                throw new Exception('Recieved data is not correct');

            // магазин
            $store_id = (int)$params['store_id'];
            // производитель
            $vendor_id = (int)$params['vendor_id'];

            // получение всех товаров производителя
            $products = Yii::app()->db->createCommand()->select('p.id')->from('cat_product p')
                ->where('p.vendor_id=:vid', array(':vid' => $vendor_id))->queryColumn();

            // товары добавляются транзакционно из-за большого объема insert запросов (для InnoDB)
            $connection = Yii::app()->db;
            $transaction = $connection->beginTransaction();
            try {
                foreach ($products as $product_id) {

                    // проверка на наличие товара в магазине
                    $exists = $connection->createCommand()->from('cat_store_price')
                        ->where('store_id=:sid and product_id=:pid', array(':sid' => $store_id, ':pid' => $product_id))->queryRow();

                    if ($exists)
                        continue;

                    // добавление товара в магазин (цена не указана)
                    $connection->createCommand()->insert('cat_store_price', array(
                        'store_id' => $store_id,
                        'product_id' => $product_id,
                        'price' => 0.00,
                        'by_vendor' => 0,
                        'create_time' => time(),
                        'update_time' => time(),
                    ));
                }
                // коммит транзакции
                $transaction->commit();

            } catch (Exception $e) {
                // откат транзакции в случае ошибки
                $transaction->rollback();
            }

        } catch (Exception $e) {

            // дебаг-информация в случае исключительной ситуации
            echo 'Error data: ' . print_r($params, true);
            echo mysql_error();
            echo $e->getMessage();
        }
    }
}
