<?php

/**
 * Класс реализует управление Логотипа в рамках каталога товаров
 * страницы сайта.
 */
class TapestoreController extends AdminController
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
			array(
				'allow',
				//'actions' => array('*'),
				'roles'   => array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_MODERATOR,
					User::ROLE_SALEMANAGER,
				),
			),
			array(
				'deny', // deny all users
				'users' => array('*'),
			),
		);
	}

	public function actionCreate()
	{
		$model=new Tapestore();

		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$sCategory = $request->getParam('TapestoreCategory', array());

		if (isset($_POST['Tapestore'])) {
			$model->attributes = $_POST['Tapestore'];

			$dateTo = @strtotime( $request->getParam('date_to') );
			$dateFrom = @strtotime( $request->getParam('date_from') );

			$model->start_time = ($dateTo !== -1) ? $dateTo : 0;
			$model->end_time = ($dateFrom !== -1) ? $dateFrom : 0;
			$model->user_id = Yii::app()->getUser()->getId();

			$model->store_id = intval( $request->getParam('ac_store_id') );

			$imageId = intval( $request->getParam('ac_image_id') );
			if (!empty($imageId))
				$model->image_id = $imageId;

			$store = $model->getStore();
			if ( $model->validate() && $store!==null && ($file=$this->savePhoto($model) ) ) {
				$model->image_id = $file->id;
				$model->save(false);
				Tapestore::updateCategories($sCategory, $model->id);
				$model->position = $model->id;
				$model->save(false);

				$this->redirect(array('index'));
			}
		}

		$this->render('create', array(
			'model' => $model,
			'sCategory'=>$sCategory,
		));
	}

	/**
	 * Сохраняет полученную из формы фотографию.
	 * Рассматривает два случая.
	 * 1 Фотка загружена пользователем вручную
	 * 2 Фотка выбрана из сторонней модели (указывается идентификатор из UploadedFile
	 *
	 * @param $model Tapestore
	 * @return UploadedFile|false
	 */
	private function savePhoto(&$model)
	{
		// Если рукми заливаем фотку
		if (CUploadedFile::getInstance($model, 'file')) {
			$model->setImageType('logo');
			$image = UploadedFile::loadImage($model, 'file', '', true, null, false, null, false);
			if ( !$image )
				$model->addError('image_id', 'Изображение сохранить не удалось');

			return $image;
		}


		$imageId = intval( Yii::app()->getRequest()->getParam('ac_image_id') );

		if ( !empty($imageId) ) {
			if ($imageId == $model->image_id) { // фото не изменилось
				$file = UploadedFile::model()->findByPk($imageId);
				return $file;
			}
			// Если есть фотка из автокомплита

			/** @var $srcImage UploadedFile Исходное изобажение */
			$srcImage = UploadedFile::model()->findbyPk( $imageId );
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

				$model->flushImageType();

				if (
					$dstImage->validate() &&
					copy(
						$srcImage->getFullname(),
						UploadedFile::UPLOAD_PATH . '/' . $dstImage->path . '/' . $dstImage->name . '.' . $dstImage->ext
					)
				) {
					$dstImage->save(false);
					return $dstImage;
				}
				$model->addError('image_id', 'Невозможно сохранить фотографию');
			}
		}

		return false;
	}



	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		/** @var $model Tapestore */
		$model = $this->loadModel($id);

		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$sCategory = $request->getParam('TapestoreCategory', array());

		if (isset($_POST[ 'Tapestore' ])) {
			$model->attributes = $_POST[ 'Tapestore' ];

			$dateTo = @strtotime( $request->getParam('date_to') );
			$dateFrom = @strtotime( $request->getParam('date_from') );

			$model->start_time = ($dateTo !== -1) ? $dateTo : 0;
			$model->end_time = ($dateFrom !== -1) ? $dateFrom : 0;

			$model->store_id = intval( $request->getParam('ac_store_id') );

			$imageId = intval( $request->getParam('ac_image_id') );
			if (!empty($imageId))
				$model->image_id = $imageId;

			$store = $model->getStore();
			Tapestore::updateCategories($sCategory, $model->id);

			if ($model->validate() && $store!==null  ) {
				$file=$this->savePhoto($model);

				if ( $file instanceof UploadedFile ) {
				//	die();
					$model->image_id = $file->id;
				}
				$model->save(false);
				$this->redirect(array('index'));
			}
		}

		if ( !$request->getIsPostRequest() ) {
			$sCategory = Tapestore::getSelectedCategories($model->id);
		}

		$this->render('update', array(
			'model' => $model,
			'sCategory'=>$sCategory,
		));
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
		if (Yii::app()->getRequest()->getIsPostRequest()) {

			// we only allow deletion via POST request
			$model = $this->loadModel($id);

			$model->status = Tapestore::STATUS_DELETED;
			$model->save(false);

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
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model=new Tapestore('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Tapestore']))
			$model->attributes=$_GET['Tapestore'];

		$this->render('index',array(
			'model'=>$model,
		));
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 *
	 * @param integer $id ID of the model to be loaded
	 *
	 * @return Tapestore Модель логотипов промоблока
	 * @throws CHttpException 404 ошибка в случае отсутвсвия модели по его ID
	 */
	public function loadModel($id)
	{
		$model = Tapestore::model()->findByPk($id);
		if ($model === null) {
			throw new CHttpException(404, 'The requested page does not exist.');
		}

		return $model;
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
		if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
			throw new CHttpException(400, 'Только по Ajax');
		}

		Yii::import('application.modules.catalog2.models.*');

		/** @var $store Store */
		$store = Store::model()->findByPk((int)$id);
		if ( $store===null ) {
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
			//'name'    => $store->name,
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

		/** @var $current Tapestore */
		$current = Tapestore::model()->findByPk($id);
		if ($current === null)
			throw new CHttpException(404);

		$statusList = '';
		foreach (Tapestore::$statusNames as $key => $status) {
			if ($key == Tapestore::STATUS_DELETED)
				continue;

			if ($current->status == $key) {
				$statusList.= CHtml::tag('li', array(
					'data-id'   => $current->id,
					'data-status' => $current->status,
					'class'     => 'current-status',
				), $status);
			} else {
				$statusList.= CHtml::tag('li', array('data-id'=>$current->id, 'data-status'=>$key), $status);
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
		if ( !$request->getIsAjaxRequest() || empty($id) || !isset(Tapestore::$statusNames[$status]))
			throw new CHttpException(404);

		/** @var $current Tapestore */
		$current = Tapestore::model()->findByPk($id);
		if ($current === null)
			throw new CHttpException(404);

		$current->status = $status;
		$current->save(false);

		die ( json_encode( array('success'=>true), JSON_NUMERIC_CHECK ) );
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

		/** @var $current Tapestore */
		$current = Tapestore::model()->findByPk($id);

		if ( $current===null )
			die ( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'status IN (:st1, :st2) AND position<:position';
		$criteria->order = 'position DESC';
		$criteria->params = array(
			':position' => $current->position,
			':st1'=>Tapestore::STATUS_ENABLED,
			':st2'=>Tapestore::STATUS_DISABLED,
		);

		/** @var $next Tapestore */
		$next = Tapestore::model()->find($criteria);

		if ( $next===null )
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

		/** @var $current Tapestore */
		$current = Tapestore::model()->findByPk($id);

		if ( $current===null )
			die ( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$criteria = new CDbCriteria();
		$criteria->condition = 'status IN (:st1, :st2) AND position>:position';
		$criteria->order = 'position ASC';
		$criteria->params = array(
			':position' => $current->position,
			':st1'=>Tapestore::STATUS_ENABLED,
			':st2'=>Tapestore::STATUS_DISABLED,
		);

		/** @var $next Tapestore */
		$next = Tapestore::model()->find($criteria);
		if ( $next===null )
			die ( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$tmp = $current->position;
		$current->position = $next->position;
		$next->position = $tmp;
		$current->save(false);
		$next->save(false);
		die ( json_encode( array('success'=>true), JSON_NUMERIC_CHECK ) );
	}
}
