<?php
/**
 * Консольные команды для периодического выполнения
 *
 * @author alexsh
 */
class CronCommand extends CConsoleCommand
{
	/**
	 * Выставляет тендерам статус "закрыт" по истечении времени активности тендера
	 */
	public function actionTenderUpdate()
	{
		Yii::import('application.modules.tenders.models.*');

		$criteria = new CDbCriteria();
		$criteria->condition = 't.status IN (:stOpen, :stChanged) AND t.expire < :time';
		$criteria->params = array(
			':stOpen' => Tender::STATUS_OPEN,
			':stChanged' => Tender::STATUS_CHANGED,
			':time' => time(),
		);

		$tenders = Tender::model()->findAll($criteria);

		/** @var $tender Tender */
		foreach ($tenders as $tender) {
			$tender->status = Tender::STATUS_CLOSED;
			$tender->save(false);
			echo date("[Y-m-d H:i:s]") . ' Tender closed ID: '.$tender->id."\n";
		}
	}

	/**
	 * Очистка cookieStorage от устаревших записей
	 */
	public function actionClearCookieStorage()
	{
		$sql = 'DELETE FROM cookie_storage WHERE expire < :time';

		/** удаление устаревших записей (более 30 суток) */
		$time = time();
		$result = Yii::app()->db->createCommand($sql)->bindParam(':time', $time)->execute();
		echo date("[Y-m-d H:i:s]") . ' DELETED ROWS: '.$result."\n";
	}

	public function actionClearViews()
	{
		$deletePoint = time() - 86400;
		$sql = 'DELETE FROM profile_views WHERE time < :time';
		$result = Yii::app()->db->createCommand($sql)->bindParam(':time', $deletePoint)->execute();
		echo date("[Y-m-d H:i:s]") . ' PROFILE DELETED ROWS: '.$result."\n";

		$sql = 'DELETE FROM tender_views WHERE time < :time';
		$result = Yii::app()->db->createCommand($sql)->bindParam(':time', $deletePoint)->execute();
		echo date("[Y-m-d H:i:s]") . ' TENDER DELETED ROWS: '.$result."\n";
		echo "\n";
	}

	/**
	 * Очистка таблицы сессий
	 */
	public function actionClearSessionTable()
	{
		$result = Yii::app()->db->createCommand()
			->delete('session','expire<:expire',array(':expire'=>time()));
		echo date("[Y-m-d H:i:s]") . ' DELETED ROWS: '.$result."\n";
	}

	/**
	 * Функция пересчитывает среднюю цену для всех товаров, у которых есть привязка цен.
	 */
    public function actionRecountAverageProductPrice()
    {
        $this->_recountAverageProductPrice(Yii::app()->db);
        $this->_recountAverageProductPrice(Yii::app()->dbcatalog2);
    }

	public function _recountAverageProductPrice(CDbConnection $conn)
	{
		Yii::import('application.modules.catalog.models.Product');

		echo "\nЗапускаем пересчет средних цен товаров. Только тех, у которых есть связка цен с магазинами.\n";

		// Сбрасываем средние цены всех товаров
        $conn->createCommand('UPDATE cat_product SET average_price = 0')->execute();

		// Количество товаров, которые выбираются за раз
		$limit = 3000;

		$startTime = microtime(true);

		$sql = "

			SELECT
				csp.product_id as pid, AVG(csp.price) as average_price
			FROM
				cat_store_price csp
			LEFT JOIN cat_product cp
				ON cp.id = csp.product_id
			WHERE
				csp.price > 0 AND cp.status = :st
			GROUP BY
				csp.product_id
			LIMIT
				:offset, :limit
		";

		$totalQnt = 0;
		$i = 0;
		while ( ($result = $conn->createCommand($sql)->bindValues(array(':st' => Product::STATUS_ACTIVE, ':limit' => $limit, ':offset' => $limit * $i))->queryAll()) )
		{
			$currentQnt = count($result);

			$transaction = $conn->beginTransaction();

			try
			{
				foreach($result as $item) {
                    $conn->createCommand()->update('cat_product', [
                        'average_price' => round($item['average_price'])
                    ], 'id=:id', [':id' => $item['pid']]);
				}

				$transaction->commit();
			}
			catch (Exception $e)
			{
				$transaction->rollback();
				echo "Error in transaction\n";
				break;
			}

			echo "Обработано товаров: {$currentQnt}\n";


			// Наращиваем общий счетчик результата
			$totalQnt += $currentQnt;
			$i++;
		}

		$endTime = microtime(true);

		$executeTime = round($endTime - $startTime, 2);

		echo "Всего обработано товаров: {$totalQnt}. \nВремя выполнения: {$executeTime} с.";
	}


