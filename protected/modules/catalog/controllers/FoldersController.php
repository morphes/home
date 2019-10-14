<?php

class FoldersController extends FrontController
{

	public function filters()
	{
		return array(
			'accessControl',
		);
	}

	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' action
				'actions'=>array('folder', 'list', 'ajaxfolder' ),
				'users'=>array('*'),
			      ),
			array('allow',
				'roles'=>array(User::ROLE_MALL_ADMIN),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Добавление новой папки
	 * через ajax
	 */
	public function actionAjaxAddFolder()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		$htmlItem = '';
		$errors = '';
		$folderId = null;

		$post = Yii::app()->request->getPost('item');

		$model = new CatFolders();

		$model->attributes = $post;
		$model->status = CatFolders::STATUS_EMPTY;

		$model->user_id = Yii::app()->user->id;

		if ($model->save()) {
			$success = true;
			$folderId = $model->id;
			$htmlItem = $this->renderPartial('//widget/folders/_oneItem',
				array('item' => $model), true);
		} else {
			$success = false;
			$errors = $model->getErrors();
		}

		die(json_encode(array(
			'success'  => $success,
			'errors'   => $errors,
			'htmlItem' => $htmlItem,
			'id'	   => $folderId,
		), JSON_NUMERIC_CHECK));

	}


	/**
	 * Удаление папки через Ajax
	 * Папка не удаляется из БД
	 * а ей проставляется статус
	 * DELETED
	 * @throws CHttpException
	 *
	 */
	public function actionAjaxDelFolder()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		$errors = '';
		$post = Yii::app()->request->getPost('item');

		if(!isset($post['id']))
		{
			throw new CHttpException(400, 'Error data!');
		}

		$model = CatFolders::model()->findByPk($post['id']);

		//Проверяем, получена ли модель и владеет ли текущий пользователь
		//папкой
		if ($model && $model->user_id == Yii::app()->user->id) {
			//Проставляем статус DELETED
			$model->status = CatFolders::STATUS_DELETED;
			if ($model->save()) {
				$success = true;
			} else {
				$errors = $model->getErrors();
				$success = false;
			}
		} else {
			$success = false;
		}

		die(json_encode(array(
			'success' => $success,
			'errors'  => $errors,
		), JSON_NUMERIC_CHECK));
	}


	/**
	 * Метод изменения наименования
	 * папки
	 * @throws CHttpException
	 */
	public function actionAjaxUpdateFolder()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		$errors = '';
		$post = Yii::app()->request->getPost('item');

		if(!isset($post['id']))
		{
			throw new CHttpException(400, 'Error data!');
		}

		if(!isset($post['name']))
		{
			throw new CHttpException(400, 'Error data!');
		}

		$model = CatFolders::model()->findByPk($post['id']);

		if ($model && $model->user_id == Yii::app()->user->id) {

			$model->name = $post['name'];
			if ($model->save()) {
				$success = true;
			} else {
				$errors = $model->getErrors();
				$success = false;
			}
		} else {
			$success = false;
		}

		die(json_encode(array(
			'success' => $success,
			'errors'  => $errors,
		), JSON_NUMERIC_CHECK));
	}


	/**
	 * Метод добавляет товар
	 * в папку.
	 * @throws CHttpException
	 */
	public function actionAjaxAddItem()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		$post = Yii::app()->request->getPost('item');

		if(!isset($post['folderId']) || !isset($post['modelId']))
		{
			throw new CHttpException(400, 'Error data!');
		}
		$errors = '';

		$model = new CatFolderItem();

		$maxPosition = CatFolderItem::getMaxPosition((int)$post['folderId']);

		$model->folder_id = (int)$post['folderId'];

		$model->model_id = (int)$post['modelId'];

		$model->position = $maxPosition+1;

		if($model->Folder->user_id!== Yii::app()->user->id){
			throw new CHttpException(400, 'Error auth');
		}

		// Проверка на дуплшикаты товара в папке
		$folderItem = CatFolderItem::model()->findByAttributes(array('folder_id' => $model->folder_id, 'model_id' => $model->model_id));

		if($folderItem){

			$success = false;
			$errors = 'Duplicate';
			die(json_encode(array(
				'success' => $success,
				'errors'  => $errors,
			), JSON_NUMERIC_CHECK));
		}

		if($model->save())
		{
			$success = true;
		}
		else
		{
			$success = false;
		}

		die(json_encode(array(
			'success' => $success,
			'errors'  => $errors,
		), JSON_NUMERIC_CHECK));
	}


	/**
	 * Метод удаляет товар
	 * из папки.
	 * @throws CHttpException
	 */
	public function actionAjaxDelItem()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		$post = Yii::app()->request->getPost('item');

		if(!isset($post['id']))
		{
			throw new CHttpException(400, 'Error data!');
		}

		$id = (int)$post['id'];

		$result = CatFolderItem::model()->findByPk($id)->delete();

		if($result)
		{
			$success = true;
		}
		else
		{
			$success = false;
		}

		die(json_encode(array(
			'success' => $success,
		), JSON_NUMERIC_CHECK));


	}


	/**
	 * Получение товаров в
	 * папке
	 * @param $id
	 *
	 * @throws CHttpException
	 */
	public function actionFolder($id)
	{
		$this->layout = '//layouts/layoutBm';
		$this->bodyClass = "goods folders bm-promo";
		$id = (int)$id;
		$limit = CatFolderItem::PAGE_SIZE_LIMIT;

		$folderModel = CatFolders::model()->findByPk($id);
		$isOwner = $folderModel->isOwner(Yii::app()->user->id);

		if (!$folderModel) {
			throw new CHttpException(404);
		}



		$criteria = new CDbCriteria;

		$criteria->condition = 'folder_id = :id';
		$criteria->params = array(':id' => $id);
		$criteria->order = 'position ASC';

		$this->setPageTitle($folderModel->name);

		$folderItemProvider = new CActiveDataProvider(CatFolderItem::model(), array(
			'criteria'   => $criteria,
			'pagination' => array(
				'pageSize'    => (int)$limit,
				'pageVar'     => 'page',
				'currentPage' => 0
			),
		));

		$this->render('//catalog/folders/folder',
			array(
				'folderItemProvider' => $folderItemProvider,
				'folder'             => $folderModel,
				'limit'              => $limit,
				'isOwner'	     => $isOwner,
			)
		);
	}


	/**
	 * Ajax метод для получения
	 * следующей порции товаров
	 * в папке
	 * @throws CHttpException
	 */
	public function actionAjaxFolder()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}


		$limit = CatFolderItem::PAGE_SIZE_LIMIT;

		$id = Yii::app()->request->getParam('id');

		$folderModel = CatFolders::model()->findByPk($id);

		$isOwner = $folderModel->isOwner(Yii::app()->user->id);

		if (!$folderModel) {
			throw new CHttpException(404);
		}

		$page = Yii::app()->request->getParam('page');

		$criteria = new CDbCriteria;

		$criteria->condition = 'folder_id = :id';
		$criteria->params = array(':id' => $id);
		$criteria->order = 'position ASC';
		$folderItemProvider = new CActiveDataProvider(CatFolderItem::model(), array(
			'criteria'   => $criteria,
			'pagination' => array(
				'pageSize'    => (int)$limit,
				'pageVar'     => 'page',
				'currentPage' => $page,
			),
		));

		$html = $this->renderPartial('//catalog/folders/_folder',
			array('folderItemProvider' => $folderItemProvider, 'id' => $id, 'isOwner' => $isOwner), true);

		die(json_encode(array(
			'success' => true,
			'html'    => $html,
		), JSON_NUMERIC_CHECK));
	}


	/**
	 * Список папок
	 */
	public function actionList()
	{
		$this->layout = '//layouts/layoutBm';
		$this->bodyClass = "folders bm-promo";
		$this->setPageTitle('Спецпредложения - Myhome.ru');
		$models = CatFolders::model()->findAllByAttributes(array('status' => CatFolders::STATUS_NOT_EMPTY));

		$this->render('//catalog/folders/list', array('models' => $models));
	}


	/**
	 * перемещение элемента в папке
	 * @throws CHttpException
	 */
	public function actionMoveItemAjax()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		$post = Yii::app()->request->getPost('item');

		$itemId = $post['itemId'];
		$itemPosition = $post['itemPosition'];

		$model = CatFolderItem::model()->findByPk($itemId);

		$model->position = $itemPosition;

		$model->save();

		die(json_encode(array(
			'success' => true,
		), JSON_NUMERIC_CHECK));
	}


	public function actionAjaxGetEditForm()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if (!$request->getIsAjaxRequest()) {
			throw new CHttpException(400);
		}

		$folderId = intval($request->getParam('folder_id'));
		/** @var $folder CatFolders */
		$folder = CatFolders::model()->findByPk($folderId);

		if ($folder===null || $folder->status==CatFolders::STATUS_DELETED || $folder->user_id!=Yii::app()->getUser()->getId()) {
			throw new CHttpException(404);
		}

		$photos = $folder->getPhotos();

		$html = $this->renderPartial('_imageForm', array(
			'folder'=>$folder,
			'photos'=>$photos,
		), true);

		die (json_encode( array('success'=>true, 'html'=>$html) ));

	}

	/**
	 * Замена обложки на папке
	 * @throws CHttpException
	 */
	public function actionAjaxSetCover()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if (!$request->getIsAjaxRequest()) {
			throw new CHttpException(400);
		}

		$folderId = intval($request->getParam('folder_id'));
		/** @var $folder CatFolders */
		$folder = CatFolders::model()->findByPk($folderId);
		// folder access control
		if ($folder===null || $folder->status==CatFolders::STATUS_DELETED || $folder->user_id!=Yii::app()->getUser()->getId()) {
			throw new CHttpException(404);
		}

		$imageId = intval($request->getParam('image_id'));
		if (!empty($imageId)) { // выбрана обложка
			// access control for images
			$photos = $folder->getPhotos();
			if (empty($photos[$imageId])) {
				throw new CHttpException(403);
			}
			$folder->image_id = $imageId;
		} else { // загружена обложка
			$folder->setImageType('folder');
			$uFile = UploadedFile::loadImage($folder, 'file');
			if (!$uFile) {
				throw new CHttpException(400);
			}
			$folder->image_id = $uFile->id;
		}

		$folder->save(false, array('image_id', 'update_time'));
		die( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
	}


	/**
	 * Получает список магазинов
	 * который продаю товар
	 * @throws CHttpException
	 */
	public function actionGetStoresByProductAjax()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		$post = Yii::app()->request->getPost('item');

		$productId = (int)$post['productId'];

		$productModel = Product::model()->findByPk($productId);

		$productIds = Product::getStoresInMall($productId);

		$stores = Store::model()->findAllByPk($productIds);

		$html = $this->renderPartial('//catalog/folders/_discountFoldersList',
			array('stores' => $stores,
			      'model' => $productModel
			), true);

		die (json_encode( array('success'=>true, 'html'=>$html) ));
	}


	/**
	 * Добавляет скидку в магазин
	 * @throws CHttpException
	 */
	public function actionAddDiscountAjax()
	{
		if (!Yii::app()->request->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Only Ajax!');
		}

		$post = $_POST;

		foreach ($post as $key => $value) {
			$dayStart = strtotime($value['firstDay']);
			$dayEnd = strtotime($value['lastDay']);
			$modelId = (int)$value['modelId'];
			$storeId = $key;
			$number = (int)$value['discNumber'];

			if (!empty($number)) {
				$storePrice = StorePrice::model()->findByPk(array('store_id' => $storeId, 'product_id' => $modelId));

				if ($storePrice) {
					$discountPercent = $storePrice->convertNumberDiscount($number);
					$storePrice->discount = $discountPercent;
				} else {
					die (json_encode(array('success' => false)));
				}

				CatFolderDiscount::model()->deleteAllByAttributes(
					array(
						'model_id' => $modelId,
						'store_id' => $storeId
					));

				$catFolderDiscount = new CatFolderDiscount;
				$catFolderDiscount->model_id = $modelId;
				$catFolderDiscount->store_id = $storeId;
				$catFolderDiscount->discount = $discountPercent;
				$catFolderDiscount->status = $catFolderDiscount::STATUS_ACTIVE;

				if (!$dayStart || $dayStart <= time()) {
					$storePrice->save();
					if ($dayEnd) {
						$catFolderDiscount->date_end = $dayEnd;
						$catFolderDiscount->save();
					}
				} else {
					$storePrice->discount = 0;
					$storePrice->save();
					$catFolderDiscount->date_start = $dayStart;
					if ($dayEnd) {
						$catFolderDiscount->date_end = $dayEnd;
					}
					$catFolderDiscount->save();
				}
			}
		}
		die (json_encode(array('success' => true)));
	}
}
