<?php

class VendorController extends AdminController
{
        public $layout = 'webroot.themes.myhome.views.layouts.backend';

        public function filters() {
                return array('accessControl');
        }

        public function accessRules() {

                return array(
			array('allow',
				'actions' => array('index', 'exportImport', 'acVendor'),
				'roles' => array(User::ROLE_SALEMANAGER)
			),
                        array('allow',
                                'actions' => array('acVendor'),
                                'roles' => array(User::ROLE_STORES_ADMIN, User::ROLE_STORES_MODERATOR)
                        ),
			array('allow',
				'actions' => array(),
			),
                        array('allow',
                                'roles'=>array(
                                        User::ROLE_ADMIN,
                                        User::ROLE_POWERADMIN,
                                        User::ROLE_MODERATOR,
				    	User::ROLE_SENIORMODERATOR,
					User::ROLE_FREELANCE_PRODUCT,
					User::ROLE_FREELANCE_STORE,
                                ),
                        ),
                        array('deny',
                                'users'=>array('*'),
                        ),
                );
        }

        public function init()
        {
                // отключение твиттер-панели
                $this->rightbar = null;
        }

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Vendor;

		if(isset($_POST['Vendor']))
		{
			$model->attributes=$_POST['Vendor'];
                        $model->user_id = Yii::app()->user->id;
                        $model->image=CUploadedFile::getInstance($model,'image');

			if($model->save())
                                $this->redirect(array('index'));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		if(isset($_POST['Vendor']))
		{
			$model->attributes=$_POST['Vendor'];
                        $model->user_id = Yii::app()->user->id;
                        $model->image=CUploadedFile::getInstance($model,'image');

			if($model->save())
                                $this->redirect(array('index'));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Manages all models.
	 */
	public function actionIndex()
	{
                $model = new Vendor('search');
                $criteria = new CDbCriteria();
                $criteria->order = 'create_time DESC';

                if(isset($_GET['Vendor'])) {
                        $model->attributes = $_GET['Vendor'];

                        if($model->id)
                                $criteria->compare('id', $model->id);
                        if($model->name)
                                $criteria->compare('name', $model->name, true);
                        if($model->country_id)
                                $criteria->compare('country_id', $model->country_id);

			if (!empty($model->contractor))	{
				$criteria->join = 'INNER JOIN cat_vendor_contractor as cvc ON cvc.vendor_id=t.id ';
				$criteria->compare('cvc.contractor_id', $model->contractor);
			}


		}

                $dataProvider = new CActiveDataProvider('Vendor', array(
                        'criteria'=>$criteria,
                        'pagination'=>array(
                                'pageSize'=>20,
                        )));

                $this->render('index',array(
                        'dataProvider'=>$dataProvider,
                        'model'=>$model,
                ));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Vendor::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='vendor-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

        /**
         * Автокомплит опции Производитель
         * @param $term
         * @throws CHttpException
         */
        public function actionAcVendor($term)
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);
                $this->layout = false;

                $term_trans = Amputate::rus2translit($term);

                $command = Yii::app()->dbcatalog2;

                $data = $command->createCommand("SELECT t.id, t.name, t.country_id FROM `cat_vendor` t WHERE t.name LIKE '%" . CHtml::encode($term) . "%' OR t.name LIKE '%" . CHtml::encode($term_trans) . "%'")->queryAll();
                $results = array();
                foreach($data as $record) {

			$colls = $command->createCommand("SELECT id, name FROM cat_vendor_collection WHERE vendor_id = ".intval($record['id']).' ORDER BY position ASC')->queryAll();
                        $country_name = $command->createCommand()->select('name')->from('country')->where('id=:id', array(':id'=>$record['country_id']))->queryScalar();

                        $results[] = array(
                                'label'=>$record['name'],
                                'value'=>$record['name'],
                                'id'=>$record['id'],
                                'country'=>$record['country_id'],
                                'country_name'=>$country_name,
                                'collections'=>$this->renderPartial('_collectionOptionForDropdown',array('colls'=>$colls),true),
                                'collections_qt'=>count($colls),
                        );
                }
                die(CJSON::encode($results));
        }

        /**
         * Создание коллекции
         * @throws CHttpException
         */
        public function actionCreateCollection()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $this->layout = false;

                $vendor = $this->loadModel(Yii::app()->request->getParam('vid'));

                $coll_name = Yii::app()->request->getParam('coll_name');

                if(!$coll_name)
                        die(CJSON::encode(array('success'=>false, 'message'=>'Не указано название коллекции')));


		// Проверяем максимальную позицию коллекций у текущего производителя
		$maxPos = Yii::app()->dbcatalog2->createCommand("SELECT MAX(position) FROM cat_vendor_collection WHERE vendor_id = ".$vendor->id)->queryScalar();

		// Добавляем новую коллекцию в следующую позицию
		$cmd = Yii::app()->dbcatalog2->createCommand();
		$cmd->insert('cat_vendor_collection', array(
			'vendor_id'   => $vendor->id,
			'name'        => CHtml::encode($coll_name),
			'position'    => $maxPos + 1,
			'create_time' => time()
		));
		$collectionId = Yii::app()->dbcatalog2->lastInsertID;

                $response = array('success'=>true, 'html'=>$this->renderPartial('_collectionRow', array('coll_id'=>$collectionId, 'coll_name'=>CHtml::encode($coll_name)), true));
                die(CJSON::encode($response));
        }

