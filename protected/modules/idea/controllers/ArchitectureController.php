<?php

class ArchitectureController extends FrontController
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
	public function actionCreate()
	{
		$this->layout = false;

		$newIdea = new Architecture();
		$newIdea->author_id = Yii::app()->user->id;

		$newIdea->status = Interior::STATUS_MAKING;


		if ( ! $newIdea->save()) {
			throw new CHttpException(403);
		}

		$this->redirect($newIdea->getUpdateLink());
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

		/** @var $model Architecture */
		if (is_null($id) || ! ($model = Architecture::model()->findByPk((int)$id)))
			throw new CHttpException(404);

		if (!$model->checkAccess()) {
			throw new CHttpException(403);
		}

		// Получаем список строений для формы "Архитектура"
		$buildingTypes = IdeaHeap::model()->findAllByAttributes(array(
			'idea_type_id' => Config::ARCHITECTURE,
			'option_key' => 'building_type'
		), 'parent_id <> :parent_id', array(':parent_id' => 0));


		// Название группы построек. Родительская категория, к которой относятся типы строений.
		$objectTypeConst = null;

		// Массив содержащий ошибки сохранения дополнтильных цветов
		$errorsSaveColors = array();

		// Массив, который при необходимости содержит список ошибок соавторов
		$coauthorErrors = array();

		// Массив дополнительных параметров, передаваемых в представление
		$paramsForView = array();


		/* --------------------------
		 *  Пришли данные из формы
		 * --------------------------
		 */
		if (($params = Yii::app()->getRequest()->getParam('Architecture')))
		{
			/* При смене типа строения нужно почистить дополнительные свойства,
			 * чтобы они не переносились со старых типов.
			 * При сохранении из формы все равно придут необходимые значения.
			 */
			Architecture::model()->updateByPk($model->id, array(
				'material_id' 	=> null,
				'style_id' 	=> null,
				'floor_id' 	=> null,
				'color_id'	=> null,
			));


			// До того как взять данные из формы, получаем тип сторения, определяем
			// нужный сценарий, уставнавливаем его, а затем делаем setAttributes
			$model->building_type_id = (int)$_POST['Architecture']['building_type_id'];

			// Группа того типа строения, который выбрал пользователь.
			// В зависимости от группы, будет определяется какие дополнительные
			// характеристики показывать пользователю в форме и какой сценарий активировать.
			$buildingType = IdeaHeap::model()->findByPk((int)$model->building_type_id);
			if ($buildingType) {
				$object = IdeaHeap::model()->findByPk((int)$buildingType->parent_id);
				if ($object) {
					$model->object_id = $object->id;
					$objectTypeConst = IdeaHeap::getBuildTypeByName($object->option_value, Config::ARCHITECTURE);
				} else {
					throw new CHttpException(500, 'Wrong building type');
				}
			} else {
				throw new CHttpException(500, 'Wrong building type');
			}

			/**
			 * Установка сценария
			 */
			$scenarioName = '';
			switch($objectTypeConst)
			{
				case Architecture::BUILD_TYPE_HOUSE:
					$scenarioName = 'edit_type_'.Architecture::BUILD_TYPE_HOUSE;
					break;
				case Architecture::BUILD_TYPE_OUTBUILDING:
					$scenarioName = 'edit_type_'.Architecture::BUILD_TYPE_OUTBUILDING;
					break;
				case Architecture::BUILD_TYPE_PUBLIC:
					$scenarioName = 'edit_type_'.Architecture::BUILD_TYPE_PUBLIC;
					break;
			}
			$model->setScenario($scenarioName);

			$model->setAttributes($params);


			/* --------------------------------------------------
			 *  Сохраняем данные из формы
			 * --------------------------------------------------
			 */
			if ( ! Yii::app()->getRequest()->getParam('change_build_type')) {

				/** @var $imgComp ImageComponent */
				$imgComp = Yii::app()->img;

				// Если пришел главный логотип, сохраняем его
				$fileId = $model->loadImage('image', '');
				if ( $fileId!==null ) {
					$model->image_id = $fileId;
				}

				if ($objectTypeConst == Architecture::BUILD_TYPE_HOUSE) {
					$errorsSaveColors = IdeaAdditionalColor::saveAdditionalColor($model->id, Config::ARCHITECTURE);
					if (!empty($errorsSaveColors))
						$model->addError('color_id', 'Повторяющиеся дополнительные цвета');
				}

				/**
				 * Для формы Simple
				 * Сохранение новых изображений с их описанием
				 */
				if(isset($_POST['UploadedFile']['new'])) {
					foreach($_POST['UploadedFile']['new'] as $file_key=>$desc){
						$fileId = $model->loadImage('file_'.$file_key, $desc['desc']);
						if ($fileId) {
							$ideaUF = new IdeaUploadedFile();
							$ideaUF->item_id = $model->id;
							$ideaUF->idea_type_id = Config::ARCHITECTURE;
							$ideaUF->uploaded_file_id = $fileId;
							$ideaUF->save();
						}
					}
				}

				// Обновление описаний для уже созданых ранее изображений
				if(isset($_POST['UploadedImage']['desc'])) {
					foreach($_POST['UploadedImage']['desc'] as $file_id => $file_desc) {
						/** @var $img IdeaUploadedFile */
						$img = IdeaUploadedFile::model()->findByAttributes(array('idea_type_id'=>Config::ARCHITECTURE, 'uploaded_file_id'=>(int)$file_id));
						if(!$img || $img->architecture->author_id != Yii::app()->user->id)
							continue;

						$imgComp->setDesc($img->uploaded_file_id, CHtml::encode($file_desc));
					}
				}

				if (count($model->getPhotoList()) == 0)
					$model->addError('imagesCount', 'Необходимо добавить хотябы одно изображение');

				$coauthorErrors = Coauthor::SaveCoauthors($model->id, Config::ARCHITECTURE);
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
					$model->status = Architecture::STATUS_MAKING;
					$model->save(false);
					$user = Yii::app()->user->model;
					$this->redirect("/users/{$user->login}/portfolio/draft");
				}
				elseif (empty($errorsSaveColors) && empty($coauthorErrors) && $model->validate(null, false))
				{
					$model->changeStatus();

					$model->save();

					if ($model->count_photos < 4) {
						$model->status = Architecture::STATUS_MODERATING;
						$model->save(false);
					}

					$updatedArch = Architecture::model()->findByPk($model->id);
					if ($updatedArch->count_photos > 2) {
						$updatedArch->status = Architecture::STATUS_ACCEPTED;
						$updatedArch->save(false);
					}

					$this->redirect('/users/'.$model->author->login.'/portfolio/service/'.$model->service_id);
				}
			}

		} else {
			// Если первый раз открыли проект на редактирование (не нажимали "сохранить")

			if (($buildingType = IdeaHeap::model()->findByPk((int)$model->building_type_id))) {
				if (($object = IdeaHeap::model()->findByPk((int)$buildingType->parent_id))) {
					$objectTypeConst = IdeaHeap::getBuildTypeByName($object->option_value, Config::ARCHITECTURE);
				}
			}
		}


		/* --------------------------
		 *  Дом, котедж, особняк
		 * --------------------------
		 */
		if ($objectTypeConst == Architecture::BUILD_TYPE_HOUSE) {
			// Получаем списки свойств для дополнительных характеристик
			$styles = IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $model->object_id, 'style');
			$materials = IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $model->object_id, 'material');
			$floors = IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $model->object_id, 'floor');
			$colors = IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $model->object_id, 'color');

			// Дополнительные цвета
			$addColors = IdeaAdditionalColor::model()->findAll(array(
				'condition' 	=> 'item_id= :id AND idea_type_id = :typeId',
				'params' 	=> array(':id'=> $model->id, ':typeId' => Config::ARCHITECTURE),
				'order' 	=> 'item_id ASC, position ASC'
			));
			if ( ! isset($addColors[0]))
				$addColors[0] = new IdeaAdditionalColor();
			if ( ! isset($addColors[1]))
				$addColors[1] = new IdeaAdditionalColor();

			$paramsForView = array(
				'styles' 		=> $styles,
				'materials' 		=> $materials,
				'floors' 		=> $floors,
				'colors' 		=> $colors,
				'addColors' 		=> $addColors,
				'errorsSaveColors'	=> $errorsSaveColors,
			);
		}


		/* -------------------------------
		 *  Хозяйственные постройки
		 * -------------------------------
		 */
		if ($objectTypeConst == Architecture::BUILD_TYPE_OUTBUILDING) {
			// Получаем списки свойств для дополнительных характеристик
			$materials = IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $model->object_id, 'material');
			$paramsForView = array(
				'materials' 		=> $materials,
			);
		}



		// Получаем список Интерьеров этого же автора
		$interiors = Interior::model()->findAllByAttributes(array(
			'author_id' => $model->author_id,
		), 'status <> '.Interior::STATUS_DELETED.' AND status <> '.Interior::STATUS_MAKING);

		// Получаем список соавторов
		$coauthors = Coauthor::model()->findAllByAttributes(array('idea_id' => $model->id, 'idea_type_id' => Config::ARCHITECTURE));

		/* -----------
		 *  РЕНДЕРИНГ
		 * -----------
		 */
		$view = Yii::app()->user->fileApiSupport
			? '//idea/architecture/create'
			: '//idea/architecture/simpleCreate';
		$this->render(
			$view,
			array(
				'user' 			=> Yii::app()->user->model,
				'model' 			=> $model,
				'buildingTypes' 		=> $buildingTypes,
				'objectTypeConst' 	=> $objectTypeConst,
				'interiors' 		=> $interiors,
				'coauthors'		=> $coauthors,
				'coauthorErrors'	=> $coauthorErrors,
			) + $paramsForView,
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
		if ( 	($model = Architecture::model()->findByPk($id))
		    	&&
		    	$model->status != Architecture::STATUS_DELETED
		    	&&
		    	Yii::app()->user->id == $model->author_id
		) {
			$user = Yii::app()->user->model;
			$model->status = Architecture::STATUS_DELETED;
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
	 * @param null $aid
	 */
	public function actionUpload($aid = null)
	{
		$aid = (int)$aid;
		$desc = isset($_POST['Architecture']['desc']) ? $_POST['Architecture']['desc'] : '';
		/** @var $architecture Architecture */
		$architecture = Architecture::model()->findByPk($aid);

		if ( $architecture===null )
			throw new CHttpException(404);

		$fileId = $architecture->loadImage('image', $desc);

		if ( $fileId===null ) {
			throw new CHttpException(500);
		}

		$ideaUF = new IdeaUploadedFile();
		$ideaUF->item_id = $aid;
		$ideaUF->idea_type_id = Config::ARCHITECTURE;
		$ideaUF->uploaded_file_id = $fileId;

		if ($ideaUF->save()) {
			die('success');
		} else {
			die('error');
		}
	}

	/**
	 * Метод для удаления фотографии из проекта по его ID
	 */
	public function actionDeleteImage($id = null)
	{
		if (Yii::app()->getRequest()->getIsAjaxRequest())
		{
			// Находим файл привязанный к проекту.
			$ideaUF = IdeaUploadedFile::model()->findByAttributes(array('idea_type_id' => Config::ARCHITECTURE, 'uploaded_file_id' => (int)$id));

			// Если привязанный найден и существует проект,...
			if ($ideaUF)
			{
				//...то проверяем владельца и удаляем запись о привязанном файле
				if ($ideaUF->architecture->author_id == Yii::app()->user->id) {
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

		$architectureId = Yii::app()->request->getParam('architectureId');
		$architecture = Architecture::model()->findByPk($architectureId);
		if (is_null($architecture) || $architecture->author_id != Yii::app()->user->id)
			throw new CHttpException(404);

		// coauthors limit
		$count = Coauthor::model()->countByAttributes(array('idea_type_id' => Config::ARCHITECTURE, 'idea_id' => $architecture->id));
		if ($count >= Config::MAX_INTCOAUTHORS) {
			echo CJSON::encode(array('error' => 'Слишком большое число соавторов'));
			return;
		}

		$this->layout = false;

		$result = $this->renderPartial('//idea/architecture/_addCoauthor', array(
			'architecture' => $architecture,
			'coauthor' => Coauthor::createRow($architecture->id, Config::ARCHITECTURE),
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

		$coauthor = Coauthor::model()->findByPk($coauthorId, 'idea_type_id=:typeId', array(':typeId' => Config::ARCHITECTURE));

		if (is_null($coauthor)) {
			echo CJSON::encode(array('success' => false));
			return;
		}

		$architecture = Architecture::model()->findByPk($coauthor->idea_id);

		if (is_null($architecture) || $architecture->author_id != Yii::app()->user->id)
			throw new CHttpException(404);

		$coauthor->delete();

		echo CJSON::encode(array('success' => true));

		return;
	}

	public function actionView($id = null)
	{
		if ( preg_match("/^\d+$/", $id) == 0 ) {
			throw new CHttpException(404);
		}

		// Отмечаем нужный пункт меню
		$this->menuActiveKey = 'interior';
		$this->menuIsActiveLink = true;

		/** @var $model Architecture */
		$model = Architecture::model()->findByPk( intval($id));
		if (is_null($model))
			throw new CHttpException(404);

		$author = User::model()->findByPk($model->author_id);

		$photoList = $model->getPhotoList();

		$object = $model->getObject();
		$colorsList = $model->getColorsList();
		$colors = IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $model->object_id, 'color');

		$viewCount = Architecture::appendView($model->id);


		// Источники
		Yii::import('application.modules.content.models.SourceMultiple');
		$sources = SourceMultiple::model()->findAllByAttributes(array('model' => get_class($model), 'model_id' => $model->id ));
		// Соавторы
		$coauthors = Coauthor::model()->findAllByAttributes(array('idea_id' => $model->id, 'idea_type_id' => Config::ARCHITECTURE));
		// Список доступных материалов
		$materials = IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $model->object_id, 'material');
		// Список доступных стилей
		$styles = IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $model->object_id, 'style');
		// Список доступных этажей
		$floors = IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $model->object_id, 'floor');

		/** Получение image для open graph */
		Yii::app()->openGraph->title = $model->name;
		$image = Yii::app()->getRequest()->getParam('image');
		if (!empty($image) && in_array($image, $photoList)) {
			Yii::app()->openGraph->description = Yii::app()->img->getDesc($image);
			Yii::app()->openGraph->image = Yii::app()->img->getPreview($image, 'crop_210');
		} else {
			Yii::app()->openGraph->description = $model->desc;
			Yii::app()->openGraph->image = Yii::app()->img->getPreview($model->image_id, 'crop_210');
		}

		//Если профиль просматривает не владелец
		//И автор идеи специалист
		//то наращиваем счетчик просмотров
		if (in_array($author->role, array( User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR ))) {

			if ($author->id !== Yii::app()->user->id)
			{
				StatProject::hit($model->id, get_class($model), $author->id, StatProject::TYPE_PROJECT_VIEW );
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
					StatProject::hit($model->id, get_class($model),$author->id, StatProject::TYPE_CLICK_PROJECT_IN_LIST);
				}
			}
		}

		$this->render('//idea/architecture/view', array(
			'model'		=> $model,
			'author' 	=> $author,
			'photoList'	=> $photoList,
			'object'	=> $object,
			'viewCount'	=> $viewCount,
			'sources'	=> $sources,
			'coauthors'	=> $coauthors,
			'materials'	=> $materials,
			'styles'	=> $styles,
			'floors'	=> $floors,
			'colorsList'	=> $colorsList,
			'colors'	=> $colors,
		));

	}

	/**
	 * Попап для просмотра картинок архитектуры
	 */
	public function actionPopup()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		$archId = intval( $request->getParam('idea_id') );
		/** @var $architecture Architecture */
		$architecture = Architecture::model()->findByPk($archId);
		if ( is_null($architecture) )
			throw new CHttpException(404);

		$photoList = $architecture->getPhotoList();


		$colorsList = $architecture->getColorsList();

		$object = $architecture->getObject();
		$colors = IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $architecture->object_id, 'color');
		// Список доступных стилей
		$styles = IdeaHeap::getListByOptionKey(Config::ARCHITECTURE, $architecture->object_id, 'style');

		$html = $this->renderPartial('//idea/architecture/_popup', array(
			'model' => $architecture,
			'object' => $object,
			'styles' => $styles,
			'colors' => $colors,
			'photoList' => $photoList,
			'colorsList' => $colorsList,
		), true);

		Yii::app()->end( json_encode( array('success'=>true, 'html'=>$html), JSON_NUMERIC_CHECK ) );
	}
}