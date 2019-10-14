<?php

class TenderController extends AdminController
{
	public $layout = 'webroot.themes.myhome.views.layouts.backend';
	
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',
			    'actions' => array('list', 'view', 'update', 'removefile', 'delete', 'upload'),
			    'roles'=>array(User::ROLE_ADMIN, User::ROLE_MODERATOR, User::ROLE_POWERADMIN, User::ROLE_SENIORMODERATOR),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	/**
	 * Страница тендера
	 * @param integer $id
	 * @throws CHttpException 
	 */
	public function actionView($id = null)
	{
		/** @var $tender Tender */
		$tender = Tender::model()->findByPk(intval($id));
		if (is_null($tender) || in_array($tender->status, array(Tender::STATUS_DELETED, Tender::STATUS_MAKING)) )
			throw new CHttpException(404);
		
		$user = $tender->getUser();
		$responseProvider = new CActiveDataProvider('TenderResponse', array(
								'criteria' => array(
								    'condition' => 'tender_id=:tid',
								    'params' => array(':tid'=>$tender->id),
								), 
								'pagination'=>array('pageSize'=>100),
		    ));
		$serviceList = $tender->getServiceList();
		
		$files = Yii::app()->db->createCommand()->select('uf.id, uf.name, uf.ext, uf.size, tender_file.desc')
				->from('uploaded_file as uf')
				->join('tender_file', 'tender_file.file_id=uf.id')
				->where('tender_file.tender_id=:tid', array(':tid'=>  $tender->id))
				->queryAll();
		
		if ($tender->getIsClosed())
			$tender->status = Tender::STATUS_CLOSED;
		
		$this->render('view', array('tender'=>$tender, 
					'user'=>$user, 
					'responseProvider'=>$responseProvider,
					'serviceList' =>$serviceList,
					'files'=>$files,
		    ));
	}
	
	public function actionList()
	{
		// configure filter
		$model = new Tender('search');
		
		if(isset($_GET['Tender']))
			$model->attributes=$_GET['Tender'];
		
		$date_from = Yii::app()->request->getParam('date_from');
		$date_to = Yii::app()->request->getParam('date_to');
		
		$criteria = new CDbCriteria();
		// date
		if ($date_from)
			$criteria->compare('t.create_time', '>='.(strtotime($date_from)));
		if ($date_to)
			$criteria->compare('t.create_time', '<='.(strtotime($date_to)+86400));
		// city_id
		$criteria->compare('t.city_id', $model->city_id);
		
		// author_id
		$criteria->compare('t.author_id', $model->author_id);
		// id
		if ($model->id)
			$criteria->compare('t.id', explode(',', $model->id), true);
		
		// status
		if (!empty($model->status)) {
			$criteria->compare('t.status', $model->status);
		} else {
			$criteria->addCondition('(t.status IN (:stOpen, :stClosed, :stModer, :stComp, :stChanged))');

			$criteria->params[':stOpen'] = Tender::STATUS_OPEN;
			$criteria->params[':stClosed'] = Tender::STATUS_CLOSED;
			$criteria->params[':stModer'] = Tender::STATUS_MODERATING;
			$criteria->params[':stComp'] = Tender::STATUS_IN_COMPLETITION;
			$criteria->params[':stChanged'] = Tender::STATUS_CHANGED;
			
		}
		
		$criteria->order = 'id DESC';
		
		$pageSize = 50;
		
		$dataProvider = new CActiveDataProvider('Tender', array( 
		    'criteria' => $criteria, 
		    'pagination' => array('pageSize' => $pageSize, 'pageVar'=>'page'),
		));
		
		$this->render('list', array(
			'model' => $model,
			'dataProvider' => $dataProvider,
			'date_from' => $date_from,
			'date_to' => $date_to,
		));
	}