        /**
         * Удаление коллекции
         * @throws CHttpException
         */
        public function actionDeleteCollection()
        {
                if ( ! Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $this->layout = false;

                $vendor = $this->loadModel(Yii::app()->request->getParam('vid'));

                $coll_id = Yii::app()->request->getParam('coll_id');

		$cmd = Yii::app()->dbcatalog2->createCommand();
		$cmd->select('id');
		$cmd->from('cat_vendor_collection');
		$cmd->where('id = :coll_id AND vendor_id = :vid', array(':coll_id' => $coll_id, ':vid' => $vendor->id));
		$collection = $cmd->queryRow();

		if ($collection)
		{
			$cmd = Yii::app()->dbcatalog2->createCommand();
			$cmd->delete('cat_vendor_collection', 'id = :id', array(':id' => $collection['id']));

			// Перенумеровавывем все коллекции текущего производителя.
			Yii::app()->dbcatalog2->createCommand("SET @num := 0; UPDATE cat_vendor_collection SET position = (@num := @num + 1) WHERE vendor_id = {$vendor->id} ORDER BY position ASC ")->execute();

			die(CJSON::encode(array('success'=>true)));
		}
		else
		{
			die(CJSON::encode(array('success'=>false, 'message'=>'Коллекция не существует')));
		}
        }

        /**
         * Редактирование коллекции
         * @throws CHttpException
         */
        public function actionUpdateCollection()
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $this->layout = false;

                $vendor = $this->loadModel(Yii::app()->request->getParam('vid'));
                $coll_id = Yii::app()->request->getParam('coll_id');

		$cmd = Yii::app()->dbcatalog2->createCommand();
		$cmd->select('id');
		$cmd->from('cat_vendor_collection');
		$cmd->where('id = :coll_id AND vendor_id = :vid', array(':coll_id' => $coll_id, ':vid' => $vendor->id));
		$collection = $cmd->queryRow();

                if ( ! $collection)
                        die(CJSON::encode(array('success'=>false, 'message'=>'Коллекция не существует')));

                $coll_name = Yii::app()->request->getParam('coll_name');

                if ( ! $coll_name)
                        die(CJSON::encode(array('success'=>false, 'message'=>'Не указано название коллекции')));

		// Добавляем новую коллекцию в следующую позицию
		$cmd = Yii::app()->dbcatalog2->createCommand();
		$cmd->update('cat_vendor_collection', array(
			'name' => CHtml::encode($coll_name),
		), 'id = :cid', array(':cid' => $collection['id']));

                die(CJSON::encode(array('success'=>true)));
        }

