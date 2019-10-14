<?php

class CreateController extends FrontController
{

        public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {

                return array(
                    array('allow',
                        'roles' => array(
				User::ROLE_ADMIN,
				User::ROLE_MODERATOR,
				User::ROLE_SENIORMODERATOR,
				User::ROLE_POWERADMIN,
				User::ROLE_SPEC_FIS,
				User::ROLE_SPEC_JUR
			),
                    ),
		    array('deny', 'users' => array('*')),
                );
        }

	/**
	 * Создает экземпляр классса Interior и редиректит на редактирование записи
	 *
	 * @throws CHttpException 403 Если не удалось сохранить интерьер
	 */
        public function actionIndex()
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

		Yii::app()->getClientScript()->registerCssFile('/css/architecture.css');

		// Получаем список строений для формы "Архитектура"
		$buildingTypes = IdeaHeap::model()->findAllByAttributes(array(
			'option_key' => 'building_type'
		), 'parent_id <> :parent_id AND idea_type_id IN (:t1,:t2)', array(':parent_id' => 0, ':t1'=>Config::INTERIOR, ':t2'=>Config::INTERIOR_PUBLIC));


		/**
		 * Получаем тип помещения
		 */
		$buildingTypeId = Yii::app()->getRequest()->getParam('building_type');

		if ($buildingTypeId)
		{
			$buildingType = IdeaHeap::model()->findByPk((int)$buildingTypeId);
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

			/* --------------------------------
			 *  Жилое помещение
			 * --------------------------------
			 */
			if ($objectTypeConst == Interior::BUILD_TYPE_LIVE)
			{
				$newIdea = new Interior('create_step_1');
				$newIdea->idea_type_id = Config::INTERIOR;
				$newIdea->author_id = Yii::app()->user->id;

				$newIdea->status = Interior::STATUS_MAKING;

				$newIdea->object_id = $object->id;

				$newIdea->save(false);


				// После создания идеи, редиректим на форму редактирования.
				$this->redirect(
					$this->createUrl(
						$this->id . '/' . strtolower(Config::$ideaTypes[Config::INTERIOR]), array('id' => $newIdea->id)
					)
				);
			}
			elseif ($objectTypeConst == Interior::BUILD_TYPE_PUBLIC)
			{
				$this->redirect('/idea/interiorpublic/create/building_type_id/'.$buildingType->id);
			}
		}


