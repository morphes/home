<?php

class BannerController extends AdminController
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
				'roles'=>array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_MODERATOR,
				),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		// создание нового баннера и редирект на страницу его редактирования
		// необходимо для защиты от создания пустых баннеров при рефреше страницы создания

		// инициализация нового баннера
		$model=new BannerItem('init');
		$model->setAttributes(array(
			'user_id'=>Yii::app()->user->id,
			'status'=>BannerItem::STATUS_INACTIVE,
			'type_id'=>BannerItem::TYPE_HORIZONTAL,
		));
		$model->save();
		// инициализация пустой секции ротации баннера
		$model->createEmptySection();
		// редирект с указанием id баннера для редактирования
		$this->redirect($this->createUrl('update', array(
			'id'=>$model->id
		)));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		if(isset($_POST['BannerItem']))
		{
			$model->attributes=$_POST['BannerItem'];
			$errors = false;
			if( !$model->validate() )
				$errors = true;

			foreach($model->itemSections as $is) {
				if ( !$is->validate() )
					$errors = true;
			}

			if ( !$errors ) {
				$model->save();
				$model->updateRotation();
				$this->redirect(array('index'));
			}
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
			/**
			 * @var $model BannerItem
			 */
			$model = $this->loadModel($id);
			$model->forceDelete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Manages all models.
	 */
	public function actionIndex()
	{
		$model=new BannerItem('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['BannerItem']))
			$model->attributes=$_GET['BannerItem'];

		$this->render('index',array(
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
		$model=BannerItem::model()->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='banner-item-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	/**
	 * Создание новой привязки баннера к секции
	 */
	public function actionAjaxCreateItemSection()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(400);

		$banner = BannerItem::model()->findByPk((int) Yii::app()->request->getParam('item_id'));
		if (!$banner)
			throw new CHttpException(404);

		$itemSection = $banner->createEmptySection();

		$responseHtml = $this->renderPartial('_sectionForm', array(
			'model'=>$banner,
			'itemSection'=>$itemSection,
		), true);

		die(CJSON::encode(array('success'=>true, 'html'=>$responseHtml)));
	}

	/**
	 * Удаление связки баннер-раздел
	 * @throws CHttpException
	 */
	public function actionAjaxDeleteItemSection()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(400);

		$itemSection = BannerItemSection::model()->findByPk((int) Yii::app()->request->getParam('itemSectionid'));
		if (!$itemSection)
			throw new CHttpException(404);

		$itemSection->forceDelete();

		die(CJSON::encode(array('success'=>true)));
	}

	/**
	 * Обновление данных связки баннер-раздел
	 * @throws CHttpException
	 */
	public function actionAjaxUpdateItemSection($item_section_id)
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(400);

		$itemSection = BannerItemSection::model()->findByPk((int) $item_section_id);
		if (!$itemSection)
			throw new CHttpException(404);

		$itemSection->attributes = $_POST['BannerItemSection'];
		if ($itemSection->save()) {

			$itemSection->updateGeo();
			die(CJSON::encode(array('success'=>true)));
		} else {

			$errors = '';
			foreach($itemSection->getErrors() as $attr)
				foreach($attr as $error)
					$errors.= $error . "<br>";

			die(CJSON::encode(array('success'=>false, 'errors'=>$errors)));
		}
	}

	/**
	 * Создает новую привязку баннер-раздел к гео
	 * @throws CHttpException
	 */
	public function actionAjaxCreateItemSectionGeo($item_section_id)
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(400);

		$itemSection = BannerItemSection::model()->findByPk((int) $item_section_id);
		if (!$itemSection)
			throw new CHttpException(404);

		$type = Yii::app()->request->getParam('type');
		$geo_id = (int) Yii::app()->request->getParam('geo_id');

		if ( !in_array($type, array('city_id', 'region_id', 'country_id')) )
			throw new CHttpException(400);

		$geo = $itemSection->assignToGeo($type, $geo_id);

		if ( !$geo->getErrors() )
			die(CJSON::encode(array('success'=>true, 'html'=>$this->renderPartial('_geoForm', array('geo'=>$geo), true))));

		else {
			$errors = '';
			foreach($geo->getErrors() as $attr)
				foreach($attr as $error)
					$errors.= $error . "<br>";
			die(CJSON::encode(array('success'=>false, 'errors'=>$errors)));
		}
	}

	/**
	 * Удаление гео-привязки баннера
	 * @throws CHttpException
	 */
	public function actionAjaxDeleteItemSectionGeo()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(400);

		$geo = BannerItemSectionGeo::model()->findByPk((int) Yii::app()->request->getParam('geo_id'));

		$geo->forceDelete();

		die(CJSON::encode(array('success'=>true)));
	}

	/**
	 * Обновление баннера
	 * @param $id
	 * @throws CHttpException
	 */
	public function actionAjaxUpdateBannerItem($id)
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(400);

		$banner = BannerItem::model()->findByPk((int) $id);
		if (!$banner)
			throw new CHttpException(404);

		$banner->attributes = $_POST['BannerItem'];
		if ($banner->save())
			die(CJSON::encode(array('success'=>true)));

		else {
			$errors = '';
			foreach($banner->getErrors() as $attr)
				foreach($attr as $error)
					$errors.= $error . "<br>";
			die(CJSON::encode(array('success'=>false, 'errors'=>$errors)));
		}
	}


	/**
	 * Загрузка файлов баннера
	 * @param string $type "image"/"swf" - тип загружаемого файла
	 *
	 * @throws CHttpException
	 */
	public function actionFileUpload($type)
	{
		$banner = BannerItem::model()->findByPk((int) Yii::app()->request->getParam('item_id'));
		if (!$banner)
			throw new CHttpException(404);

		// валидация swf-файла
		if ( $type == 'swf' ) {

			$fileInstance = $banner->swfFile = CUploadedFile::getInstance($banner,'swfFile');
			$validationSuccess = $banner->validate(array('swfFile'));
			$attrForSave = 'swf_file_id';

		// валидация статического файла
		} elseif ( $type == 'image' ) {

			$fileInstance = $banner->imageFile = CUploadedFile::getInstance($banner,'imageFile');
			$validationSuccess = $banner->validate(array('imageFile'));
			$attrForSave = 'file_id';

		} else {
			throw new CHttpException(400);
		}

		// генерация ответа с ошибкой в случае неудачной валидации
		if ( !$validationSuccess ) {
			$errors = '';
			foreach($banner->getErrors() as $attr)
				foreach($attr as $error)
					$errors.= $error . "\n";

			die(CJSON::encode(array('success'=>false, 'errors'=>$errors)));
		}

		// создание объекта файла
		$uFile = new UploadedFile();
		$uFile->file = $fileInstance;
		$uFile->author_id = Yii::app()->user->id;
		$uFile->path = 'banner' . '/' . $banner->id;
		$uFile->name = 'banner_' . $banner->id . '_' . mt_rand(1, 99) . '_' . time();
		$uFile->ext = $uFile->file->extensionName;
		$uFile->size = $uFile->file->size;
		$uFile->type = UploadedFile::BANNER_TYPE;

		// создание директории для сохранения файла
		$folder = UploadedFile::PUBLIC_PREFIX.'/'.$uFile->path;
		if ( !file_exists($folder))
			mkdir($folder, 0755, true);

		// сохранения файла в ФС и его объекта в БД
		if ($uFile->save() && $uFile->file->saveAs($folder. '/' .$uFile->name. '.' .$uFile->ext) ) {

			// сохранение id файла в соответствующем атрибуте объекта баннера
			$banner->$attrForSave = $uFile->id;
			if ( $banner->save(false, array($attrForSave)) ) {
				die(CJSON::encode(array('success'=>true, 'html'=>$this->renderPartial('_bannerFile_' . $type, array('model'=>$banner), true))));
			}
		}

		die(CJSON::encode(array('success'=>false)));
	}


	/**
	 * Удаление файлов баннеров
	 */
	public function actionAjaxDeleteFile()
	{
		$item_id = Yii::app()->request->getParam('item_id');
		$type = Yii::app()->request->getParam('type');

		$banner = BannerItem::model()->findByPk((int) $item_id);

		if ( !$banner )
			die(CJSON::encode(array('success'=>false)));

		if ( $type == 'swf' ) {
			$banner->swf_file_id = null;
		} elseif ( $type == 'img' ) {
			$banner->file_id = null;
		}

		if ( $banner->save(false) ) {
			$banner->updateRotation();
			die(CJSON::encode(array('success'=>true)));

		} else {
			die(CJSON::encode(array('success'=>false)));
		}
	}


	/**
	 * Обновление списка доступных разделов при смене типа баннера
	 */
	public function actionAjaxLoadSectionsForType()
	{
		$item_id = Yii::app()->request->getParam('item_id');
		$type = Yii::app()->request->getParam('type');

		$banner = BannerItem::model()->findByPk((int) $item_id);

		if ( !$banner )
			die(CJSON::encode(array('success'=>false)));

		$html = '<option value="">Выберите раздел</option>';

		foreach (BannerItemSection::getAvailableSections($type) as $k=>$s) {
			$html.="\n<option value='{$k}'>{$s}</option>";
		}

		die(CJSON::encode(array('success'=>true, 'html'=>$html)));
	}
}