	/**
	 * Уведомления о событии по почте
	 */
	public function actionMailEventNotify()
	{
		$start = time();

		Yii::import('application.modules.media.models.*');
		$time = time() + MediaEvent::NOTIFY_PERIOD;
		$events = MediaEvent::model()->findAllByAttributes(array('send_status'=>MediaEvent::NOTIFY_NOT_SENT), 'start_time<:stime', array(':stime'=>$time));
		$mails = 0;

		/** @var $event MediaEvent */
		foreach ($events as $event) {
			$sql = 'SELECT email FROM media_event_notify WHERE event_id='.$event->id;
			$emails = Yii::app()->db->createCommand($sql)->queryAll();

			$places = MediaEventPlace::model()->findAllByAttributes(array('event_id'=>$event->id));

			$placesStr = '';
			$placeCnt = 0;
			if (!empty($places)) {
				$placesStr .= '<div>Место проведения: ';
				/** @var $place MediaEventPlace */
				foreach ($places as $place) {
					if ($placeCnt != 0) {
						$placesStr .= '; ';
					}
					$placesStr .= City::getNameById($place->city_id).', '.$place->name.' по адресу '.$place->address;
					$placeCnt++;
				}
				$placesStr .= '</div>';
			}

			$eventUrl = Yii::app()->homeUrl . $event->getElementLink();
			$eventLink = CHtml::link($eventUrl, $eventUrl);
			$dateRange = CFormatterEx::formatDateRange($event->start_time, $event->end_time, 'по');

			foreach ($emails as $email) {
				$mails++;
				Yii::app()->mail->create('eventNotify')
					->to($email['email'])
					->priority(EmailComponent::PRT_LOW)
					->params(array(
					'date' => $dateRange,
					'event_name' => $event->name,
					'places' => $placesStr,
					'event_url' => $eventLink,
				))
					->send();

			}
			$event->send_status = MediaEvent::NOTIFY_SENT;
			$event->save(false);
		}

		echo date("[Y-m-d H:i:s]") . ' MailEventNotify Total send: '.$mails.' Total time: '.(time()-$start)."\n";
	}

	/**
	 * Уведомления по почте для разных пользователей
	 * (не активированные, без услуг, без проектов, <3 проектов, пустой about)
	 */
	public function actionMailNotifier()
	{
		Yii::import('application.modules.idea.models.*');

		$start = time();

		$result = Mail::notAcceptedInvite(7, 14, 28);
		echo date("[Y-m-d H:i:s]") . ' Template: notAcceptedInvite Total: '.($result['success']+$result['fail']).' Success: '.$result['success'].' Fail: '.$result['fail']."\n";

		$result = Mail::acceptedNoService(7, 14, 28);
		echo date("[Y-m-d H:i:s]") . ' Template: acceptedNoService Total: '.($result['success']+$result['fail']).' Success: '.$result['success'].' Fail: '.$result['fail']."\n";

		$result = Mail::acceptedNoProjects(7, 14, 28);
		echo date("[Y-m-d H:i:s]") . ' Template: acceptedNoProjects Total: '.($result['success']+$result['fail']).' Success: '.$result['success'].' Fail: '.$result['fail']."\n";

		$result = Mail::acceptedLess3Projects(7, 14, 28);
		echo date("[Y-m-d H:i:s]") . ' Template: acceptedLess3Projects Total: '.($result['success']+$result['fail']).' Success: '.$result['success'].' Fail: '.$result['fail']."\n";

		$result = Mail::more3ProjectsEmptyInfo(7, 14, 28);
		echo date("[Y-m-d H:i:s]") . ' Template: more3ProjectsEmptyInfo Total: '.($result['success']+$result['fail']).' Success: '.$result['success'].' Fail: '.$result['fail']."\n";

		echo 'Total time: '.(time()-$start)."\n\n";
	}