		return $this->render(
			'//idea/create/interior', array(
			'user'		=> Yii::app()->user->model,
			'interior'	=> new Interior(),
			'buildingTypes' => $buildingTypes,
			'interiorImage' => null,
			'errors'	=> array(),
			'coauthorErrors'=> array(),
			'objects'	=> array(),
			'layouts'	=> array(),
			'coauthors'	=> array(),
			'rooms'		=> array(),
			'architectures' => array()
		), false, array( 'profileSpecialist', array('user'=>Yii::app()->getUser()->getModel() ) ));
        }

        /**
         * Обработка формы добавления Interior и InteriorContent.
         * Создает формы, сохраняет промежуточные значения по AJAX и сохраняет финальное заполнение формы
         * @author Roman Kuzakov
         * @param int Interior $id 
         * @return View 
         */
        public function actionInterior($id = NULL)
        {
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

		Yii::app()->getClientScript()->registerCssFile('/css/architecture.css');

	        if (!is_null($id)) {

			/** @var $interior Interior */
			$interior = Interior::model()->findByPk((int) $id, 'author_id = :uid',
				array(':uid' => Yii::app()->user->id)
			);
                        if ($interior) {

				/**
				 * Получаем тип помещения
				 */
				$buildingTypeId = Yii::app()->getRequest()->getParam('building_type');
				$objectTypeConst = null;
				if ($buildingTypeId)
				{
					$buildingType = IdeaHeap::model()->findByPk((int)$buildingTypeId);
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
				}


				// Проверям какой тип объекта. Если идет смена на "Общественный", то удаляем текущий
				// интерьер и редиректим на создание нового.
				if (isset($object) && $object->id == Interiorpublic::PROPERTY_ID_PUBLIC) {
					$interior->status = Interiorpublic::STATUS_DELETED;
					$interior->save(false);

					$this->redirect(array('/idea/create/index', 'building_type' => $buildingType->id));
				}


				// Если тип выбранного помещения относится к Общественнным, то удаляем текущий интерьер.
				// Создаем
				if ($objectTypeConst == Interior::BUILD_TYPE_PUBLIC)
				{
					$interior->status = Interior::STATUS_DELETED;
					$interior->save(false);

					$this->redirect(array('/idea/create/index', array('building_type' => $buildingType->id)));
				}




                                $errors = array();
                                $coauthorErrors = array();

				$arr = array_keys(Config::$ideaTypes, 'Interior');
                                $objects = IdeaHeap::model()->findAll('idea_type_id = :idea AND option_key = :key', array(':idea' => reset($arr),
                                    ':key' => 'object')
                                );
				unset($arr);
				
				// Ставим сценарий по-умолчанию
				$interior->setScenario('create_step_2');
				
                                /**
                                 * Обработка аякс-запроса на добавление помещения
                                 * Сохранение всей формы
                                 */
                                if (isset($_POST['Interior']) && Yii::app()->request->isAjaxRequest) {

                                        $interior->setScenario('create_step_1');

                                        $this->layout = false;

                                        $interior->attributes = $_POST['Interior'];

                                        if ($interior->validate()) {
                                                if ($interior->save(false)) {
                                                        $count = InteriorContent::model()->countByAttributes(array('interior_id' => $id));
                                                        // limit for interior content
                                                        if ($count >= Config::MAX_INTCONTENT_COUNT) {
                                                                $result = array('error' => 'Слишком много помещений');
                                                        } else {
                                                                // Save new data to exists coauthors
                                                                Coauthor::SaveCoauthors($interior->id, Config::INTERIOR);
                                                                // Сохранение ранее созданных форм InteriorContent
                                                                InteriorContent::SaveInteriorContent($interior->id);

                                                                // Отрисовка формы добавления помещения
                                                                $counter = (int) $_POST['content_counter'];


                                                                if (!empty($interior->object_id)) {
                                                                        $rooms = IdeaHeap::getRooms(Config::INTERIOR, $interior->object_id);
                                                                        $colors = IdeaHeap::getColors(Config::INTERIOR, $interior->object_id);
                                                                        $styles = IdeaHeap::getStyles(Config::INTERIOR, $interior->object_id);
                                                                } else {
                                                                        $rooms = array();
                                                                        $colors = array();
                                                                        $styles = array();
                                                                }
								
								// Задаем экземпляры для дополнительных цветов.
								$additional_colors[0] = $additional_colors[1] = new IdeaAdditionalColor();

								$content = InteriorContent::ajaxCreateRow($interior->id); // Создание новой пустой записи InteriorContent для заполнения
								$result = array(
									'success' => true,
									'id'      => $content->id,
									'data'    => $this->renderPartial('//idea/create/_interiorContentForm', array(
										'counter'           => $counter,
										'rooms'             => $rooms,
										'additional_colors' => $additional_colors,
										'colors'            => $colors,
										'styles'            => $styles,
										'content'           => $content,
										'uploadedFiles'     => array(),
										'mainImage'         => null,
									), true)
								);
                                                        }
                                                        echo CJSON::encode($result);
                                                        return;
                                                }
                                        }
                                }
				
                                /**
                                 * Обработка запроса на финальное сохранение
                                 */
                                if (isset($_POST['Interior']) && Yii::app()->request->isPostRequest) {

                                        $interior->setScenario('create_step_2');
                                        $interior->attributes = $_POST['Interior'];

					/**
					 * Для формы Simple
					 * Сохранение фоток планировок
					 */
					if(isset($_POST['UploadFile']['layout'])) {
						foreach($_POST['UploadFile']['layout'] as $file_key=>$desc){
							$interior->setImageType('layout');
							$img = UploadedFile::loadImage($interior, 'file_'.$file_key, $desc['desc']);
							if ($img) {
								$luf = new LayoutUploadedFile();
								$luf->item_id = $interior->id;
								$luf->idea_type_id = Config::INTERIOR;
								$luf->uploaded_file_id = $img->id;
								$luf->save();
							}
						}
					}

					/**
					 * Для формы Simple
					 * Сохранение фоток помещений
					 */
					if(isset($_POST['UploadFile']['content'])) {
						foreach($_POST['UploadFile']['content'] as $file_key=>$desc){
							// ПОлучаем помещение
							$intCont = InteriorContent::model()->findByPk((int)$_POST['UploadFile']['content_id'][$file_key]);
							if ($intCont && $intCont->author_id == Yii::app()->user->id) {
								$intCont->setImageType('interiorContent');
								$img = UploadedFile::loadImage($intCont, 'file_'.$file_key, $desc['desc']);
								if ($img) {
									$iuf = new IdeaUploadedFile();
									$iuf->item_id = $intCont->id;
									$iuf->idea_type_id = Config::INTERIOR;
									$iuf->uploaded_file_id = $img->id;
									$iuf->save();

									if ($intCont->image_id == null) {
										$intCont->image_id = $img->id;
										$intCont->save(false);
									}
								}
							}

						}
					}

					/**
					 * Для формы Simple
					 * Сохранение обложку интерьера
					 */
					$interior->setImageType('interior');
					$img = UploadedFile::loadImage($interior, 'cover');
					if ($img) {
						$interior->image_id = $img->id;
						Interior::model()->updateByPk($interior->id, array('image_id' => $img->id));
					}




					// Обновление описаний для уже созданых ранее изображений
					// Layouts
					if(isset($_POST['UploadImage']['desc'])) {
						foreach($_POST['UploadImage']['desc'] as $file_id => $file_desc) {
							$img = LayoutUploadedFile::model()->findByAttributes(array('idea_type_id'=>Config::INTERIOR, 'uploaded_file_id'=>(int)$file_id));
							if(!$img || $img->item->author_id != Yii::app()->user->id)
								continue;

							$uf = UploadedFile::model()->findByPk((int)$file_id);
							$uf->desc = CHtml::encode($file_desc);
							$uf->save();
						}
					}
					// Interior content image
					if(isset($_POST['UploadImage']['filedesc'])) {
						foreach($_POST['UploadImage']['filedesc'] as $file_id => $file_desc) {
							$img = IdeaUploadedFile::model()->findByAttributes(array('idea_type_id'=>Config::INTERIOR, 'uploaded_file_id'=>(int)$file_id));
							$uf = UploadedFile::model()->findByPk((int)$file_id);
							if(!$img || !$uf || !$uf->checkAccess())
								continue;

							$uf->desc = CHtml::encode($file_desc);
							$uf->save();
						}
					}


					$errors = InteriorContent::SaveInteriorContent($interior->id);
					$coauthorErrors = Coauthor::SaveCoauthors($interior->id, Config::INTERIOR);

					if ( ! isset($_POST['InteriorContent']))
						$interior->addError ('InteriorContentCount', 'Необходимо добавить хотябы одно помещение');

					if (!empty($errors['additional_colors']) || !empty($errors['interior_contents'])) {
						$interior->addErrors(array('interior_content' => array('Необходимо исправить ошибки в помещениях')));
					}

					if (!empty($coauthorErrors)) {
						$interior->addErrors(array('interior_content' => array('Необходимо исправить ошибки в соавторах')));
					}



					// Проверяем параметр на "продолжить позже"
					// Если установлен, то сохраняем идею как есть
					// и редиректим на список не опубликованных работ
					$later = Yii::app()->request->getParam('later');
					if ($later == 'yes')
					{
						$interior->status = Interior::STATUS_MAKING;
						$interior->save(false);
						$user = Yii::app()->user->model;
						$this->redirect("/users/{$user->login}/portfolio/draft");
					}
                                        elseif ($interior->validate(null, false) && !$errors && !$coauthorErrors)
					{
						$interior->changedStatus();
						if ($interior->status == Interior::STATUS_MAKING) // For new interiors only
							$interior->status = Interior::STATUS_MODERATING;
						$interior->save();

						$updatedInterior = Interior::model()->findByPk($interior->id);
						if ($updatedInterior->count_photos > 2) {
							$updatedInterior->status = Interior::STATUS_ACCEPTED;
							$updatedInterior->save(false);
						}

						$service_id = Interior::SERVICE_ID;
                                                $user_login = Yii::app()->user->model->login;

						$this->redirect(array("/users/{$user_login}/portfolio/service/{$service_id}"));
					}
                                }

                                /**
                                 * Отрисовка формы для продолжения заполнения Interior и InteriorContent
                                 */
                                $interiorImage = UploadedFile::model()->findByPk($interior->image_id);
                                $filesId = LayoutUploadedFile::model()->findAllByAttributes(array('item_id' => $interior->id, 'idea_type_id' => Config::INTERIOR));

                                $layouts = array();
                                foreach ($filesId as $item) {
                                        $layouts[] = UploadedFile::model()->findByPk($item->uploaded_file_id);
                                }
                                $coauthors = Coauthor::model()->findAllByAttributes(array('idea_id' => $interior->id, 'idea_type_id' => Config::INTERIOR));

				$rooms = IdeaHeap::getRooms(Config::INTERIOR, $interior->object_id);
				$rooms = array(''=>'Выберите помещение') + CHtml::listData($rooms, 'id', 'option_value');

				// Получаем список Интерьеров этого же автора
				$architectures = Architecture::model()->findAllByAttributes(array(
					'author_id' => $interior->author_id,
				), 'status <> '.Architecture::STATUS_DELETED.' AND status <> '.Architecture::STATUS_MAKING);


				// Получаем список строений для формы "Архитектура"
				$buildingTypes = IdeaHeap::model()->findAllByAttributes(array(
					'option_key' => 'building_type'
				), 'parent_id <> :parent_id AND idea_type_id IN (:t1,:t2)', array(':parent_id' => 0, ':t1'=>Config::INTERIOR, ':t2'=>Config::INTERIOR_PUBLIC));



				$view = Yii::app()->user->fileApiSupport
					? '//idea/create/interior'
					: '//idea/create/simpleForm/interior';

                                return $this->render($view, array(
						'user'		=> Yii::app()->user->model,
						'interior'	=> $interior,
						'interiorImage' => $interiorImage,
						'buildingTypes' => $buildingTypes,
						'errors'	=> $errors,
						'coauthorErrors'=> $coauthorErrors,
						'objects'	=> $objects,
						'layouts'	=> $layouts,
						'coauthors'	=> $coauthors,
						'rooms'		=> $rooms,
						'architectures' => $architectures
                                	), false,
					array( 'profileSpecialist', array('user'=>Yii::app()->getUser()->getModel() ) )
				);
                        }
                }


                throw new CHttpException(404);
        }

        /**
         * Вывод зависимых помещений, цветов и стилей, зависящих от выбранного объекта
         * Используется для обновления DropDownLists при добавлении помещений
         * @author Roman Kuzakov
         * @return JSON
         */
        public function actionDynamicdropdowns()
        {
                $data = array('rooms' => '', 'styles' => '', 'colors' => '');

		$rooms = IdeaHeap::getRooms(Config::INTERIOR, (int) $_POST['Interior']['object_id']);
                /*$rooms = IdeaHeap::model()->findAll('idea_type_id = :idea AND option_key = :key AND parent_id = :parent_id', array(':idea' => reset(array_keys(Config::$ideaTypes, 'Interior')),
                    ':key' => 'room', ':parent_id' => (int) $_POST['Interior']['object_id'])
                );*/

		$styles = IdeaHeap::getStyles(Config::INTERIOR, (int) $_POST['Interior']['object_id']);
                /*$styles = IdeaHeap::model()->findAll('idea_type_id = :idea AND option_key = :key AND parent_id = :parent_id', array(':idea' => reset(array_keys(Config::$ideaTypes, 'Interior')),
                    ':key' => 'style', ':parent_id' => (int) $_POST['Interior']['object_id'])
                );*/

		$colors = IdeaHeap::getColors(Config::INTERIOR, (int) $_POST['Interior']['object_id']);
                /*$colors = IdeaHeap::model()->findAll('idea_type_id = :idea AND option_key = :key AND parent_id = :parent_id', array(':idea' => reset(array_keys(Config::$ideaTypes, 'Interior')),
                    ':key' => 'color', ':parent_id' => (int) $_POST['Interior']['object_id'])
                );*/

                $rooms = array('' => 'Выберите помещение') + CHtml::listData($rooms, 'id', 'option_value');
                $styles = array('' => 'Выберите стиль') + CHtml::listData($styles, 'id', 'option_value');
                $colors = array('' => 'Выберите цвет') + CHtml::listData($colors, 'id', 'option_value');

                foreach ($rooms as $value => $name) {
                        $data['rooms'].=CHtml::tag('option', array('value' => $value), CHtml::encode($name), true);
                }

                foreach ($styles as $value => $name) {
                        $data['styles'].=CHtml::tag('option', array('value' => $value), CHtml::encode($name), true);
                }

                foreach ($colors as $value => $name) {
                        $data['colors'].=CHtml::tag('option', array('value' => $value), CHtml::encode($name), true);
                }

                echo CJSON::encode($data);
        }

        /**
         * Добавление дополнительного цвета к InteriorContent
         * @author Roman Kuzakov
         */
        public function actionAppend_color($id=NULL, $counter=NULL)
        {
                $this->layout = false;

                if (IdeaAdditionalColor::model()->count('idea_type_id = :type AND item_id = :item', array(':type' => reset(array_keys(Config::$ideaTypes, 'Interior')), ':item' => (int) $id)) > 2) {
                        return $this->renderText('Limit');
                }

                $models = InteriorContent::getModelsByInteriorContent($id);

                if ($models) {
			
			$colors = IdeaHeap::getColors(Config::INTERIOR, $models['interior']->object_id);
                        /*$colors = IdeaHeap::model()->findAll('idea_type_id = :idea AND option_key = :key', array(':idea' => reset(array_keys(Config::$ideaTypes, 'Interior')),
                            ':key' => 'color')
                        );*/

                        return $this->renderPartial('_additionalInteriorColorForm', array(
                                    'colors' => $colors,
                                    'content_id' => $models['interiorContent']->id,
                                    'counter' => (int) $counter,
                                    'color' => IdeaAdditionalColor::ajaxCreateRow($models['interiorContent']->id, (int) $counter),
                                ));
                }
                return $this->renderText('Error');
        }
	
	
	/**
         * Метод удаления идея (выставление статуса "Удален") для
	 * автора или супер админа.
         * @param integer $id ID идеи.
         */
        public function actionDelete($id)
        {
		Yii::import('application.modules.log.models.ModeratorLog');
		$model = Interior::model()->findByPk((int) $id);
		if ($model) {
			// Если нет доступа, ошибка
			if (Yii::app()->user->id != $model->author_id)
				throw new CHttpException(403);
			
			$model->status = Interior::STATUS_DELETED;
			$model->save(false);
			ModeratorLog::operationDelete($model);

                        $user = Yii::app()->user->model;
                        $service_id = Interior::SERVICE_ID;

			if (Yii::app()->request->isAjaxRequest)
				die(CJSON::encode(array('success' => true)));
			else
				$this->redirect("/users/{$user->login}/portfolio/service/{$service_id}");
		} else {
			if (Yii::app()->request->isAjaxRequest)
				die(CJSON::encode(array('success' => false)));
			else
				$this->redirect(Yii::app()->user->returnUrl);
		}
                
        }

        /**
         * Удаление созданного InteriorContent
         * @param int $id
         * @author Roman Kuzakov
         */
        public function actionDelete_sc($id)
        {
                $this->layout = false;
                $models = InteriorContent::getModelsByInteriorContent($id);
                if ($models) {

                        $models['interiorContent']->delete();

			$models['interior']->updateRoomsList();
			$models['interior']->countPhotos();
			

                        return $this->renderText('ok');
                }
                return $this->renderText('Error');
        }

        /**
         * Удаление созданного InteriorContentColor
         * @param int $id
         * @author Roman Kuzakov
         */
        public function actionDelete_scc($sc_id, $pos)
        {
                $this->layout = false;
                $models = InteriorContent::getModelsByInteriorContent($sc_id);
                if ($models) {
                        $color = IdeaAdditionalColor::model()->find('item_id = :sc AND idea_type_id = :idea AND position = :pos', array(':sc' => $models['interiorContent']->id, ':idea' => reset(array_keys(Config::$ideaTypes, 'Interior')), ':pos' => (int) $pos));
                        $color->delete();
                        return $this->renderText('ok');
                }
                return $this->renderText('Error');
        }


	/**
	 * Загружает фотографии приходящие из формы редактирования Интерьера
	 * по методу fileApi
	 * @param null $id
	 * @throws CHttpException
	 */
	public function actionImageupload($id = null)
	{
		$interior = Interior::model()->findByPk((int)$id);

		if (is_null($interior) || $interior->author_id != Yii::app()->user->id)
			die('error');


		// ПОлучаем все входные параметры
		$inputParams = Yii::app()->getRequest()->getParam('UploadImage');

		// Тип загружаемого файла
		$type = (isset($inputParams['type'])) ? $inputParams['type'] : '';

		// Описание изображения
		$desc = (isset($inputParams['desc'])) ? $inputParams['desc'] : '';


		/* -------------
		 *  ОБЛОЖКА
		 * -------------
		 */
		if ($type === 'cover')
		{
			$_FILES['Interior'] = $_FILES['UploadImage'];

			// Грузим фотку на сервер
			$interior->setImageType('interior');
			$image = UploadedFile::loadImage($interior, 'image', $desc);

			if ($image) {

				$interior->image_id = $image->id;

				$interior->changedStatus();
				$interior->save(false);

				die('success');
			}
		}
		/* -------------
		 *  ПЛАНИРОВКИ
		 * -------------
		 */
		elseif ($type === 'layout')
		{
			// Лимит загрузки изображений
			$count = LayoutUploadedFile::model()->countByAttributes(array('item_id' => $interior->id, 'idea_type_id' => Config::INTERIOR));
			if ($count >= Config::MAX_LAYOUT_IMAGE)
				throw new CHttpException(500, 'Limit quantity of images');


			$_FILES['Interior'] = $_FILES['UploadImage'];

			// Грузим фотку на сервер
			$interior->setImageType('layout');
			$image = UploadedFile::loadImage($interior, 'image', $desc);

			// Если фотка загрузилась, добавляем инфу о новодобавленной планировке
			if ($image) {
				$luf = new LayoutUploadedFile();
				$luf->item_id = $interior->id;
				$luf->idea_type_id = Config::INTERIOR;
				$luf->uploaded_file_id = $image->id;
				$luf->save();

				// смена статуса при изменении
				$interior->changedStatus();
				$interior->save(false);

				die('success');
			}
		}
		/* -------------
		 *  ПОМЕЩЕНИЯ
		 * -------------
		 */
		elseif ($type === 'content')
		{
			$content_id = (isset($inputParams['content_id'])) ? (int)$inputParams['content_id'] : 0;
			/** @var $interiorContent InteriorContent */
			$interiorContent = InteriorContent::model()->findByPk($content_id);

			if ($interiorContent && ($interiorContent->interior_id == $interior->id)) {
				// Лимит по загрузке фотографий
				$count = IdeaUploadedFile::model()->countByAttributes(array('item_id' => $interiorContent->id, 'idea_type_id' => Config::INTERIOR));
				if ($count >= Config::MAX_INTCONTENT_IMAGE)
					die('error: Limit image upload');


				$_FILES['InteriorContent'] = $_FILES['UploadImage'];

				// Грузим фотку на сервер
				$interiorContent->setImageType('interiorContent');
				$image = UploadedFile::loadImage($interiorContent, 'image', $desc);

				if ($image) {
					$iuf = new IdeaUploadedFile();
					$iuf->item_id = $interiorContent->id;
					$iuf->idea_type_id = Config::INTERIOR;
					$iuf->uploaded_file_id = $image->id;
					$iuf->save();


					// Пересчитываем кол-во фоток у интерьера
					$interior->countPhotos();

					// смена статуса при изменении
					$interior->changedStatus();
					$interior->save(false);

					// Назначаем обложку
					$interiorContent->setCover();

					/*// ПРоверяем установлена ли обложка помещения. Если нет, ставим ее.
					if (
						$interiorContent->image_id == null
						|| !UploadedFile::model()->exists('id = :id', array(':id' => $interiorContent->image_id))
					) {
						$interiorContent->image_id = $image->id;
						$interiorContent->save(false);
					}*/

					die('success');
				}

			}
		}

		die('error: something wrong');
	}

	/**
	 * Удаление фотографий
	 *
	 * @param null $id Идентификатор изображения в UploadedFile
	 * @param string $type Тип изображения
	 * @throws CHttpException
	 */
	public function actionImagedelete($id, $interior_id, $type)
	{
		if (Yii::app()->getRequest()->getIsAjaxRequest())
		{
			// Получаем объект для удаляемого файла
			$uf = UploadedFile::model()->findByPk((int)$id);
			// Получаем объект интерьера
			$interior = Interior::model()->findByPk((int)$interior_id);



			if ( ! in_array($type, array('cover', 'layout', 'content')))
				throw new CHttpException(500, 'Incorrect type of image');


			if ( ! $uf || ! $interior || ($uf->author_id != Yii::app()->user->id))
				throw new CHttpException(404);


			/* ---------------------
			 *  УДАЛЯЕМ ИЗОБРАЖЕНИЕ
			 * ---------------------
			 */
			$uf->delete();


			// Дополнтильные действия для разных типов изображений
			if ($type == 'cover')
			{
				$interior->image_id = null;
			}
			elseif ($type == 'layout')
			{
				LayoutUploadedFile::model()->deleteAllByAttributes(array('uploaded_file_id' => $uf->id), 'idea_type_id = :type', array(':type' => Config::INTERIOR));

				// ПОлучаем помещение
				$interiorContent = InteriorContent::model()->findByAttributes(array('image_id'=>$uf->id));
				if ($interiorContent) {
                                        $interiorContent->setScenario('finished');
                                        $interiorContent->image_id = null;
                                        $interiorContent->save(false);
				}
			}
			elseif ($type == 'content')
			{
				IdeaUploadedFile::model()->deleteAllByAttributes(
					array('uploaded_file_id' => $uf->id),
					'idea_type_id = :type',
					array(':type' => Config::INTERIOR)
				);


			}


			// Меняем статус у интерьера, сохраняем и пересчитываем фотки.
			$interior->changedStatus();
			$interior->save(false);

			$interior->countPhotos();

			if ($interior->count_photos < 4) {
				$interior->status = Interior::STATUS_MODERATING;
				$interior->save(false);
			}


			// ПОлучаем помещение
			/** @var $interiorContent InteriorContent */
			$interiorContent = InteriorContent::model()->findByAttributes(array('image_id'=>$uf->id));
			if ($interiorContent) {
				// Назначаем обложку
				$interiorContent->setCover();
			}


			die('success');

		}

		throw new CHttpException(403, 'Access deny');
	}

        /**
         * Get autocomplete for coauthor
         * @return JSON
         * @author Alexey Shvedov
         */
        public function actionGetcoauthor()
        {
                if (!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(404);

                $interiorId = Yii::app()->request->getParam('interiorId');
                $interior = Interior::model()->findByPk($interiorId);
                if (is_null($interior) || !$interior->checkAccess())
                        throw new CHttpException(404);
                // coauthors limit
                $count = Coauthor::model()->countByAttributes(array('idea_type_id' => Config::INTERIOR, 'idea_id' => $interior->id));
                if ($count >= Config::MAX_INTCOAUTHORS) {
                        echo CJSON::encode(array('error' => 'Слишком большое число соавторов'));
                        return;
                }

                $this->layout = false;

                $result = $this->renderPartial('_getCoAuthor', array(
                    	'interior' => $interior,
                    	'coauthor' => Coauthor::createRow($interior->id, Config::INTERIOR),
		), true);
                echo CJSON::encode(array('success' => true, 'data' => $result));
                return;
        }

        /**
         * Remove coauthor from Interior
         * @return JSON
         * @author Alexey Shvedov
         */
        public function actionRemovecoauthor()
        {
                if (!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(404);
                $this->layout = false;
                $coauthorId = Yii::app()->request->getParam('coauthorId');
                $coauthor = Coauthor::model()->findByPk($coauthorId, 'idea_type_id=:typeId', array(':typeId' => Config::INTERIOR));
                if (is_null($coauthor)) {
                        echo CJSON::encode(array('success' => false));
                        return;
                }

                $interior = Interior::model()->findByPk($coauthor->idea_id);
                if (is_null($interior) || !$interior->checkAccess())
                        throw new CHttpException(404);

                $coauthor->delete();
                echo CJSON::encode(array('success' => true));
                return;
        }
	
	/**
         * 
         */
        public function actionSaveDescriptions()
        {
                // Пока не доказали обратное, считаем что все нормально.
                $success = true;
                // Сообщение для ответа
                $message = "Ошибка:\n";

                // Get picture's id
                $id = Yii::app()->request->getParam('id');

                // If element exists we save the text
                $model = UploadedFile::model()->findByPk((int) $id);

                if (!is_null($model) && $model->checkAccess()) {


                        $type = Yii::app()->request->getParam('type');
                        switch ($type) {
                                case 'desc': {
					$purifier = new CHtmlPurifier();
					$purifier->options = array(
					    'HTML.AllowedElements' => array('a' => true),
					    'HTML.AllowedAttributes' => array('a.href' => true, 'a.title'=>true, 'a.target'=>true),
					    'URI.AllowedSchemes' => array('http' => true),
					);
					$model->desc = $purifier->purify(Yii::app()->request->getParam('text', ''));
				}
                                        break;
                                case 'keywords': {
					$purifier = new CHtmlPurifier();
					$purifier->options = array(
					    'HTML.AllowedElements' => array('a' => true),
					    'HTML.AllowedAttributes' => array('a.href' => true, 'a.title'=>true, 'a.target'=>true),
					    'URI.AllowedSchemes' => array('http' => true),
					);
					$model->keywords = $purifier->purify(Yii::app()->request->getParam('text', ''));
				}
                                        break;
                                default:
                                        $success = false;
                        }

                        if (!$model->save()) {
                                $success = false;

                                $arr = $model->getErrors();
                                if (isset($arr['desc']))
                                        $message = $arr['desc'];
                                elseif (isset($arr['keywords']))
                                        $message = $arr['keywords'];
                        }
                } else
                        $success = false;

		echo CJSON::encode(array('success' => $success, 'message' => $message));
                Yii::app()->end();
        }

}