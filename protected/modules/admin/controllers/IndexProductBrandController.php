<?php

/**
 * Класс реализует управление Логотипа в рамках Промоблока-Товаров главной
 * страницы сайта.
 */
class IndexProductBrandController extends AdminController
{

	/**
	 * Инициализация контроллера.
	 * Переопределяем с нем layout
	 */
	public function init()
	{
		$this->layout = 'webroot.themes.myhome.views.layouts.backend';

		parent::init();
	}


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
			array(
				'allow',
				'actions' => array(
					'index', 'view', 'create',
					'update', 'upload',
					'delete',
					'ajaxGetLogoVendor',
					'AjaxGetLogoStore',
					'AjaxStatusList',
					'AjaxStatusUpdate'
				),
				'roles'   => array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_SALEMANAGER,
					User::ROLE_JOURNALIST,
				),
			),
			array(
				'deny', // deny all users
				'users' => array('*'),
			),
		);
	}


	/**
	 * Displays a particular model.
	 *
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view', array(
			'model' => $this->loadModel($id)
		));
	}


	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model = new IndexProductBrand;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST[ 'IndexProductBrand' ])) {
			$model->attributes = $_POST[ 'IndexProductBrand' ];
			if ($model->save()) {

				$model->saveTabs($_POST['IndexProductBrand']['tabIds']);

				// Сохраняем фотки
				if ($this->savePhoto($model)) {
					$this->redirect(array('index'));
				}

			}
		}

		$this->render('create', array(
			'model' => $model,
		));
	}


	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		/** @var $model IndexProductBrand */
		$model = $this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST[ 'IndexProductBrand' ])) {

			$model->attributes = $_POST[ 'IndexProductBrand' ];
			if ($model->save()) {

				$model->saveTabs($_POST['IndexProductBrand']['tabIds']);

				// Сохраняем фотки
				if ($this->savePhoto($model)) {
					$this->redirect(array('index'));
				}
			}
		}

		$this->render('update', array(
			'model' => $model,
		));
	}


	/**
	 * Сохраняет полученную из формы фотографию.
	 * Рассматривает два случая.
	 * <table border="1" cellspacing="0" cellpadding="4">
	 * <tr><td>1</td> <td>Фотка загружена пользователем вручную</td></tr>
	 * <tr><td>2</td> <td>Фотка выбрана из сторонней модели (указывается
	 *                идентификатор из UploadedFile</td></tr>
	 * </table>
	 *
	 * @param $model IndexProductBrand
	 *
	 * @return boolean
	 */
	private function savePhoto(&$model)
	{
		// Флаг об успешности сохранения фотки.
		$saved = true;

		// Если рукми заливаем фотку
		if (CUploadedFile::getInstance($model, 'file')) {

			$model->setImageType('logo');
			$image = UploadedFile::loadImage($model, 'file', '', true);
			if ($image) {
				$model->image_id = $image->id;
				$model->save(false);
			} else {
				$saved = false;
			}

		} elseif (isset($_POST[ 'ac_image_id' ]) && $_POST[ 'ac_image_id' ] > 0) {

			// Если есть фотка из автокомплита


			/** @var $srcImage UploadedFile Исходное изобажение */
			$srcImage = UploadedFile::model()->findbyPk($_POST[ 'ac_image_id' ]);
			if ($srcImage) {

				$model->setImageType('logo');

				// Конечное изображение
				$dstImage = new UploadedFile();

				$dstImage->author_id = $model->getAuthorId();
				$dstImage->path = $model->getImagePath();
				$dstImage->name = $model->getImageName();
				$dstImage->ext = $srcImage->ext;
				$dstImage->size = $srcImage->size;
				$dstImage->type = $srcImage->type;
				$dstImage->desc = $srcImage->desc;

				if (copy($srcImage->getFullname(),
					UploadedFile::UPLOAD_PATH . '/' . $dstImage->path . '/' . $dstImage->name . '.' . $dstImage->ext)
					&& $dstImage->validate()
				) {
					$dstImage->save(false);

					$model->image_id = $dstImage->id;
					$model->save(false);

				} else {
					$model->addError('image_id', 'Невозможно сохранить фотографию');

					$saved = false;
				}
			}
		}

		return $saved;
	}


	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the
	 * 'admin' page.
	 *
	 * @param integer $id the ID of the model to be deleted
	 *
	 * @throws CHttpException 400 Ошибка в случае обращения без pos
	 * запроса.
	 */
	public function actionDelete($id)
	{
		if (Yii::app()->request->isPostRequest) {

			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			/* if AJAX request (triggered by deletion via admin
			grid view), we should not redirect the browser*/
			if (!isset($_GET[ 'ajax' ])) {
				$this->redirect(
					isset($_POST[ 'returnUrl' ])
						? $_POST[ 'returnUrl' ]
						: array('admin'));
			}
		} else
			throw new CHttpException(400, 'Invalid request.
			Please do not repeat this request again.');
	}


	/**
	 * Manages all models.
	 */
	public function actionIndex()
	{
		$model = new IndexProductBrand('search');
		$model->unsetAttributes(); // clear any default values
		if (isset($_GET[ 'IndexProductBrand' ])) {
			$model->attributes = $_GET[ 'IndexProductBrand' ];
		}

		$this->render('index', array(
			'model' => $model,
		));
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 *
	 * @param integer $id ID of the model to be loaded
	 *
	 * @return IndexProductBrand Модель логотипов промоблока
	 * @throws CHttpException 404 ошибка в случае отсутвсвия модели по его ID
	 */
	public function loadModel($id)
	{
		$model = IndexProductBrand::model()->findByPk($id);
		if ($model === null) {
			throw new CHttpException(404, 'The requested page does not exist.');
		}

		return $model;
	}


	/**
	 * Performs the AJAX validation.
	 *
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if (isset($_POST[ 'ajax' ]) && $_POST[ 'ajax' ] === 'index-product-brand-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}


	/**
	 * Ищет Производителя по $id и если есть логотип возвращает его.
	 *
	 * @param integer $id
	 *
	 * @throws CHttpException 404 ошибка при отсутствии производителя
	 */
	public function actionAjaxGetLogoVendor($id)
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(400, 'Только по Ajax');
		}

		Yii::import('application.modules.catalog.models.*');

		/** @var $vendor Vendor */
		$vendor = Vendor::model()->findByPk((int)$id);
		if (!$vendor) {
			throw new CHttpException(404, 'Производитель не найден');
		}

		// Ссылка на логотип Производителя
		$imgUrl = '';
		$imgId = 0;

		if ($vendor->uploadedFile) {
			$imgUrl = '/' . $vendor->uploadedFile->getPreviewName(
				array('90', '90', 'resize', 80)
			);
			$imgId = $vendor->uploadedFile->id;
		}

		exit(json_encode(array(
			'id'      => $vendor->id,
			'success' => true,
			'name'    => $vendor->name,
			'imgUrl'  => $imgUrl,
			'imgId'   => $imgId
		)));
	}


	/**
	 * Ищет Магазин по $id и если есть логотип возвращает его.
	 *
	 * @param $id
	 *
	 * @throws CHttpException 404 ошибка при отсутствии магазина
	 */
	public function actionAjaxGetLogoStore($id)
	{
		if (!Yii::app()->request->isAjaxRequest) {
			throw new CHttpException(400, 'Только по Ajax');
		}

		Yii::import('application.modules.catalog.models.*');

		/** @var $store Store */
		$store = Store::model()->findByPk((int)$id);
		if (!$store) {
			throw new CHttpException(404, 'Магазин не найден');
		}

		// Ссылка на логотип Магазина
		$imgUrl = '';
		$imgId = 0;

		if ($store->uploadedFile) {
			$imgUrl = '/' . $store->uploadedFile->getPreviewName(
				array('90', '90', 'resize', 80)
			);
			$imgId = $store->uploadedFile->id;
		}

		exit(json_encode(array(
			'id'      => $store->id,
			'success' => true,
			'name'    => $store->name,
			'imgUrl'  => $imgUrl,
			'imgId'   => $imgId
		)));
	}


	/**
	 * @brief Возвращает выпадающий список для смены статуса пользователя
	 * @param integer $uid user id
	 */
	public function actionAjaxStatusList()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('id') );
		if ( !$request->getIsAjaxRequest() || empty($id) )
			throw new CHttpException(404);

		/** @var $current IndexProductBrand */
		$current = IndexProductBrand::model()->findByPk($id);
		if ($current === null)
			throw new CHttpException(404);

		$statusList = '';
		foreach (IndexProductBrand::$statusName as $key => $status) {
			if ($current->status == $key) {
				$statusList.= CHtml::tag('li', array(
					'data-id'   => $current->id,
					'data-status_id' => $current->status,
					'class'     => 'current-status',
				), $status);
			} else {
				$statusList.= CHtml::tag('li', array('data-id'=>$current->id, 'data-status_id'=>$key), $status);
			}
		}
		die ( json_encode( array('success'=>true, 'html'=>$statusList), JSON_NUMERIC_CHECK ) );
	}

	/**
	 * @brief Обрабатывает запрос на смену статуса из контекстного меню
	 * @return JSON
	 */
	public function actionAjaxStatusUpdate()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$id = intval( $request->getParam('id') );
		$status = intval( $request->getParam('status') );
		if ( !$request->getIsAjaxRequest() || empty($id) || !isset(IndexProductBrand::$statusName[$status]))
			throw new CHttpException(404);

		/** @var $current IndexProductBrand */
		$current = IndexProductBrand::model()->findByPk($id);
		if ($current === null)
			throw new CHttpException(404);

		$current->status = $status;
		$current->save(false);

		die ( json_encode( array('success'=>true), JSON_NUMERIC_CHECK ) );
	}
}