        /**
         * Сброс тарифов для магазинов, у которых подошел к концу срок действия платного тарифа
         */
        public function actionStoreTariffDisabler()
        {
                Yii::import('application.modules.catalog.models.Store');
                Yii::import('application.modules.catalog.models.Product');

                $start = time();

                $criteria = new CDbCriteria();
                // выборка всех магазинов с платными тарифами, срок действия которых подошел к концу
                $criteria->condition = 'tariff_expire_date < :now and tariff_id <> :visitka';
                $criteria->params = array(':now'=>time(), ':visitka'=>Store::TARIF_FREE);
                $stores = Store::model()->findAll($criteria);

                $count = 0;
                foreach($stores as $store) {
                        $store->tariff_id = Store::TARIF_FREE;
                        $store->save(false, array('tariff_id'));
                        $count++;
                }

                echo date("[Y-m-d H:i:s]") . ' Reset tariff\'s: ' . $count . "\n";
                echo 'Total time: '.(time()-$start)."\n\n";
        }


	/**
	 * Переключает тариф магазинов на новый (tariff_id_new => tariff_id)
	 * в случае, когда подошло время.
	 */
	public function actionStoreTariffEnabler()
	{
		Yii::import('application.modules.catalog.models.Store');
		Yii::import('application.modules.catalog.models.Product');

		$start = time();

		$criteria = new CDbCriteria();
		$criteria->condition = 'tariff_id_new IS NOT NULL AND tariff_enable_date <= :now';
		$criteria->params = array(':now' => time());
		$stores = Store::model()->findAll($criteria);

		$count = 0;

		/** @var $store Store */
		foreach ($stores as $store) {
			$store->tariff_id = $store->tariff_id_new;
			$store->tariff_id_new = null;
			$store->tariff_enable_date = null;
			$store->save(false, array('tariff_id', 'tariff_id_new', 'tariff_enable_date'));
			$count++;
		}

		echo date("[Y-m-d H:i:s]") . ' Enable tariff\'s: ' . $count . "\n";
		echo 'Total time: '.(time()-$start)."\n\n";
	}