	/**
	 * Обновление тендера
	 * @param integer $id
	 * @throws CHttpException 
	 */
	public function actionUpdate($id = null)
	{
		$id = intval($id);
		if ( empty($id) )
			throw new CHttpException(404);

		/** @var $tender Tender */
		$tender = Tender::model()->findByPk( intval($id) );
		
		if(is_null($tender) || in_array($tender->status, array(Tender::STATUS_DELETED) ) )
			throw new CHttpException(404);
		
		$user = $tender->getUser();
		
		$checkedServices = isset($_POST['Tender']['service']) ? $_POST['Tender']['service'] : array();
		if (Yii::app()->request->isPostRequest && isset($_POST['Tender'])) {

			$tender->setScenario('admUpdate');

			$tender->attributes = $_POST['Tender'];

			if ($tender->cost_flag == Tender::COST_COMPARE)
				$tender->cost = 0;

			if (isset($_POST['File']['desc'])) {
				foreach ($_POST['File']['desc'] as $key => $item) {
					$link = TenderFile::model()->findByPk(array('tender_id'=>$tender->id, 'file_id'=>$key));
					if (!is_null($link)) {
						$link->desc = $item;
						$link->save();
					}
				}
			}

			if ($tender->validate() && !empty($checkedServices)) {
				$tender->afterSaveCommit = true;
				$transaction = Yii::app()->db->beginTransaction();
				try
				{
					$tender->save(false);
					/** Удаление услуг */
					Yii::app()->db->createCommand()->delete('tender_service', 'tender_id = :tid', array(':tid' => $tender->id));

					/** Вставка новых услуг */
					if (!empty($checkedServices)) {
						$sql = 'insert into tender_service (`tender_id`, `service_id`) values ';
						$cnt = 0;
						foreach ($checkedServices as $key => $value) {
							if ($cnt > 0)
								$sql .= ',';
							$sql .= '('.$tender->id.','.$key.')';
							$cnt++;
						}
						Yii::app()->db->createCommand($sql)->execute();
					}
					$transaction->commit();
					$tender->afterSave(true);

				} catch (Exception $e) {
					$transaction->rollback();
					throw new CHttpException(500);
				}
				$this->redirect("/tenders/admin/tender/view/id/{$tender->id}");
			}
		}

		if ($tender->getIsClosed())
			$tender->status = Tender::STATUS_CLOSED;
		
		$files = Yii::app()->db->createCommand()->select('uf.id, uf.name, uf.ext, uf.size, tender_file.desc')
				->from('uploaded_file as uf')
				->join('tender_file', 'tender_file.file_id=uf.id')
				->where('tender_file.tender_id=:tid', array(':tid'=>  $tender->id))
				->queryAll();

		$services = Service::model()->findAll(array('order' => 'case when parent_id = 0 then id else parent_id end,parent_id', 'limit' => 200));

		if ( !Yii::app()->getRequest()->getIsPostRequest() ) {

			$tmpArr = Yii::app()->db->createCommand('SELECT service_id FROM tender_service WHERE tender_id='.$tender->id)->queryAll();
			foreach ($tmpArr as $item) {
				$checkedServices[$item['service_id']] = 1;
			}

		}
		if (empty($checkedServices)) {
			$tender->addError('services', 'Заполните услуги');
		}

		
		return $this->render('update', array(
				'user' => $user,
				'tender' => $tender,
				'files' => $files,
				'services' => $services,
				'checkedServices' => $checkedServices,
		    ));

	}
	
	
	/**
	 * Удаление файла из тендера
	 * @return JSON
	 */
	public function actionRemovefile()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);
		$this->layout = false;
		$fileId = (int)Yii::app()->request->getParam('file_id');
		
		$fileLink = TenderFile::model()->findByAttributes(array('file_id' => $fileId) );
		if (!is_null($fileLink)) {
			$file = UploadedFile::model()->findByPk($fileId);
			if ( !is_null($file) ) {
				$file->removeOriginFile();
				$file->delete();
				$fileLink->delete();
				die( CJSON::encode( array('success'=>true) ) );
			}
		}
		die( CJSON::encode( array('error'=>true) ) );
	}
	
	/**
	 * @brief Ставит статус "удален" для тендера
	 * @param integer $id the ID of the model
	 */
	public function actionDelete($id)
	{
		if ( !Yii::app()->request->isAjaxRequest || !$id )
			throw new CHttpException(404);

		$tender = Tender::model()->findByPk((int) $id);

		if (!is_null( $tender ) ) {
			$tender->status = Tender::STATUS_DELETED;
			$tender->save(false);
			die ( CJSON::encode( array('success'=>true) ) );
		}
		die ( CJSON::encode( array('error'=>true) ) );
	}

	/**
	 * Загрузка файлов на fileApi
	 * @param tid - Tender id
	 */
	public function actionUpload($tid = null)
	{
		$tender = Tender::model()->findByPk(intval($tid));
		if (is_null($tender))
			die( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$result = TenderFile::saveFile($tender, 'file', '', false, true);

		if ($result === false)
			die ( json_encode( array('error'=>true), JSON_NUMERIC_CHECK) );

		list($tenderFile, $file) = $result;

		$html = $this->renderPartial('_fileItem', array('tenderFile'=>$tenderFile, 'file'=>$file), true);
		die( json_encode( array('success'=>true, 'html'=>$html), JSON_NUMERIC_CHECK ) );
	}
}