<?php

class ArchitectureController extends AdminController
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
				'roles' => array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_JOURNALIST,
				),
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

		$newIdea = new Architecture();
		$newIdea->author_id = Yii::app()->user->id;

		$newIdea->status = Interior::STATUS_MAKING;


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
		$model = new Architecture('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Architecture']))
			$model->setAttributes($_GET['Architecture']);

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
		$criteria->compare('t.status', '<>'.Interior::STATUS_DELETED);
		if ($date_from)
			$criteria->compare('t.create_time', '>='.(strtotime($date_from)));
		if ($date_to)
			$criteria->compare('t.create_time', '<='.(strtotime($date_to)+86400));


		$criteria->order = 't.create_time DESC';

		if (empty($model->status))
			$model->status = 0;


		$dataProvider = new CActiveDataProvider('Architecture', array(
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
		$model = Architecture::model()->findByPk((int) $id);

		if (!$model)
			throw new CHttpException(404);

		if ($model && $model->status == Architecture::STATUS_DELETED) {
			$this->render('deleted', array('model' => $model));
			Yii::app()->end();
		}

		// Получаем список Интерьеров этого же автора
		$interiors = Architecture::model()->findAllByAttributes(array(
			'author_id' => $model->author_id,
		), 'status <> '.Architecture::STATUS_DELETED.' AND status <> '.Interior::STATUS_MAKING);

		// Получаем список соавторов
		$coauthors = Coauthor::model()->findAllByAttributes(array('idea_id' => $model->id, 'idea_type_id' => Config::ARCHITECTURE));

		// Получаем тип строения
		$object = $model->getObject();
		$build = $model->getBuild();
		$objectTypeConst = IdeaHeap::getBuildTypeByName($object->option_value, Config::ARCHITECTURE);


		return $this->render('view', array(
			'model'		=> $model,
			'interiors'	=> $interiors,
			'coauthors'	=> $coauthors,
			'object'	=> $object->option_value,
			'buildingType'	=> $build->option_value,
			'objectTypeConst' => $objectTypeConst,
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

		$flagChangeAuthor = false;

		/** @var $model Architecture */
		$model = Architecture::model()->findByPk((int)$id);

		if (is_null($id) || ! $model)
			throw new CHttpException(404);


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
		if (($params = Yii::app()->getRequest()->getParam('Architecture')))
		{
			/** @var $imgComp ImageComponent */
			$imgComp = Yii::app()->img;

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
			if($params['author_id'] !== $model->id)
			{
				$flagChangeAuthor = true;
			}


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
			 *  Если из формы пришел параметр "save",
			 *  значит нужно сохранять данные из основной формы
			 * --------------------------------------------------
			 */
			// Если пришел главный логотип, сохраняем его
			$fileId = $model->loadImage('image', '');
			if ( $fileId!==null ) {
				$model->image_id = $fileId;
			}

			if ( ! Yii::app()->getRequest()->getParam('change_build_type')) {

				if ($objectTypeConst == Architecture::BUILD_TYPE_HOUSE) {
					$errorsSaveColors = IdeaAdditionalColor::saveAdditionalColor($model->id, Config::ARCHITECTURE);
					if (!empty($errorsSaveColors))
						$model->addError('color_id', 'Повторяющиеся дополнительные цвета');
				}

				// Обновление описаний для уже созданых ранее изображений
				if(isset($_POST['UploadedImage']['desc'])) {
					foreach($_POST['UploadedImage']['desc'] as $file_id => $file_desc) {
						$img = IdeaUploadedFile::model()->findByAttributes(array('idea_type_id'=>Config::ARCHITECTURE, 'uploaded_file_id'=>(int)$file_id));
						if(!$img)
							continue;

						$imgComp->setDesc($img->uploaded_file_id, CHtml::encode($file_desc));
					}
				}

				if (count($model->getPhotoList()) == 0)
					$model->addError('imagesCount', 'Необходимо добавить хотябы одно изображение');

				$coauthorErrors = Coauthor::SaveCoauthors($model->id, Config::ARCHITECTURE);
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

						if($flagChangeAuthor)
						{
							$item = PortfolioSort::model()->findByPk(array('item_id'=>$model->id,
												       'idea_type_id'=>Config::ARCHITECTURE,
												       'service_id'=>$model->service_id));
							if($item)
							{
								$item -> user_id = $model->author_id;
								$item -> position = 1;
								$item -> save(false);
							}
						}

						if ($model->status == Architecture::STATUS_REJECTED) {
							$model->increaseReject();
						}

						$this->redirect(array('list'));
					}

				}
				elseif (empty($errorsSaveColors) && empty($coauthorErrors) && $sourceError == false && $model->validate(null, false))
				{
					$model->save();

					if($flagChangeAuthor)
					{
						$item = PortfolioSort::model()->findByPk(array('item_id'=>$model->id,
											       'idea_type_id'=>Config::ARCHITECTURE,
											       'service_id'=>$model->service_id));
						if($item)
						{
							$item -> user_id = $model->author_id;
							$item -> position = 1;
							$item -> save(false);
						}
					}

					if ($model->status == Architecture::STATUS_REJECTED) {
						$model->increaseReject();
					}

					$this->redirect(array('list'));
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
		$cmd = Yii::app()->getDb()->createCommand();
		$cmd->select = 'id, name, image_id';
		$cmd->from(Interior::model()->tableName());
		$cmd->where = 'author_id = :authorID AND status <> :st1 AND status <> :st2';
		$cmd->params = array(
			':authorID' => $model->author_id,
			':st1'      => Interior::STATUS_DELETED,
			':st2'      => Interior::STATUS_MAKING
		);
		$interiors = $cmd->queryAll();


		// Получаем список соавторов
		$coauthors = Coauthor::model()->findAllByAttributes(array('idea_id' => $model->id, 'idea_type_id' => Config::ARCHITECTURE));

		// Получаем список строений для формы "Архитектура"
		$buildingTypes = IdeaHeap::model()->findAllByAttributes(array(
			'idea_type_id' => Config::ARCHITECTURE,
			'option_key' => 'building_type'
		), 'parent_id <> :parent_id', array(':parent_id' => 0));



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
				'interiors' 		=> $interiors,
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
				Architecture::model()->updateAll(
					array('status' => Architecture::STATUS_DELETED), 'id in ( ' . implode(',', Yii::app()->request->getParam('ids')) . ' )'
				);
			} elseif ($id) {
				$model = Architecture::model()->findByPk((int) $id);
				if ($model) {
					$model->status = Architecture::STATUS_DELETED;
					$model->save(false);
				}
			}

			Yii::app()->end();
		} else {
			$model = Architecture::model()->findByPk((int) $id);
			if ($model) {
				$model->status = Architecture::STATUS_DELETED;
				$model->save(false);

				$user = Yii::app()->user->model;
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array("/users/{$user->login}/architecture/service/{$model->service_id}"));
			} else {
				throw new CHttpException(403);
			}
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

		$architectureId = Yii::app()->request->getParam('architectureId');
		$architecture = Architecture::model()->findByPk($architectureId);
		if (is_null($architecture))
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

		if (is_null($architecture))
			throw new CHttpException(404);

		$coauthor->delete();

		echo CJSON::encode(array('success' => true));

		return;
	}

        /**
         * Автокомплит для тегов
         * @param $term
         */
        public function actionAcTags($term)
        {
                header("Content-type: application/json");
                $this->layout = false;

                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $term = CHtml::encode($term);

                $tags = Yii::app()->db->createCommand()->from('tag')
                        ->where('name like "'.$term.'%"')->queryAll();

                $data = array();

                foreach($tags as $tag) {
                        $json = array();
                        $json['value'] = $tag['id'];
                        $json['name'] = $tag['name'];
                        $data[] = $json;
                }

                echo json_encode($data);
                die();
        }

        /**
         * Привязка тега к проекту
         * @throws CHttpException
         */
        public function actionCreateTag()
        {
                $this->layout = false;

                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $id = Yii::app()->request->getParam('id');
                $name = Yii::app()->request->getParam('name');

                $model = Architecture::model()->findByPk((int)Yii::app()->request->getParam('model_id'));
                if(!$model)
                        throw new CHttpException(404);

                /**
                 * Если в id - текст, значит это новый тег, который нужно сохранить и привязать к проекту
                 * Иначе просто привязываем тег по id к проекту
                 */
                if(preg_match("/^\d+$/", $id) == 0) {
                        $saved = Tag::saveTagsFromString(CHtml::encode($name));
                        if(!$saved || !isset($saved[0]))
                                throw new CHttpException(500);

                        $id = $saved[0]; // устанавливаем id для только что сохраненного тега
                }

                /**
                 * Проверка на существование связки тега и проекта
                 */
                $exist = Yii::app()->db->createCommand()->from('architecture_tag')
                        ->where('tag_id=:tid and architecture_id=:aid', array(':tid'=>(int) $id, ':aid'=>$model->id))
                        ->limit(1)
                        ->queryAll();

                /**
                 * Привязка тега к проекту
                 */
                if(!$exist)
                        Yii::app()->db->createCommand()->insert('architecture_tag', array('tag_id'=>$id, 'architecture_id'=>$model->id));

                die(CJSON::encode(array('success'=>true)));
        }


        /**
         * Удаление связки тега и проекта
         * @throws CHttpException
         */
        public function actionDeleteTag()
        {
                $this->layout = false;

                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $id = Yii::app()->request->getParam('id');

                $model = Architecture::model()->findByPk((int)Yii::app()->request->getParam('model_id'));
                if(!$model)
                        throw new CHttpException(404);

                /**
                 * Проверка на существование связки тега и проекта
                 */
                $exist = Yii::app()->db->createCommand()->from('architecture_tag')
                        ->where('tag_id=:tid and architecture_id=:aid', array(':tid'=>(int) $id, ':aid'=>$model->id))
                        ->limit(1)
                        ->queryAll();

                /**
                 * Удаление связки тега и проекта
                 */
                if($exist)
                        Yii::app()->db->createCommand()->delete('architecture_tag', 'tag_id=:tid and architecture_id=:aid', array(':tid'=>(int) $id, ':aid'=>$model->id));

                die(CJSON::encode(array('success'=>true)));
        }

	/**
	 * По ID из UploadedFile возвращает путь до картинки
	 * @param $id
	 */
	public function actionSrcImageById($id)
	{
		$src = '#';

		$model = UploadedFile::model()->findByPk((int)$id);
		if ($model)
			$src = '/'.$model->getPreviewName(Architecture::$preview['crop_230']);

		die($src);
	}
}