	/**
	 * Связывает товары с магазинами через производителей.
	 */
	public function actionBindProductByVendor()
	{
		Yii::import('application.modules.catalog.models.*');

		$i = 0;
		$limit = 1000;

		$totalStores = Store::model()->count();
		$curStores = 0;
		$beginTime = microtime(true);

		echo "=======================================>\n";
		echo "Обработка магазинов: ".$totalStores."\n";
		echo "Начало: ".date('d.m.Y H:i')."\n";

		// Запрос на получение пачки магазинов
		$sql = "SELECT id FROM cat_store WHERE id >= :min AND id < :max";
		while( ($storeIds = Yii::app()->db->createCommand($sql)->bindValues(array(':min' => $i * $limit, ':max' => $i * $limit + $limit))->queryColumn()) )
		{
			// Обходим все магазины
			foreach($storeIds as $sid)
			{
				$curStores++;
				$percent = round($curStores * 100 / $totalStores, 1);
				echo "\rОбработано ".$percent.'%   ';

				// Получаем список производителей магазина
				$vendorIds = Yii::app()->db
					->createCommand("SELECT vendor_id FROM cat_store_vendor WHERE store_id = :sid")
					->bindValue(':sid', $sid)
					->queryColumn();

				foreach ($vendorIds as $vid) {
					/* Проверяем есть ли на конкретного производителя и конкретный магазин
						псевдо-связи через производителя.
					*/
					$isBind = Yii::app()->db->createCommand("
						SELECT sv.store_id FROM cat_store_vendor sv
						INNER JOIN cat_store_price sp ON sv.store_id = sp.store_id
						WHERE sv.vendor_id = :vid AND sv.store_id = :sid AND sp.by_vendor = 1
						LIMIT 1
					")
						->bindValue(':sid', $sid)
						->bindValue(':vid', $vid)
						->execute();

					// Если связи уже были, то ничего не делаем.
					if ($isBind) {
						continue;
					}

					/* Если связей не было, то
					 * получаем список товаров для магазина,
					 * которых нет в cat_store_price
					 */
					$productIds = Yii::app()->db->createCommand('
						SELECT DISTINCT p.id as pid
						FROM cat_store_vendor sv
						INNER JOIN cat_product p
							ON p.vendor_id = sv.vendor_id
						LEFT JOIN cat_store_price sp
							ON sp.product_id = p.id AND sp.store_id = :sid
						WHERE
							isnull(sp.product_id)
							AND
							p.status = 2
							AND
							sv.store_id = :sid
							AND
							sv.vendor_id = :vid
					')
						->bindValue(':sid', $sid)
						->bindValue(':vid', $vid)
						->queryColumn();

					if ( ! empty($productIds)) {
						try {

							$time = time();
							$sql2 = 'INSERT INTO cat_store_price (`store_id`, `product_id`, `price`, `status`, `price_type`, `by_vendor`, `create_time`, `update_time`) VALUES ';
							$sql2Values = '';

							foreach($productIds as $pid)
							{
								if ($sql2Values != '')
									$sql2Values .= ',';
								$sql2Values .= "({$sid}, {$pid}, 0, ".StorePrice::STATUS_AVAILABLE.", ".StorePrice::PRICE_TYPE_EQUALLY.", 1, {$time}, {$time})";
							}

							Yii::app()->db->createCommand($sql2.$sql2Values)->execute();
						} catch(Exception $e) {
							echo $e->getMessage();
						}
					}
				}

			}
			$i++;
		}


		$endTime = microtime(true);
		echo "\nКонец: ".date('d.m.Y H:i');
		echo "\nВсего секунд: ".($endTime-$beginTime);
	}



	/**
	 * Сжатие логов redis, работает в BG
	 */
	public function actionRedisService()
	{
		$result = Yii::app()->predis->bgrewriteaof();
		if ($result) {
			echo "\n".date('d.m.Y H:i').' redis log was compressed';
		} else {
			echo "\n".date('d.m.Y H:i').' ERROR! redis log compress';
		}

	}

	/**
	 * Обновление статистики по разделам
	 */
	public function actionUpdateStat()
	{
		Yii::import('application.modules.member.models.*');
		Yii::import('application.modules.idea.models.*');
		Yii::import('application.modules.catalog.models.*');

		// Переносит данные статистики магазинов из Redis в Mysql
		$pattern='STAT:SPECIALIST:*';
		StatSpecialist::model()->updateStatSpecialistMySql($pattern);

		// Переносит данные статистики по проектам из Redis в Mysql
		$pattern='STAT:PROJECT:*';
		StatProject::model()->updateStatProjectMySql($pattern);

		// Переносит данные статистики по услугам юзеров из Redis в Mysql
		$pattern = 'STAT:USER:*';
		StatUserService::model()->updateStatUserServMySql($pattern);

		//  Переносит данные статистики магазинов из Redis в Mysql
		$pattern='STAT:STORE:*';
		StatStore::model()->updateStatStoreMySql($pattern);
	}

    /**
     * Обновление статистики по разделам
     */
    public function actionUpdateStatSafe()
    {
        Yii::import('application.modules.member.models.*');
        Yii::import('application.modules.idea.models.*');
        Yii::import('application.modules.catalog.models.*');

        $pattern = 'STAT:USER:*';
        StatUserService::model()->updateStatUserServMySqlSafe($pattern);
    }

    /**
     * Обновление статистики по разделам
     */
    public function actionUpdateStatStoreSafe()
    {
        Yii::import('application.modules.member.models.*');
        Yii::import('application.modules.idea.models.*');
        Yii::import('application.modules.catalog.models.*');

        //  Переносит данные статистики магазинов из Redis в Mysql
        $pattern='STAT:STORE:*';
        StatStore::model()->updateStatStoreMySqlSafe($pattern);
    }


    /**
	 * Скрипт формирует подборка
	 * для блока " это интересно"
	 * Пока этот блок только в знаниях
	 */
	public function actionPrepareInterestData()
	{
		//размер выборки по каждому типу
		$size = 100;
		$i = 0;
		//Массив с категориями для фильтрации в выборке
		$categoryIds = array();
		$flag = true;

		Yii::import('application.modules.catalog.models.*');
		Yii::import('application.modules.idea.models.*');
		Yii::import('application.modules.media.models.*');

		//Функция вставки данных в таблицу interest_data
		function insertData($model_id, $model)
		{
			$model_id = (int)$model_id;
			$model = (string)$model;

			$sqlInsert = 'INSERT INTO interest_data (`model_id`, `model`)
						 VALUES (:model_id, :model)';

			Yii::app()->db->createCommand($sqlInsert)
				->bindParam(':model_id', $model_id)
				->bindParam(':model', $model)
				->execute();
		}

		// Получаем случайные id товаров в указанных категориях
		$selectedCatId = array(71, 30, 7);

		foreach ($selectedCatId as $id) {
			//получаем категории с товарами
			$tmpArray = Category::model()->getCategoryChildren($id, false, true);
			$categoryIds = array_merge($categoryIds, $tmpArray);
		}

		$randomProductIds = Product::getRandomProductsIds($size, $categoryIds);

		//Получаем случайные идеи

		$randomIdeas = Idea::getRandomIdeas($size);

		//Получаем случайные статьи

		$randomMedias = Media::getRandomMediaIds($size);

		$productId = reset($randomProductIds);

		$interiorId = reset($randomIdeas);

		$knowledgeId = reset($randomMedias);

		//Очищаем таблицу с данными
		Yii::app()->db->createCommand('truncate table interest_data')->execute();

		$transaction = Yii::app()->db
			->beginTransaction();


		while ($flag) {

			$t = 1;
			while ($t <= 9) {

				//Если это второй элемент в списке то
				//То выбираем или идею или статью
				//надо для того чтобы при формировании
				//вьюхи в большом итеме были статьи или идеи
				if ($t == 2) {
					$rand = rand(2, 3);
				} else {
					$rand = rand(1, 3);
				}

				switch ($rand) {
					case 1:
						if ($productId) {
							insertData($productId, InterestData::MODEL_PRODUCT);
							$productId = next($randomProductIds);
						} else {
							$flag = false;
						}


						break;
					case 2:
						if ($interiorId) {
							insertData($interiorId, InterestData::MODEL_INTERIOR);
							$interiorId = next($randomIdeas);
						} else {
							$flag = false;
						}

						break;
					case 3:
						if ($knowledgeId) {
							insertData($knowledgeId, InterestData::MODEL_KNOWLEDGE);
							$knowledgeId = next($randomMedias);
						} else {
							$flag = false;
						}

						break;
					default:
						break;
				}
				$t++;
			}
			$i++;
		}

		$transaction->commit();
	}

	public function actionRunFolderDiscountTasks()
	{
		Yii::import('application.modules.catalog.models.*');
		//Включаем скидки
		$criteria = new CDbCriteria();
		$criteria->condition = 'date_start<=:time and status=:status and date_start IS NOT NULL';
		$criteria->params = array(
			':time' => time(),
			':status' => CatFolderDiscount::STATUS_ACTIVE,
		);

		$discountsModels = CatFolderDiscount::model()->findAll($criteria);

		foreach($discountsModels as $discount)
		{
			$storePrice = StorePrice::model()->findByAttributes(array(
				'store_id'   => $discount->store_id,
				'product_id' => $discount->model_id
			));

			if($storePrice) {
				$storePrice->discount = $discount->discount;
				$storePrice->save();
				unset($storePrice);
				$discount->status = CatFolderDiscount::STATUS_DEACTIVATE;
				$discount->save();
			}
		}

		//Выключаем скидки
		$criteria = new CDbCriteria();
		$criteria->select = '*';
		$criteria->condition = 'date_end<=:time and status=:status and date_end IS NOT NULL';
		$criteria->params = array(
			':time' => time(),
			':status' => CatFolderDiscount::STATUS_ACTIVE,
		);

		$discountsModels = CatFolderDiscount::model()->findAll($criteria);

		foreach($discountsModels as $discount)
		{
			$storePrice = StorePrice::model()->findByAttributes(array(
				'store_id'   => $discount->store_id,
				'product_id' => $discount->model_id
			));

			if($storePrice) {
				$storePrice->discount = 0;
				$storePrice->save();
				unset($storePrice);

				$discount->status=CatFolderDiscount::STATUS_DEACTIVATE;
				$discount->save(false);
			}
		}
	}

    public function actionStoreXmlImport()
    {
        Yii::import('application.modules.catalog2.models.*');
	$stores = Store::model()->findAll('xml_url <> ""');
	foreach($stores as $store) {
            // проверка наличия запущенных импортов для текущего магазина
            /*if (($taskExists = CatXml::model()->findByAttributes(array(
                'store_id' => $store->id,
                'status' => [CatXml::STATUS_NEW, CatXml::STATUS_IN_PROGRESS]
            )))) {
                continue;
            }*/

            // сохранение файла
            $filePath = 'uploads/protected/catalog2/importXml';
            $fileName = date('Y-m-d_Hi').'_store_'.Amputate::rus2translit($store->name).'.xml';
            if ( ! file_exists(Yii::app()->basePath . '/../' . $filePath)) {
                mkdir(Yii::app()->basePath . '/../' . $filePath, 0755, true);
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'php');
            $fp = fopen($tempFile, 'w');
            $ch = curl_init($store->xml_url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            $result = curl_exec($ch);
            curl_close($ch);
            fclose($fp);

            if ( !$result )
                return false;

            copy($tempFile, Yii::app()->basePath . '/../' . $filePath . '/' . $fileName);
            unlink($tempFile);

            // сохранение задачи импорта
            $xmlImportTask = new CatXml();
            $xmlImportTask->user_id = 0;
            $xmlImportTask->status = CatXml::STATUS_NEW;
            $xmlImportTask->store_id = $store->id;
            $xmlImportTask->file = $filePath . DIRECTORY_SEPARATOR . $fileName;

            if ($xmlImportTask->save())
            {
                Yii::app()->gearman->appendJob('catXml:importXml', array(
                    'task_id' => $xmlImportTask->id,
                ));
            }
        }
}
	public function actionRecreateGearmanJobs() {
	Yii::app()->gearman->appendJob('catXml:importXml', array(
                    'task_id' => 1389,
                ));
Yii::app()->gearman->appendJob('catXml:importXml', array(
                    'task_id' => 1388,
                ));
Yii::app()->gearman->appendJob('catXml:importXml', array(
                    'task_id' => 1387,
                ));
Yii::app()->gearman->appendJob('catXml:importXml', array(
                    'task_id' => 1386,
                ));
	}
 #   }
}
