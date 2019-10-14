<?php

class MainvendorController extends AdminController
{
	public $layout = 'webroot.themes.myhome.views.layouts.backend';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl',
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
				'roles'=>array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_SENIORMODERATOR,
				),
			),
			array('deny',  // deny all users
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
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new MainUnit('vendor');

		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$mainRooms = MainRoom::model()->findAllByAttributes(array('status'=>MainRoom::STATUS_ENABLED), array('order'=>'position ASC'));

		$sCategory = $request->getParam('MainUnitCategory', array());
		if (isset( $sCategory[1] ))
			unset($sCategory[1]); // Remove root
		$sRooms = $request->getParam('MainUnitRoom', array());

		if (isset($_POST['MainUnit'])) {
			$model->attributes = $_POST['MainUnit'];
			$model->type_id = MainUnit::TYPE_VENDOR;

			$dateTo = @strtotime( $request->getParam('date_to') );
			$dateFrom = @strtotime( $request->getParam('date_from') );

			$model->start_time = ($dateTo !== -1) ? $dateTo : 0;
			$model->end_time = ($dateFrom !== -1) ? $dateFrom : 0;

			/** @var $origin Vendor */
			$origin = $model->getOrigin();
			if ( $model->validate() && !is_null($origin) ) {
				$model->file_id = $origin->image_id;
				$model->save(false);
				$model->position = $model->id;
				$model->save(false);

				MainUnitCategory::updateCategories($sCategory, $model->id);
				MainUnitRoom::updateRooms($sRooms, $model->id);

				$this->redirect(array('index'));
			}
		}

		$this->render('create',array(
			'model'=>$model,
			'mainRooms'=>$mainRooms,
			'sCategory'=>$sCategory,
			'sRooms'=>$sRooms,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$id = intval($id);
		/** @var $model MainUnit */
		$model = MainUnit::model()->findByPk($id, 'type_id=:tid', array(':tid'=>MainUnit::TYPE_VENDOR));

		if (is_null($model)) // Не блокируем удаленные
			throw new CHttpException(404);

		$model->setScenario('vendor');

		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$mainRooms = MainRoom::model()->findAllByAttributes(array('status'=>MainRoom::STATUS_ENABLED), array('order'=>'position ASC'));

		$sCategory = $request->getParam('MainUnitCategory', array());
		if (isset( $sCategory[1] ))
			unset($sCategory[1]); // Remove root
		$sRooms = $request->getParam('MainUnitRoom', array());

		if(isset($_POST['MainUnit'])) {
			$model->attributes=$_POST['MainUnit'];

			$model->attributes = $_POST['MainUnit'];
			$model->type_id = MainUnit::TYPE_VENDOR;

			$dateTo = @strtotime( $request->getParam('date_to') );
			$dateFrom = @strtotime( $request->getParam('date_from') );

			$model->start_time = ($dateTo !== -1) ? $dateTo : 0;
			$model->end_time = ($dateFrom !== -1) ? $dateFrom : 0;

			/** @var $origin Vendor */
			$origin = $model->getOrigin();
			if ( $model->validate() && !is_null($origin) ) {
				$model->file_id = $origin->image_id;
				$model->save(false);

				MainUnitCategory::updateCategories($sCategory, $model->id);
				MainUnitRoom::updateRooms($sRooms, $model->id);

				$this->redirect(array('index'));
			}
		}

		if (!$request->getIsPostRequest()) {
			$sCategory = MainUnitCategory::getSelectedCategories($model->id);
			$sRooms = MainUnitRoom::getSelectedRooms($model->id);
		}

		$this->render('update',array(
			'model'=>$model,
			'mainRooms'=>$mainRooms,
			'sCategory'=>$sCategory,
			'sRooms'=>$sRooms,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete()
	{
		$id = intval( Yii::app()->getRequest()->getParam('id') );

		if (!Yii::app()->getRequest()->getIsAjaxRequest() || empty($id))
			throw new CHttpException(404);

		$model = MainUnit::model()->findByPk($id);
		if (is_null($model))
			throw new CHttpException(404);

		$model->status = MainUnit::STATUS_DELETED;
		$model->save(false);
		die ( CJSON::encode( array('success'=>true) ) );

	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model=new MainUnit('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['MainUnit']))
			$model->attributes=$_GET['MainUnit'];

		$this->render('index',array(
			'model'=>$model,
		));
	}

	/**
	 * Move up item position
	 * @return JSON
	 * @author Alexey Shvedov
	 */
	public function actionUp()
	{
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('id') );
		if ( !$request->getIsAjaxRequest() || empty($id) )
			throw new CHttpException(404);

		/** @var $current MainUnit */
		$current = MainUnit::model()->findByPk($id);

		if (is_null($current) || $current->type_id != MainUnit::TYPE_VENDOR)
			die ( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'status IN (:st1, :st2) AND position<:position AND type_id=:tid';
		$criteria->order = 'position DESC';
		$criteria->params = array(
			':position' => $current->position,
			':st1'=>MainUnit::STATUS_ENABLED,
			':st2'=>MainUnit::STATUS_DISABLED,
			':tid'=>MainUnit::TYPE_VENDOR,
		);

		/** @var $next MainUnit */
		$next = MainUnit::model()->find($criteria);
		if (is_null($next) || $next->type_id != MainUnit::TYPE_VENDOR)
			die ( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$tmp = $current->position;
		$current->position = $next->position;
		$next->position = $tmp;
		$current->save(false);
		$next->save(false);
		die ( json_encode( array('success'=>true), JSON_NUMERIC_CHECK ) );
	}

	/**
	 * Move down item position
	 * @return JSON
	 * @author Alexey Shvedov
	 */
	public function actionDown()
	{
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('id') );
		if ( !$request->getIsAjaxRequest() || empty($id) )
			throw new CHttpException(404);

		/** @var $current MainUnit */
		$current = MainUnit::model()->findByPk($id);

		if (is_null($current) || $current->type_id != MainUnit::TYPE_VENDOR)
			die ( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'status IN (:st1, :st2) AND position>:position AND type_id=:tid';
		$criteria->order = 'position ASC';
		$criteria->params = array(
			':position' => $current->position,
			':st1'=>MainRoom::STATUS_ENABLED,
			':st2'=>MainRoom::STATUS_DISABLED,
			':tid'=>MainUnit::TYPE_VENDOR,
		);

		/** @var $next MainUnit */
		$next = MainUnit::model()->find($criteria);
		if (is_null($next) || $next->type_id != MainUnit::TYPE_VENDOR)
			die ( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$tmp = $current->position;
		$current->position = $next->position;
		$next->position = $tmp;
		$current->save(false);
		$next->save(false);
		die ( json_encode( array('success'=>true), JSON_NUMERIC_CHECK ) );
	}

	/**
	 * @brief Обрабатывает запрос на смену статуса из контекстного меню
	 * @return JSON
	 */
	public function actionAxStatusUpdate()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('id') );
		$status = intval( $request->getParam('status') );
		if ( !$request->getIsAjaxRequest() || empty($id) || !isset(MainUnit::$statusNames[$status]))
			throw new CHttpException(404);

		/** @var $current MainUnit */
		$current = MainUnit::model()->findByPk($id);
		if ($current === null)
			throw new CHttpException(404);

		$current->status = $status;
		$current->save(false);

		die ( json_encode( array('success'=>true), JSON_NUMERIC_CHECK ) );
	}

	/**
	 * @brief Возвращает выпадающий список для смены статуса пользователя
	 * @param integer $uid user id
	 */
	public function actionAxStatusList()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('id') );
		if ( !$request->getIsAjaxRequest() || empty($id) )
			throw new CHttpException(404);

		/** @var $current MainUnit */
		$current = MainUnit::model()->findByPk($id);
		if ($current === null)
			throw new CHttpException(404);

		$statusList = '';
		foreach (MainUnit::$statusNames as $key => $status) {
			if ($current->status == $key) {
				$statusList.= CHtml::tag('li', array(
					'data-id'=>$current->id,
					'status-id'=>$current->status,
					'class'=>'current-status',
				), $status);
			} else {
				$statusList.= CHtml::tag('li', array('data-id'=>$current->id, 'status-id'=>$key), $status);
			}
		}
		die ( json_encode( array('success'=>true, 'html'=>$statusList), JSON_NUMERIC_CHECK ) );
	}
}
