<?php

class ContractorController extends AdminController
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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'roles'=>array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_MODERATOR,
				),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		/** @var $model Contractor */
		$model=Contractor::model()->findByPk( intval($id) );
		if(is_null($model))
			throw new CHttpException(404);

		$contacts = ContractorContact::model()->findAllByAttributes(array('contractor_id'=>$model->id), array('index'=>'id'));
		$this->render('view',array(
			'model'=>$model,
			'contacts'=>$contacts,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Contractor;

		if(isset($_POST['Contractor']))
		{
			$model->attributes=$_POST['Contractor'];

			if($model->validate()) {
				$model->save(false);
				$this->redirect(array('update','id'=>$model->id));
			}
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
		/** @var $model Contractor */
		$model=Contractor::model()->findByPk( intval($id) );
		if(is_null($model))
			throw new CHttpException(404);

		$contacts = ContractorContact::model()->findAllByAttributes(array('contractor_id'=>$model->id), array('index'=>'id'));

		if(isset($_POST['Contractor']))
		{
			$hasErrors = false;
			if (isset($_POST['ContractorContact'])) {
				foreach ($_POST['ContractorContact'] as $key=>$values) {
					if ( !isset($contacts[$key]) || $contacts[$key]->contractor_id !== $model->id)
						continue;

					$contacts[$key]->attributes = $values;
					if ( !$contacts[$key]->save() )
						$hasErrors = true;
				}
			}

			$model->attributes=$_POST['Contractor'];
			if ( $model->save() && !$hasErrors ) {
				$this->redirect(array('view','id'=>$model->id));
			}

		}

		$this->render('update',array(
			'model'=>$model,
			'contacts' => $contacts,
		));
	}

	/**
	 * Deletes a particular model.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{

		/** @var $model Contractor */
		$model=Contractor::model()->findByPk( intval($id) );
		if(is_null($model))
			throw new CHttpException(404);

		$model->status = Contractor::STATUS_DELETED;
		$model->save(false);
		$model->removeLinkedData();

		if (Yii::app()->getRequest()->getIsAjaxRequest()) {
			die ( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
		} else {
			$this->redirect(array('index'));
		}
	}

	/**
	 * Статистика для контрагента
	 * @param $id
	 */
	public function actionStatistic($id)
	{
		/** @var $model Contractor */
		$model=Contractor::model()->findByPk( intval($id) );
		if(is_null($model))
			throw new CHttpException(404);

		/* Получение всей статистики */
		$sql = 'select cc.id,cc.name, tmp.cnt, tmp.category_id, tmp.vendor_id, v.name as vendor_name '
			.'FROM cat_category as cc '
			.'INNER JOIN ( '
				.'SELECT count(id) as cnt, category_id, p.vendor_id FROM cat_product as p '
				.'INNER JOIN cat_vendor_contractor as cvc ON cvc.vendor_id=p.vendor_id '
				.'WHERE cvc.contractor_id=:cid AND p.status=:st '
				.'GROUP BY p.vendor_id, category_id '
			.') as tmp ON tmp.category_id = cc.id '
			.'INNER JOIN cat_vendor as v ON v.id=tmp.vendor_id '
			.'ORDER BY tmp.vendor_id';

		$id = $model->id;
		$status = Product::STATUS_ACTIVE;
		$data = Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':cid', $id)->bindParam(':st', $status)->queryAll();

		$this->render('statistic', array(
					'model'=>$model,
					'data'=>$data
		));

	}

	public function actionRemovecontact()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		$contactId = intval( $request->getParam('contact_id') );
		$contractorId = intval( $request->getParam('contractor_id') );

		/** @var $contractor Contractor */
		$contractor = Contractor::model()->findByPk( $contractorId );
		$contact = ContractorContact::model()->findByPk( $contactId );
		if ( is_null($contact) ||
			is_null($contractor) ||
			$contractor->status == Contractor::STATUS_DELETED ||
			$contact->contractor_id !== $contractor->id
		)
			throw new CHttpException(404);

		$contact->delete();

		die ( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model = new Contractor('search');
		$model->unsetAttributes();

		if (isset($_GET['Contractor']))
			$model->attributes = $_GET['Contractor'];

		$this->render('index',array(
			'model' => $model,
		));
	}

	/**
	 * Html разметка для нового контакта
	 * @throws CHttpException
	 */
	public function actionGetcontact()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		$contractorId = intval( $request->getParam('contractor_id') );
		/** @var $contractor Contractor */
		$contractor = Contractor::model()->findByPk($contractorId);
		if (is_null($contractor) || $contractor->status == Contractor::STATUS_DELETED)
			throw new CHttpException(404);

		$model = new ContractorContact();
		$model->contractor_id = $contractor->id;
		$model->save(false);

		$html = $this->renderPartial('_contactItem', array('model'=>$model), true);
		die ( json_encode(array('success'=>true, 'data'=>$html), JSON_NUMERIC_CHECK) );
	}

	/**
	 * Html разметка для производителя
	 * @throws CHttpException
	 */
	public function actionGetvendor()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		$contractorId = intval( $request->getParam('contractor_id') );
		$vendorId = intval( $request->getParam('vendor_id') );

		/** @var $contractor Contractor */
		$contractor = Contractor::model()->findByPk($contractorId);
		$vendor = Vendor::model()->findByPk($vendorId);

		$link = VendorContractor::model()->findByPk(array('vendor_id'=>$vendorId, 'contractor_id'=>$contractorId));

		if (is_null($contractor) ||
			$contractor->status == Contractor::STATUS_DELETED ||
			is_null($vendor)
		)
			throw new CHttpException(404);

		if (!is_null($link))
			die ( json_encode(array('error'=>true, 'message'=>'Производитель уже привязан'), JSON_NUMERIC_CHECK) );


		$model = new VendorContractor();
		$model->contractor_id = $contractor->id;
		$model->vendor_id = $vendor->id;
		$model->save(false);

		$html = $this->renderPartial('_vendorItem', array('vendor'=>$vendor), true);
		die ( json_encode(array('success'=>true, 'data'=>$html), JSON_NUMERIC_CHECK) );
	}

	/**
	 * Удаление связки с производителем
	 * @throws CHttpException
	 */
	public function actionRemovevendor()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		$contractorId = intval( $request->getParam('contractor_id') );
		$vendorId = intval( $request->getParam('vendor_id') );

		$link = VendorContractor::model()->findByPk(array('vendor_id'=>$vendorId, 'contractor_id'=>$contractorId));

		if (is_null($link))
			throw new CHttpException(404);

		$link->delete();

		die ( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
	}


	/**
	 * Удаление связки с магазином
	 * @throws CHttpException
	 */
	public function actionRemovestore()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		$contractorId = intval( $request->getParam('contractor_id') );
		$storeId = intval( $request->getParam('store_id') );

		$contractor = Contractor::model()->findByPk($contractorId);
		/** @var $store Store */
		$store = Store::model()->findByPk($storeId);


		if (is_null($contractor) ||
			$contractor->status == Contractor::STATUS_DELETED ||
			is_null($store)
		)
			throw new CHttpException(404);

		$store->contractor_id = null;
		$store->save(false);

		die ( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
	}

	/**
	 * Html разметка для магазина
	 * @throws CHttpException
	 */
	public function actionGetstore()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		$contractorId = intval( $request->getParam('contractor_id') );
		$storeId = intval( $request->getParam('store_id') );
		/* Флаг подтверждения перепривязки */
		$confirm = (bool)$request->getParam('confirm');

		/** @var $store Store */
		$store = Store::model()->findByPk($storeId);
		/** @var $contractor Contractor */
		$contractor = Contractor::model()->findByPk($contractorId);
		if (is_null($contractor) ||
			$contractor->status == Contractor::STATUS_DELETED ||
			is_null($store)
		)
			throw new CHttpException(404);

		if ($store->contractor_id == $contractor->id)
			die ( json_encode(array('error'=>true, 'message'=>'Магазин уже привязан'), JSON_NUMERIC_CHECK) );

		if ( is_null($store->contractor_id) || $confirm ) {
			$store->contractor_id = $contractor->id;
			$store->save(false);
			$html = $this->renderPartial('_storeItem', array('store'=>$store), true);
			die ( json_encode(array('success'=>true, 'data'=>$html), JSON_NUMERIC_CHECK) );
		}

		$message = 'Магазин уже привязан ID контрагента: '.$store->contractor_id.'. Перепривязать?';

		die ( json_encode(array('confirm'=>true, 'message'=>$message), JSON_NUMERIC_CHECK) );
	}

	/**
	 * Export CSV
	 */
	public function actionExport()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if (!$request->getIsAjaxRequest())
			throw new CHttpException(400);

		$id = intval( $request->getParam('contractor_id') );

		/** @var $model Contractor */
		$model = Contractor::model()->findByPk( $id );
		if (is_null($model))
			throw new CHttpException(404);

		$sql = 'SELECT vendor_id FROM cat_vendor_contractor WHERE contractor_id=:cid';

		$idList = Yii::app()->dbcatalog2->createCommand($sql)->bindParam(':cid', $id)->queryColumn();

		$taskExport = new CatCsv();
		$taskExport->user_id  = Yii::app()->user->id;
		$taskExport->item_id = $model->id;
		$taskExport->action   = 'export';
		$taskExport->type     = CatCsv::TYPE_CONTRACTOR;
		$taskExport->status   = CatCsv::STATUS_NEW;
		$taskExport->data     = serialize(array('vendor_ids' => $idList));
		$taskExport->progress = serialize(array('totalItems' => 0, 'doneItems' => 0));


		if ( ! $taskExport->save()) {
			die(json_encode(array(
				'error' => true,
				'message' => 'Ошибка создания задачи на экспорт',
			)));
		}

		// Добавляем очередь в воркер
		Yii::app()->gearman->appendJob('catCsv:exportForVendors', array(
			'task_id' => $taskExport->id,
		));

		$url = '/catalog2/admin/catCsv/list';
		die(json_encode(array('success' => true, 'redirectUrl'=>$url)));
	}

}