	public function actionChangePosCollection()
	{
		if ( ! Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);

		$this->layout;

		$vendor = $this->loadModel(Yii::app()->request->getParam('vid'));
		$coll_id = Yii::app()->request->getParam('coll_id');
		$direct = Yii::app()->request->getParam('direct');

		$cmd = Yii::app()->dbcatalog2->createCommand();
		$cmd->select('id, position');
		$cmd->from('cat_vendor_collection');
		$cmd->where('id = :coll_id AND vendor_id = :vid', array(':coll_id' => $coll_id, ':vid' => $vendor->id));
		$collection = $cmd->queryRow();

		if ( ! $collection)
			die(CJSON::encode(array('success'=>false, 'message'=>'Коллекция не существует')));

		switch($direct)
		{
			case 'up':
				$newPos = $collection['position'] - 1;
				if ($newPos >= 1) {
					// Вышестоящему элементу увеличиваем позицию на 1
					Yii::app()->dbcatalog2->createCommand("UPDATE cat_vendor_collection SET position = position + 1 WHERE vendor_id = '".$vendor->id."' AND position = '".$newPos."'")->execute();
					// Текущему элементу ставим новую позицию
					Yii::app()->dbcatalog2->createCommand("UPDATE cat_vendor_collection SET position = '".$newPos."' WHERE id = '".$collection['id']."'")->execute();
				}
				break;
			case 'down':
				$newPos = $collection['position'] + 1;

				// Получаем максимально доступную позицию
				$maxPos = Yii::app()->dbcatalog2->createCommand("SELECT MAX(position) FROM cat_vendor_collection WHERE vendor_id = '".$vendor->id."'")->queryScalar();

				if ($newPos <= $maxPos) {
					// Нижестоящему элементу уменьшаем позицию на 1
					Yii::app()->dbcatalog2->createCommand("UPDATE cat_vendor_collection SET position = position - 1 WHERE vendor_id = '".$vendor->id."' AND position = '".$newPos."'")->execute();
					// Текущему элементу ставим новую позицию
					Yii::app()->dbcatalog2->createCommand("UPDATE cat_vendor_collection SET position = '".$newPos."' WHERE id = '".$collection['id']."'")->execute();
				}

				break;
			default:
				die(CJSON::encode(array('success'=>false, 'message'=>'Неверное направление сдвига')));
		}

		die(CJSON::encode(array('success'=>true)));
	}

        /**
         * Автокомплит по странам
         * @param null $term
	 * TODO: FIX THIS SHIT!!! city_name index
         */
        public function actionAutocompleteCountry($term = null)
        {
                if (!Yii::app()->request->isAjaxRequest || empty($term))
                        throw new CHttpException(404);

                $countries = Yii::app()->dbcatalog2->createCommand()->from(Country::model()->tableName())
                        ->where('name like "'.$term.'%"')->queryAll();

                $arr = array();
                foreach ($countries as $ct) {
                        $arr[] = array(
                                'label' => $ct['name'],
                                'value' => $ct['name'],
                                'id' => $ct['id'],
                        );
                }
                echo CJSON::encode($arr);
        }


