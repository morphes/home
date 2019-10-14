<?php

class InteriorpublicController extends FrontController
{
	public function filters()
	{
		return array('accessControl');
	}

	public function accessRules()
	{

		return array(
			array('allow',
				'actions' => array('create', 'update', 'delete', 'upload', 'deleteImage', 'Addcoauthor', 'Deletecoauthor'),
				'roles' => array(
					User::ROLE_POWERADMIN,
					User::ROLE_SPEC_FIS,
					User::ROLE_SPEC_JUR
				),
				'users' => array('@')
			),
			array(
				'allow',
				'actions' => array('view', 'popup'),
				'users' => array('*')
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
		Yii::app()->getClientScript()->registerScriptFile('/js/architectureFilter.js');

		return parent::beforeAction($action);
	}


	/**
	 * Создает новый объект Архитекутра и редиректит на редактирование
	 * @throws CHttpException
	 */
	public function actionCreate($building_type_id)
	{
		$this->layout = false;

		$buildingType = IdeaHeap::model()->findByPk((int)$building_type_id);
		if ($buildingType) {
			$object = IdeaHeap::model()->findByPk((int)$buildingType->parent_id);
			if ($object) {
				$objectTypeConst = IdeaHeap::getBuildTypeByName($object->option_value, Config::INTERIOR);
			} else {
				throw new CHttpException(500, 'Wrong building type');
			}
		} else {
			throw new CHttpException(500, 'Wrong building type');
		}

		if ($objectTypeConst == Interior::BUILD_TYPE_PUBLIC)
		{
			$newIdea = new Interiorpublic();
			$newIdea->author_id = Yii::app()->user->id;

			$newIdea->status = Interiorpublic::STATUS_MAKING;
			$newIdea->object_id = $object->id;
			$newIdea->building_type_id = $buildingType->id;

			if ( ! $newIdea->save()) {
				throw new CHttpException(403);
			}

			$this->redirect($newIdea->getUpdateLink());
		}


		throw new CHttpException(404);
	}

	/**
	 * Редактирование идеи "Архитектура"
	 * @param null $id
	 * @throws CHttpException
	 */
	public function actionUpdate($id = null)
	{
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

		if (is_null($id) || ! ($model = Interiorpublic::model()->findByPk((int)$id)))
			throw new CHttpException(404);

		// Получаем список строений для формы "Архитектура"
		$buildingTypes = IdeaHeap::model()->findAllByAttributes(array(
			'option_key' => 'building_type'
		), 'parent_id <> :parent_id AND idea_type_id IN (:t1,:t2)', array(':parent_id' => 0, ':t1'=>Config::INTERIOR, ':t2'=>Config::INTERIOR_PUBLIC));


		// Название группы построек. Родительская категория, к которой относятся типы строений.
		$objectTypeConst = null;

		// Массив содержащий ошибки сохранения дополнтильных цветов
		$errorsSaveColors = array();

		// Массив, который при необходимости содержит список ошибок соавторов
		$coauthorErrors = array();



		/* --------------------------
		 *  Пришли данные из формы
		 * --------------------------
		 */
		if (($params = Yii::app()->getRequest()->getParam('Interiorpublic')))
		{
			/* При смене типа строения нужно почистить дополнительные свойства,
			 * чтобы они не переносились со старых типов.
			 * При сохранении из формы все равно придут необходимые значения.
			 */
			Interiorpublic::model()->updateByPk($model->id, array(
				'style_id' 	=> null,
				'color_id'	=> null,
			));


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


			// Проверям какой тип объекта. Если идет смена на "Жилой", то удаляем текущий
			// интерьер и редиректим на создание нового.
			if ($object->id == Interiorpublic::PROPERTY_ID_LIVE) {
				$model->status = Interiorpublic::STATUS_DELETED;
				$model->save(false);

				$this->redirect('/idea/create/index?building_type='.$buildingType->id);
			}


			$model->setScenario('edit');
			$model->setAttributes($params);


			/* --------------------------------------------------
			 *  Сохраняем данные из формы
			 * --------------------------------------------------
			 */
			if ( ! Yii::app()->getRequest()->getParam('change_build_type')) {

				$model->setImageType('interiorPublic');
				$image = UploadedFile::loadImage($model, 'image', '');

				if ($image && ! $model->image_id) {
					$model->image = $image;
					$model->image_id = $model->image->id;
				}


				$errorsSaveColors = IdeaAdditionalColor::saveAdditionalColor($model->id, Config::INTERIOR_PUBLIC);
				if (!empty($errorsSaveColors))
					$model->addError('color_id', 'Повторяющиеся дополнительные цвета');

				/**
				 * Для формы Simple
				 * Сохранение новых изображений с их описанием
				 */
				if(isset($_POST['UploadedFile']['new'])) {
					foreach($_POST['UploadedFile']['new'] as $file_key=>$desc){
						$model->setImageType('interiorPublic');
						$img = UploadedFile::loadImage($model, 'file_'.$file_key, $desc['desc']);
						if ($img) {
							$ideaUF = new IdeaUploadedFile();
							$ideaUF->item_id = $model->id;
							$ideaUF->idea_type_id = Config::INTERIOR_PUBLIC;
							$ideaUF->uploaded_file_id = $img->id;

							$ideaUF->save();
						}
					}
				}

				// Обновление описаний для уже созданых ранее изображений
				if(isset($_POST['UploadedImage']['desc'])) {
					foreach($_POST['UploadedImage']['desc'] as $file_id => $file_desc) {
						$img = IdeaUploadedFile::model()->findByAttributes(array('idea_type_id'=>Config::INTERIOR_PUBLIC, 'uploaded_file_id'=>(int)$file_id));
						if(!$img || $img->interiorPublic->author_id != Yii::app()->user->id)
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


				/* -----------------------------------
				 *  Основное СОХРАНЕНИЕ формы
				 * -----------------------------------
				 */
				// Проверяем параметр на "продолжить позже"
				// Если установлен, то сохраняем идею как есть
				// и редиректим на список не опубликованных работ
				$later = Yii::app()->request->getParam('later');
				if ($later == 'yes')
				{
					$model->status = Interiorpublic::STATUS_MAKING;
					$model->save(false);
					$user = Yii::app()->user->model;
					$this->redirect("/users/{$user->login}/portfolio/draft");
				}
				elseif (empty($errorsSaveColors) && empty($coauthorErrors) && $model->validate(null, false))
				{
					$model->changeStatus();

					$model->save();

					if ($model->count_photos < 3) {
						$model->status = Interiorpublic::STATUS_MODERATING;
						$model->save(false);
					}

					$updatedInterior = Interiorpublic::model()->findByPk($model->id);
					if ($updatedInterior->count_photos > 2) {
						$updatedInterior->status = Interiorpublic::STATUS_ACCEPTED;
						$updatedInterior->save(false);
					}

					$this->redirect('/users/'.$model->author->login.'/portfolio/service/'.$model->service_id);
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



		// Получаем список Интерьеров этого же автора
		$architectures = Architecture::model()->findAllByAttributes(array(
			'author_id' => $model->author_id,
		), 'status <> '.Architecture::STATUS_DELETED.' AND status <> '.Architecture::STATUS_MAKING);


		// Получаем список соавторов
		$coauthors = Coauthor::model()->findAllByAttributes(array('idea_id' => $model->id, 'idea_type_id' => Config::INTERIOR_PUBLIC));

		// ПОлучаем объект обложки по его ID
		if ( ! $model->image && $model->image_id)
			$model->image = UploadedFile::model()->findByPk($model->image_id);

		/* -----------
		 *  РЕНДЕРИНГ
		 * -----------
		 */
		$view = Yii::app()->user->fileApiSupport
			? '//idea/interiorpublic/create'
			: '//idea/interiorpublic/simpleCreate';
			
		$this->render(
			$view,
			array(
				'user' 			=> Yii::app()->user->model,
				'model' 			=> $model,
				'buildingTypes' 		=> $buildingTypes,
				'objectTypeConst' 	=> $objectTypeConst,
				'architectures' 	=> $architectures,
				'coauthors'		=> $coauthors,
				'coauthorErrors'	=> $coauthorErrors,
				'styles' 		=> $styles,
				'colors' 		=> $colors,
				'addColors' 		=> $addColors,
				'errorsSaveColors'	=> $errorsSaveColors,
			),
			false,
			array('profileSpecialist', array('user' => Yii::app()->user->model))
		);
	}

	/**
	 * Удаление идеи "Арихтекутра"
	 * @param $id
	 * @throws CHttpException
	 */
	public function actionDelete($id)
	{
		if ( 	($model = Interiorpublic::model()->findByPk($id))
		    	&&
		    	$model->status != Interiorpublic::STATUS_DELETED
		    	&&
		    	Yii::app()->user->id == $model->author_id
		) {
			$user = Yii::app()->user->model;
			$model->status = Interiorpublic::STATUS_DELETED;
			$model->save(false);

			if (Yii::app()->request->isAjaxRequest)
				die(CJSON::encode(array('success' => true)));
			else
				return $this->redirect("/users/{$user->login}/portfolio/service/{$model->service_id}");
		} else {
			throw new CHttpException(403);
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


		$interiorPublic = Interiorpublic::model()->findByPk((int)$id);

		if ($interiorPublic) {
			$interiorPublic->setImageType('interiorPublic');
			$image = UploadedFile::loadImage($interiorPublic, 'image', $desc);

			if ($image) {
				$ideaUF = new IdeaUploadedFile();
				$ideaUF->item_id = $id;
				$ideaUF->idea_type_id = Config::INTERIOR_PUBLIC;
				$ideaUF->uploaded_file_id = $image->id;

				if ($ideaUF->save())
					die('success');
			}
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
				//...то проверяем владельца и удаляем запись о привязанном файле
				if ($ideaUF->interiorPublic->author_id == Yii::app()->user->id) {
					$ideaUF->delete();
					die('success');
				}
				else
					throw new CHttpException(403);
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

		$intPublicId = Yii::app()->request->getParam('interiorpublicId');
		$interiorPublic = Interiorpublic::model()->findByPk($intPublicId);
		if (is_null($interiorPublic) || $interiorPublic->author_id != Yii::app()->user->id)
			throw new CHttpException(404);

		// coauthors limit
		$count = Coauthor::model()->countByAttributes(array('idea_type_id' => Config::INTERIOR_PUBLIC, 'idea_id' => $interiorPublic->id));
		if ($count >= Config::MAX_INTCOAUTHORS) {
			echo CJSON::encode(array('error' => 'Слишком большое число соавторов'));
			return;
		}

		$this->layout = false;

		$result = $this->renderPartial('//idea/interiorpublic/_addCoauthor', array(
			'interiorPublic' => $interiorPublic,
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

		if (is_null($interiorPublic) || $interiorPublic->author_id != Yii::app()->user->id)
			throw new CHttpException(404);

		$coauthor->delete();

		echo CJSON::encode(array('success' => true));

		return;
	}

	/**
	 * Просмотр общественного интерьера на фронте
	 * @param null $id
	 * @throws CHttpException
	 */
	public function actionView($id = null)
	{
		// Отмечаем нужный пункт меню
		$this->menuActiveKey = 'interior';
		$this->menuIsActiveLink = true;

		/** @var $intPublic Interiorpublic */
		$intPublic = Interiorpublic::model()->findByPk(intval($id));
		if ( is_null($intPublic) || !in_array($intPublic->status, array(Interiorpublic::STATUS_ACCEPTED, Interiorpublic::STATUS_CHANGED)) )
			throw new CHttpException(404);

		$author = User::model()->findByPk($intPublic->author_id);

		$styles = IdeaHeap::getStyles(Config::INTERIOR_PUBLIC, $intPublic->object_id);
		$colors = IdeaHeap::getColors(Config::INTERIOR_PUBLIC, $intPublic->object_id);
		$buildType = IdeaHeap::model()->findByPk($intPublic->building_type_id);

		Yii::import('application.modules.content.models.SourceMultiple');
		// Источники
		$sources = SourceMultiple::model()->findAllByAttributes(array('model' => get_class($intPublic), 'model_id' => $intPublic->id ));
		$photos = $intPublic->getPhotos();
		$colorsList = $intPublic->getColorsList();
		// Соавторы
		$coauthors = Coauthor::model()->findAllByAttributes(array('idea_id' => $intPublic->id, 'idea_type_id' => Config::INTERIOR_PUBLIC));

		$viewCount = Interiorpublic::appendView($intPublic->id);

		/** Получение image для open graph */
		Yii::app()->openGraph->title = $intPublic->name;
		$image = Yii::app()->getRequest()->getParam('image');
		if (!empty($image) && isset($photos[$image])) {
			Yii::app()->openGraph->description = $photos[$image]->desc;
			Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$photos[$image]->getPreviewName(Interiorpublic::$preview['crop_210']);
		} else {
			Yii::app()->openGraph->description = $intPublic->desc;
			Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$intPublic->getPreview(Interiorpublic::$preview['crop_210']);
		}

		//Если профиль просматривает не владелец
		//И автор идеи специалист
		//то наращиваем счетчик просмотров
		if (in_array($author->role, array( User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR ))) {

			if ($author->id !== Yii::app()->user->id)
			{
				StatProject::hit($intPublic->id, get_class($intPublic), $author->id, StatProject::TYPE_PROJECT_VIEW );
			}

			$urlReferrer = Yii::app()->request->getUrlReferrer();

			if($urlReferrer)
			{
				$urlParse = parse_url($urlReferrer);
			}

			$parseUrlHost = parse_url(Yii::app()->request->getHostInfo());

			/**
			 * Если переход со списка идей то наращиваем
			 */
			if(isset($urlParse) && $parseUrlHost['host'] == $urlParse['host'])
			{
				$urlPath = $urlParse['path'];
				$stringArray = explode("/", $urlPath);
				if(isset($stringArray[1]) && $stringArray[1] == 'idea')
				{
					StatProject::hit($intPublic->id, get_class($intPublic),$author->id, StatProject::TYPE_CLICK_PROJECT_IN_LIST);
				}
			}
		}

		$this->render('//idea/interiorpublic/view', array(
			'model' => $intPublic,
			'author' => $author,
			'styles' => $styles,
			'colors' => $colors,
			'buildType' => $buildType,
			'sources' => $sources,
			'photos' => $photos,
			'colorsList' => $colorsList,
			'viewCount' => $viewCount,
			'coauthors' => $coauthors,
		));
	}

	/**
	 * Попап для просмотра картинок интерьера
	 */
	public function actionPopup()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		$intPublicId = intval( $request->getParam('idea_id') );
		/** @var $intPublic Interiorpublic */
		$intPublic = Interiorpublic::model()->findByPk($intPublicId);
		if ( is_null($intPublic) )
			throw new CHttpException(404);

		$photos = $intPublic->getPhotos();
		$colorsList = $intPublic->getColorsList();
		$buildType = IdeaHeap::model()->findByPk($intPublic->building_type_id);

		$styles = IdeaHeap::getStyles(Config::INTERIOR_PUBLIC, $intPublic->object_id);
		$colors = IdeaHeap::getColors(Config::INTERIOR_PUBLIC, $intPublic->object_id);

		$html = $this->renderPartial('//idea/interiorpublic/_popup', array(
			'model' => $intPublic,
			'styles' => $styles,
			'colors' => $colors,
			'photos' => $photos,
			'colorsList' => $colorsList,
			'buildType' => $buildType,
		), true);

		Yii::app()->end( json_encode( array('success'=>true, 'html'=>$html), JSON_NUMERIC_CHECK ) );
	}

}