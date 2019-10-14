<?php

class InteriorpublicController extends AdminController
{
	public $layout = 'webroot.themes.myhome.views.layouts.backend';

	public function filters()
	{
		return array('accessControl');
	}

	public function accessRules()
	{

		return array(
			array('allow',
				'actions' => array('list', 'view', 'create', 'update', 'delete', 'upload', 'deleteImage', 'Addcoauthor', 'Deletecoauthor'),
				'roles' => array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_JUNIORMODERATOR,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_FREELANCE_IDEA,
					User::ROLE_JOURNALIST,
				),
				'users' => array('@')
			),
			array('deny',
				'users' => array('*'),
			),
		);
	}

	public function beforeAction($action)
	{
		Yii::app()->getClientScript()->registerCssFile('/css/architecture.css');
		Yii::app()->getClientScript()->registerScriptFile('/js/architecture.js');

		return parent::beforeAction($action);
	}


	/**
	 * Создает новый объект Архитекутра и редиректит на редактирование
	 * @throws CHttpException
	 */
	public function actionCreate()
	{
		$this->layout = false;

		$newIdea = new Interiorpublic();
		$newIdea->author_id = Yii::app()->user->id;

		$newIdea->status = Interiorpublic::STATUS_MAKING;


		if ( ! $newIdea->save()) {
			throw new CHttpException(403);
		}

		$this->redirect(array('update', 'id' => $newIdea->id));
	}

	/**
	 * Отображает список проектов Архитектура
	 * @return string
	 */
	public function actionList()
	{
		$this->rightbar = null;

		$model = new Interiorpublic('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Interiorpublic']))
			$model->setAttributes($_GET['Interiorpublic']);

		if ($model->status == 0)
			$model->status = null;

		$date_from = Yii::app()->request->getParam('date_from');
		$date_to = Yii::app()->request->getParam('date_to');


		$criteria=new CDbCriteria;
		$criteria->compare('t.author_id', $model->author_id);
		$criteria->compare('t.name', $model->name, true);
		if ($model->id)
			$criteria->compare('t.id', explode(',', $model->id), true);

		$criteria->compare('t.status', $model->status);
		$criteria->compare('t.status', '<>'.Interiorpublic::STATUS_DELETED);
		if ($date_from)
			$criteria->compare('t.create_time', '>='.(strtotime($date_from)));
		if ($date_to)
			$criteria->compare('t.create_time', '<='.(strtotime($date_to)+86400));


		$criteria->order = 't.create_time DESC';

		if (empty($model->status))
			$model->status = 0;


		$dataProvider = new CActiveDataProvider('Interiorpublic', array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize'=>20,
			),
		));

		$this->render('list', array(
			'model'		=> $model,
			'dataProvider'	=> $dataProvider,
			'date_from'	=> $date_from,
			'date_to'	=> $date_to
		));
	}

	/**
	 * Детальная страница Архитекутры
	 * @param integer $interior_id
	 */
	public function actionView($id = NULL)
	{
		$model = Interiorpublic::model()->findByPk((int) $id);

		if (!$model)
			throw new CHttpException(404);

		if ($model && $model->status == Interiorpublic::STATUS_DELETED) {
			$this->render('deleted', array('model' => $model));
			Yii::app()->end();
		}

		// Получаем список Архитектур этого же автора
		$architectures = Architecture::model()->findAllByAttributes(array(
			'author_id' => $model->author_id,
		), 'status <> '.Architecture::STATUS_DELETED.' AND status <> '.Architecture::STATUS_MAKING);

		// Получаем список соавторов
		$coauthors = Coauthor::model()->findAllByAttributes(array('idea_id' => $model->id, 'idea_type_id' => Config::INTERIOR_PUBLIC));

		// ПОлучаем объект обложки по его ID
		if ( ! $model->image && $model->image_id)
			$model->image = UploadedFile::model()->findByPk($model->image_id);

		// Получаем тип строения
		$object = $model->getObject();
		$build = $model->getBuild();
		$objectTypeConst = IdeaHeap::getBuildTypeByName($object->option_value, Config::INTERIOR);


		return $this->render('view', array(
			'model'		=> $model,
			'architectures'	=> $architectures,
			'coauthors'	=> $coauthors,
			'object'	=> $object->option_value,
			'buildingType'	=> $build->option_value,
			'objectTypeConst' => $objectTypeConst,
			//'errors'	=> $errors,
			//'coauthors'	=> $coauthors,
			//'journal'=> InteriorJournal::getJournal($architecture),
		));
	}
	/**
	 * Редактирование идеи "Архитектура"
	 * @param null $id
	 * @throws CHttpException
	 */
	public function actionUpdate($id = null)
	{
		Yii::import('application.modules.content.models.SourceMultiple');

		$this->rightbar = null;

		/** @var $model Interiorpublic */
		$model = Interiorpublic::model()->findByPk((int)$id);

		if (is_null($id) || ! $model) {
			throw new CHttpException(404);
		}


		// Название группы построек. Родительская категория, к которой относятся типы строений.
		$objectTypeConst = null;

		// Массив содержащий ошибки сохранения дополнтильных цветов
		$errorsSaveColors = array();

		// Массив, который при необходимости содержит список ошибок соавторов
		$coauthorErrors = array();

		// Массив дополнительных параметров, передаваемых в представление
		$paramsForView = array();

		// Источники
		$sources = SourceMultiple::model()->findAllByAttributes(array('model' => get_class($model), 'model_id' => $model->id ));


		/* --------------------------
		 *  Пришли данные из формы
		 * --------------------------
		 */
		if (($params = Yii::app()->getRequest()->getParam('Interiorpublic')))
		{

			// До того как взять данные из формы, получаем тип сторения, определяем
			// нужный сценарий, уставнавливаем его, а затем делаем setAttributes
			$model->building_type_id = (int)$_POST['Interiorpublic']['building_type_id'];

			// Группа того типа строения, который выбрал пользователь.
			// В зависимости от группы, будет определяется какие дополнительные
			// характеристики показывать пользователю в форме и какой сценарий активировать.
			$buildingType = IdeaHeap::model()->findByPk((int)$model->building_type_id);
			if ($buildingType) {
				$object = IdeaHeap::model()->findByPk((int)$buildingType->parent_id);
				if ($object) {
					$model->object_id = $object->id;
					$objectTypeConst = IdeaHeap::getBuildTypeByName($object->option_value, Config::INTERIOR);
				} else {
					throw new CHttpException(500, 'Wrong building type');
				}
			} else {
				throw new CHttpException(500, 'Wrong building type');
			}

			/**
			 * Установка сценария
			 */
			$model->setScenario('edit');

			$model->setAttributes($params);


			/* --------------------------------------------------
			 *  Если из формы пришел параметр "save",
			 *  значит нужно сохранять данные из основной формы
			 * --------------------------------------------------
			 */
			if ( ! Yii::app()->getRequest()->getParam('change_build_type')) {

				$model->setImageType('interiorPublic');
				$image = UploadedFile::loadImage($model, 'image', '', true);

				if ($image && ! $model->image_id) {
					$model->image = $image;
					$model->image_id = $model->image->id;
				}

				$errorsSaveColors = IdeaAdditionalColor::saveAdditionalColor($model->id, Config::INTERIOR_PUBLIC);
				if (!empty($errorsSaveColors))
					$model->addError('color_id', 'Повторяющиеся дополнительные цвета');


				// Обновление описаний для уже созданых ранее изображений
				if(isset($_POST['UploadedImage']['desc'])) {
					foreach($_POST['UploadedImage']['desc'] as $file_id => $file_desc) {
						$img = IdeaUploadedFile::model()->findByAttributes(array('idea_type_id'=>Config::INTERIOR_PUBLIC, 'uploaded_file_id'=>(int)$file_id));
						/*if(!$img || $img->interiorPublic->author_id != Yii::app()->user->id)
							continue;*/

						if ( ! $img
							||
							( ! in_array(Yii::app()->user->role, array(User::ROLE_POWERADMIN, User::ROLE_MODERATOR, User::ROLE_JUNIORMODERATOR, User::ROLE_STORES_ADMIN))

							)
						)
							continue;

						$uf = UploadedFile::model()->findByPk((int)$file_id);
						$uf->desc = CHtml::encode($file_desc);
						$uf->save();
					}
				}

				if (count($model->images) == 0)
					$model->addError('imagesCount', 'Необходимо добавить хотябы одно изображение');

				$coauthorErrors = Coauthor::SaveCoauthors($model->id, Config::INTERIOR_PUBLIC);
				if ( ! empty($coauthorErrors))
					$model->addError('coauthors', 'Необходимо исправить ошибки в соавторах');

				// Проверяем и сохраняем "Источники"
				$sourceError = false;
				if (isset($_POST['SourceMultiple'])) {

					$valid = true;
					foreach ($sources as $source) {

						if (isset($_POST['SourceMultiple'][$source->id])) {
							$source->attributes = $_POST['SourceMultiple'][$source->id];
						}
						$valid = $source->save() && $valid;

					}
					if (!$valid)
						$sourceError = true;
				}

				/* -----------------------------------
				 *  Основное СОХРАНЕНИЕ формы
				 * -----------------------------------
				 */
				if (	   $model->status != Architecture::STATUS_ACCEPTED
					&& $model->status != Architecture::STATUS_VERIFIED
					&& $model->status != Architecture::STATUS_CHANGED )
				{
					if($model->validate(array('author_id')))
					{
						$model->save(false);

						if ($model->status == Interiorpublic::STATUS_REJECTED) {
							$model->increaseReject();
						}

						$this->redirect(array('list'));
					}
				}
				elseif (empty($errorsSaveColors) && empty($coauthorErrors) && $sourceError == false && $model->validate(null, false))
				{
					$model->save();

					$this->redirect(array('list'));
				}
			}

		} else {
			// Если первый раз открыли проект на редактирование (не нажимали "сохранить")

			if (($buildingType = IdeaHeap::model()->findByPk((int)$model->building_type_id))) {
				if (($object = IdeaHeap::model()->findByPk((int)$buildingType->parent_id))) {
					$objectTypeConst = IdeaHeap::getBuildTypeByName($object->option_value, Config::INTERIOR);
				}
			}
		}


		// Получаем списки свойств для дополнительных характеристик
		$styles = IdeaHeap::getListByOptionKey(Config::INTERIOR_PUBLIC, $model->object_id, 'style');
		$colors = IdeaHeap::getListByOptionKey(Config::INTERIOR_PUBLIC, $model->object_id, 'color');

		// Дополнительные цвета
		$addColors = IdeaAdditionalColor::model()->findAll(array(
			'condition' 	=> 'item_id= :id AND idea_type_id = :typeId',
			'params' 	=> array(':id'=> $model->id, ':typeId' => Config::INTERIOR_PUBLIC),
			'order' 	=> 'item_id ASC, position ASC'
		));
		if ( ! isset($addColors[0]))
			$addColors[0] = new IdeaAdditionalColor();
		if ( ! isset($addColors[1]))
			$addColors[1] = new IdeaAdditionalColor();

		$paramsForView = array(
			'styles' 		=> $styles,
			'colors' 		=> $colors,
			'addColors' 		=> $addColors,
			'errorsSaveColors'	=> $errorsSaveColors,
		);


		// Получаем список соавторов
		$coauthors = Coauthor::model()->findAllByAttributes(array('idea_id' => $model->id, 'idea_type_id' => Config::INTERIOR_PUBLIC));

		// Получаем объект обложки по его ID
		if ( ! $model->image && $model->image_id)
			$model->image = UploadedFile::model()->findByPk($model->image_id);

		// Получаем список строений для формы "Общественный интерьер"
		$buildingTypes = IdeaHeap::model()->findAllByAttributes(array(
			'idea_type_id' => Config::INTERIOR_PUBLIC,
			'option_key' => 'building_type',
		));

		/* -----------
		 *  РЕНДЕРИНГ
		 * -----------
		 */
		$this->render(
			'update',
			array(
				'user' 			=> Yii::app()->user->model,
				'model' 			=> $model,
				'buildingTypes' 		=> $buildingTypes,
				'objectTypeConst' 	=> $objectTypeConst,
				'coauthors'		=> $coauthors,
				'coauthorErrors'	=> $coauthorErrors,
				'sources'		=> $sources,
			) + $paramsForView
		);
	}

	/**
	 * Удаление идеи "Арихтекутра"
	 * @param $id
	 * @throws CHttpException
	 */
	public function actionDelete($id)
	{
		if (Yii::app()->request->isAjaxRequest) {

			if (isset($_POST['ids'])) {
				Interiorpublic::model()->updateAll(
					array('status' => Interiorpublic::STATUS_DELETED), 'id in ( ' . implode(',', Yii::app()->request->getParam('ids')) . ' )'
				);
			} elseif ($id) {
				$model = Interiorpublic::model()->findByPk((int) $id);
				if ($model) {
					$model->status = Interiorpublic::STATUS_DELETED;
					$model->save(false);
				}
			}

			Yii::app()->end();
		} else {
			$model = Interiorpublic::model()->findByPk((int) $id);
			if ($model) {
				$model->status = Interiorpublic::STATUS_DELETED;
				$model->save(false);

				$user = Yii::app()->user->model;
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array("/users/{$user->login}/interiorpublic/service/{$model->service_id}"));
			} else {
				throw new CHttpException(403);
			}
		}

	}

	/**
	 * Метод который вызвается для сохранения фотографии AJAX запросом.
	 * Сохраняет фотку в UploadedFile и делает в случае успеха запись в IdeaUploadedFile
	 * @param null $id
	 */
	public function actionUpload($id = null)
	{
		$id = (int)$id;
		$desc = isset($_POST['Interiorpublic']['desc']) ? $_POST['Interiorpublic']['desc'] : '';

		$intPub = Interiorpublic::model()->findByPk((int)$id);

		$intPub->setImageType('interiorPublic');
		$image = UploadedFile::loadImage($intPub, 'image', $desc, true);

		if ($image) {
			$ideaUF = new IdeaUploadedFile();
			$ideaUF->item_id = $id;
			$ideaUF->idea_type_id = Config::INTERIOR_PUBLIC;
			$ideaUF->uploaded_file_id = $image->id;

			if ($ideaUF->save())
				die('success');
		}

		die('error');
	}

	/**
	 * Метод для удаления фотографии из проекта по его ID
	 */
	public function actionDeleteImage($id = null)
	{
		if (Yii::app()->getRequest()->getIsAjaxRequest())
		{
			// Находим файл привязанный к проекту.
			$ideaUF = IdeaUploadedFile::model()->findByAttributes(array('idea_type_id' => Config::INTERIOR_PUBLIC, 'uploaded_file_id' => (int)$id));

			// Если привязанный найден и существует проект,...
			if ($ideaUF)
			{
				$ideaUF->delete();
				die('success');
			}
		} else {
			throw new CHttpException(403);
		}

		die('error');
	}


	/**
	 * Добавляет соавтора к проекту
	 * @return JSON
	 */
	public function actionAddcoauthor()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);

		$interiorpublicId = intval( Yii::app()->request->getParam('interiorpublicId') );
		$interiorPublic = Architecture::model()->findByPk($interiorpublicId);
		if (is_null($interiorPublic))
			throw new CHttpException(404);

		// coauthors limit
		$count = Coauthor::model()->countByAttributes(array('idea_type_id' => Config::INTERIOR_PUBLIC, 'idea_id' => $interiorPublic->id));
		if ($count >= Config::MAX_INTCOAUTHORS) {
			echo CJSON::encode(array('error' => 'Слишком большое число соавторов'));
			return;
		}

		$this->layout = false;

		$result = $this->renderPartial('//idea/architecture/_addCoauthor', array(
			'architecture' => $interiorPublic,
			'coauthor' => Coauthor::createRow($interiorPublic->id, Config::INTERIOR_PUBLIC),
		), true);
		echo CJSON::encode(array('success' => true, 'data' => $result));
		return;
	}

	/**
	 * Удаляет соавтора из проекта
	 * @return mixed
	 * @throws CHttpException
	 */
	public function actionDeletecoauthor()
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);

		$this->layout = false;
		$coauthorId = (int)Yii::app()->request->getParam('coauthorId');

		$coauthor = Coauthor::model()->findByPk($coauthorId, 'idea_type_id=:typeId', array(':typeId' => Config::INTERIOR_PUBLIC));


		if (is_null($coauthor)) {
			echo CJSON::encode(array('success' => false));
			return;
		}

		$interiorPublic = Interiorpublic::model()->findByPk($coauthor->idea_id);

		if ( is_null($interiorPublic) )
			throw new CHttpException(404);

		$coauthor->delete();


		echo CJSON::encode(array('success' => true));

		return;
	}
}