	public function actionExportImport($vid = null)
	{
		$vendor = Vendor::model()->findByPk((int)$vid);
		if (!$vendor)
			throw new CHttpException(404);


		$action_name = Yii::app()->request->getParam('action_name');


		// Получаем данные по задаче на экспорт для производителя $vid
		$taskExport = CatExportCsv::model()->findByAttributes(array(
			'user_id' => Yii::app()->user->id,
			'vendor_id' => $vendor->id
		));

		// Получаем данные по задаче на импорт для производителя $vid
		$taskImport = CatImportCsv::model()->findByAttributes(array(
			'user_id' => Yii::app()->user->id,
			'vendor_id' => $vendor->id
		));

		switch($action_name)
		{
			case 'export':

				// Если уже есть задача и она завершена, удаляем ее
				if ($taskExport)
				{
					if ($taskExport->status == CatExportCsv::STATUS_FINISHED)
					{
						if ($taskExport->download_file) {
							if (file_exists(Yii::app()->basePath.'/..'.$taskExport->download_file))
								unlink(Yii::app()->basePath.'/..'.$taskExport->download_file);
						}
						$taskExport->delete();
						$taskExport = null;
					}
					elseif ($taskExport->status == CatExportCsv::STATUS_IN_PROGRESS)
					{
						Yii::app()->user->setFlash('alreadyExport', 'Экспорт уже запущен.');
					}
				}

				// Инициируем новую задачу в случае, если ее не было или удалили старую
				if ( ! $taskExport)
				{
					$taskExport = new CatExportCsv();
					$taskExport->user_id = Yii::app()->user->id;
					$taskExport->vendor_id = $vendor->id;
					$taskExport->status = CatExportCsv::STATUS_NEW;
					$taskExport->progress = serialize(array('totalItems' => 0, 'doneItems' => 0));
					if ( ! $taskExport->save()) {
						throw new CHttpException(500, 'Ошибка создания задачи на экспорт');
					}

					// Добавляем очередь в воркер
					Yii::app()->gearman->appendJob('catalogExportCsv', array(
						'user_id' => Yii::app()->user->id,
						'vendor_id' => $vendor->id,
						'vendor_name' => $vendor->name
					));
				}
				break;

			case 'exportStatus':

				if (Yii::app()->request->isAjaxRequest) {

					$data = unserialize($taskExport->progress);

					die(json_encode(array(
						'status'        => $taskExport->status,
						'totalItems'    => $data['totalItems'],
						'doneItems'     => $data['doneItems'],
						'download_file' => ($taskExport->download_file) ? '/download/catalogCsv/id/'.$taskExport->id : '',
					)));
				}
				break;


			case 'import':

				if ($taskImport)
				{
					if ($taskImport->status == CatImportCsv::STATUS_FINISHED)
					{
						@unlink(Yii::app()->basePath.'/..'.$taskImport->import_file);
						$taskImport->delete();
						$taskImport = null;
					}
					elseif ($taskImport->status == CatImportCsv::STATUS_NEW || $taskImport->status == CatImportCsv::STATUS_IN_PROGRESS)
					{
						Yii::app()->user->setFlash('importError', 'Импорт уже запущен. Дождитесь завершения операции.');
					}
				}

				if ( ! $taskImport)
				{
					// Если файл существует и нет ошибок
					if (isset($_FILES['file_csv']) && ($file_csv = $_FILES['file_csv']) && $file_csv['error'] == 0)
					{
						// Новое имя для файла
						$basePath = Yii::app()->basePath.'/..';
						$folder = '/uploads/protected/catalog/importCsv';
						// Имя csv файла
						$fileName = date('Y-m-d_Hi').'_'.Amputate::rus2translit($vendor->name).'.csv';

						// Создаем папку, если ее вдруг нет.
						if ( ! file_exists($basePath.$folder))
							mkdir($basePath.$folder, 0755, true);

						if (move_uploaded_file($file_csv['tmp_name'], $basePath.$folder.'/'.$fileName))
						{
							if (($handle = fopen($basePath.$folder.'/'.$fileName, "r")) !== FALSE)
							{
								// Считываем первую строку из CSV файла, и проверяем, чтобы там были нужные названия полей
								// Если все ок, то ставим запись в очередь на импорт.
								$data = fgetcsv($handle, 4096, ";");
								$data = array_map(function($n){ return iconv('cp1251', 'UTF-8', $n); }, $data);
								if (	   isset($data[0]) && mb_strtolower($data[0], 'UTF-8') == 'pid'
									&& isset($data[1]) && mb_strtolower($data[1], 'UTF-8') == 'артикул'
									&& isset($data[2]) && mb_strtolower($data[2], 'UTF-8') == 'название'
									&& isset($data[3]) && mb_strtolower($data[3], 'UTF-8') == 'категория'
									&& isset($data[4]) && mb_strtolower($data[4], 'UTF-8') == 'цена'
									&& isset($data[5]) && mb_strtolower($data[5], 'UTF-8') == 'url'
								) {
									// Подсчитаем кол-во строк в файле, которое равно кол-ву импортируемых элементов
									$countLines = 0;
									while (($line = fgets($handle,4096)) !== false) {
										$countLines++;
									}
									fclose($handle);


									// Создаем задачу на импорт
									$taskImport = new CatImportCsv();
									$taskImport->user_id = Yii::app()->user->id;
									$taskImport->vendor_id = $vendor->id;
									$taskImport->status = CatImportCsv::STATUS_NEW;
									$taskImport->import_file = $folder.'/'.$fileName;
									$taskImport->progress = serialize(array('totalItems' => $countLines, 'doneItems' => 0));
									if ( ! $taskImport->save()) {
										throw new CHttpException(500, 'Ошибка создания задачи на импорт');
									}


									// Добавляем очередь в воркер
									Yii::app()->gearman->appendJob('catalogImportCsv', array(
										'user_id' => Yii::app()->user->id,
										'vendor_id' => $vendor->id,
										'vendor_name' => $vendor->name
									));

								} else {
									Yii::app()->user->setFlash('importError', 'Неверный набор полей в csv файле');
								}


							}
						}

					}
				}

				break;

			case 'importStatus':

				if (Yii::app()->request->isAjaxRequest) {

					$data = unserialize($taskImport->progress);

					die(json_encode(array(
						'status'        => $taskImport->status,
						'totalItems'    => $data['totalItems'],
						'doneItems'     => $data['doneItems'],
					)));
				}
				break;

			default:
				break;
		}


		$this->render('export_import', array(
			'vendor' => $vendor,
			'taskExport' => $taskExport,
			'taskImport' => $taskImport
		));
	}

}
