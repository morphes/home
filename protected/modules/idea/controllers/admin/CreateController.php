<?php

class CreateController extends AdminController
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
				User::ROLE_ADMIN,
				User::ROLE_JUNIORMODERATOR,
				User::ROLE_MODERATOR,
				User::ROLE_SENIORMODERATOR,
				User::ROLE_POWERADMIN,
				User::ROLE_FREELANCE_IDEA,
				User::ROLE_JOURNALIST,
			),
                    ),
		    array('allow',
			'actions' => array('interior'),
			'roles' => array(
				User::ROLE_FREELANCEEDITOR
			),
			),
                    array('deny',
                        'users' => array('*'),
                    ),
                );
        }

        /**
	 * Создает экземпляр классса Interior и редиректит на редактирование записи
	 *
	 * @throws CHttpException 403 Если не удалось сохранить интерьер
	 */
        public function actionIndex()
        {
		$interior = new Interior('create_step_1');
		$interior->idea_type_id = Config::INTERIOR;
		$interior->author_id = Yii::app()->user->id;

		$interior->status = Interior::STATUS_MAKING;

		if (!$interior->save()) {
			throw new CHttpException(403);
		}

		$role = Yii::app()->user->getRole();
		if ($role == User::ROLE_MODERATOR || $role == User::ROLE_JUNIORMODERATOR) {
			Yii::import('application.modules.log.models.ModeratorLog');
			ModeratorLog::operationCreate($interior);
		}

		$this->redirect(
			$this->createUrl(
				$this->id . '/' . strtolower(Config::$ideaTypes[Config::INTERIOR]), array('id' => $interior->id)
			)
		);
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
		Yii::import('application.modules.content.models.SourceMultiple');
		if ( in_array(Yii::app()->user->role, array(User::ROLE_FREELANCEEDITOR)) ) {
			return $this->_freelanceEditorInterior($id);
		}
		$flagChangeAuthor = false;
		
                if (!is_null($id)) {

			/** @var $interior Interior */
			$interior = Interior::model()->findByPk((int) $id);
                        if ($interior) {
                                $errors = array();
                                $coauthorErrors = array();
				$sources = SourceMultiple::model()->findAllByAttributes(array('model' => get_class($interior), 'model_id' => $interior->id ));
				// Идентификатор идеи Interior
				$arKeys = array_keys(Config::$ideaTypes, 'Interior');
                                $objects = IdeaHeap::model()->findAll('idea_type_id = :idea AND option_key = :key', array(':idea' => reset($arKeys),
                                    ':key' => 'object')
                                );

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
                                                                $rooms = array(''=>'Выберите помещение') + CHtml::listData($rooms, 'id', 'option_value');
                                                                $colors = array('' => 'Выберите цвет') + CHtml::listData($colors, 'id', 'option_value');
                                                                $styles = array('' => 'Выберите стиль') + CHtml::listData($styles, 'id', 'option_value');
                                                        } else {
                                                                $rooms = array();
                                                                $colors = array();
                                                                $styles = array();
                                                        }
                                                        
                                                        $content = InteriorContent::ajaxCreateRow($interior->id);
							
                                                        $html = $this->renderPartial('_interiorContentForm', array(
                                                                    'counter' => $counter,
                                                                    'rooms' => $rooms,
                                                                    'colors' => $colors,
                                                                    'styles' => $styles,
                                                                    'content' => $content, // Создание новой пустой записи InteriorContent для заполнения
                                                                    'uploadedFiles' => array(),
                                                                    'mainImage' => null,
                                                                ), true);
                                                        
                                                        $tab = '<li id="tab_'.$content->id.'"><a href="#interior_content_id_'.$content->id.'">'.@$rooms[$content->room_id].'</a></li>';
                                                        
                                                        die(CJSON::encode(array('tab'=>$tab, 'html'=>$html)));
                                                }
                                        }
                                }

                                /**
                                 * Обработка запроса на финальное сохранение
                                 */
                                if (isset($_POST['Interior']) && Yii::app()->request->isPostRequest) {

					//Флаг на то, что изменился автор
					if($interior->author_id !== $_POST['Interior']['author_id'] )
					{
						$flagChangeAuthor = true;
					}

                                        $interior->setScenario('create_step_2');
                                        $interior->attributes = $_POST['Interior'];

                                        $no_error = false;

					// Если статус любой кроме "принят в идеи" (STATUS_ACCEPTED)
					// и "Проверен Junior'ом" (STATUS_VERIFIED), мы ставим статус
					// для сохранения без лишних проверок
                                        if ($interior->status != Interior::STATUS_ACCEPTED && $interior->status != Interior::STATUS_VERIFIED && $interior->status != Interior::STATUS_CHANGED) {
						$interior->setScenario('create_step_1');
                                        }  
						
					$errors = InteriorContent::SaveInteriorContent($interior->id);
					$coauthorErrors = Coauthor::SaveCoauthors($interior->id, Config::INTERIOR);
					
					
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
					
					

					// если сохранение без проверок или без ошибок, то очишаем ошибки помещений и соавторов и сохраняем
					if ($interior->validate() && ( $interior->getScenario() == 'create_step_1' || (!$errors && !$coauthorErrors && !$sourceError)) ) {
						
						$errors = false;
						$coauthorErrors = false;

						if($interior->validate(array('author_id')))
						{
							$interior->save(false);

							if($flagChangeAuthor)
							{
								$item = PortfolioSort::model()->findByPk(array('item_id'=>$interior->id, 'idea_type_id'=>Config::INTERIOR,
													       'service_id'=>$interior->service_id));
								if($item)
								{
									$item -> user_id = $interior->author_id;
									$item -> position = 1;
									$item -> save(false);
								}
							}
						}

						$role = Yii::app()->user->getRole();
						// log
						if ( ($role == User::ROLE_MODERATOR || $role == User::ROLE_JUNIORMODERATOR )
							&& ($interior->status == Interior::STATUS_VERIFIED || $interior->status == Interior::STATUS_ACCEPTED)) {

							Yii::import('application.modules.log.models.ModeratorLog');
							ModeratorLog::operationModerate($interior);
						}
						$no_error = true;
					}


                                        // Если сохранились, отправляем сообщения и делаем редирект на страницу просмотра
                                        if ($no_error) {

						if ($interior->status == Interior::STATUS_REJECTED) {
							$interior->increaseReject();
						}

                                                if ( ! in_array($interior->author->role, array(User::ROLE_JUNIORMODERATOR, User::ROLE_MODERATOR, User::ROLE_ADMIN, User::ROLE_SENIORMODERATOR)) && $interior->status == Interior::STATUS_ACCEPTED){
                                                        Yii::app()->mail->create('publicOnIdea')
                                                                ->to($interior->author->email)
                                                                ->params(array(
                                                                        'username' 	=> $interior->author->name,
                                                                        'idea_name' 	=> $interior->name,
                                                                        'link_to_idea' 	=> 'idea/interior/'.$interior->id,
                                                                        'sign_A'	=> Yii::app()->mail->create('sign_A')->useView(false)->prepare()->getMessage(),
                                                                ))
                                                                ->send();
                                                }

                                                // личное сообщение пользователю
                                                if ($_POST['user_message'])
                                                        MsgBody::newMessage($interior->author_id, $_POST['user_message']);

                                                Yii::app()->request->redirect($this->createUrl('admin/interior/view', array('interior_id' => $interior->id)));
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

				return $this->render('interior', array(
					'interior'       => $interior,
					'interiorImage'  => $interiorImage,
					'errors'         => $errors,
					'coauthorErrors' => $coauthorErrors,
					'objects'        => $objects,
					'layouts'        => $layouts,
					'coauthors'      => $coauthors,
					'sources'        => $sources,
					'journal'        => InteriorJournal::getJournal($interior),
				));
                        }
                }


                throw new CHttpException(404);
        }

	/**
	 * Используется для сохранения описаний фоточек в интерьере
	 * ATTENSION!!!! нет проверки принадлежности определенной идее, по сути можно обновить любой uploadedFile
	 */
	public function actionImagedesc()
	{
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if ( !$request->getIsAjaxRequest() )
			throw new CHttpException(400);

		$id = intval( $request->getParam('id') );
		$html = $request->getParam('html');

		$file = UploadedFile::model()->findByPk($id);
		if (is_null($file))
			throw new CHttpException(404);

		$purifier = new CHtmlPurifier();
		$purifier->options = array('HTML.AllowedElements'=>array());

		$text = $purifier->purify($html);
		$file->desc = $text;
		if ($file->save()) {
			die ( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
		} else {
			die ( json_encode(array('error'=>true), JSON_NUMERIC_CHECK) );
		}
	}

	private function _freelanceEditorInterior($id)
	{
		$interior = Interior::model()->findByPk($id);
		if (is_null($interior))
			throw new CHttpException(404);
		
		$this->rightbar = null;
		
		$interiorContents = InteriorContent::model()->findAllByAttributes(array('interior_id' => $interior->id));
		
		if (isset($_POST['InteriorContent'])) {
			$postContent = Yii::app()->request->getParam('InteriorContent', array());
			foreach ($interiorContents as $content) {
				if ( empty($postContent[$content->id]['tag']) ) 
					continue;
				$content->setScenario('tagUpdate');
				$content->tag = $postContent[$content->id]['tag'];
				if ($content->validate()) {
					// TODO: append parser or in cron
					$content->save(false);
					$result = InteriorContentTag::updateTags($content->id, $content->tag);
					if (!$result) {
						$content->addError('tag', 'Теги некорректны');
					}
				} 
			}
		}
		
		return $this->render('freelanceInterior', array(
		    'interior' => $interior,
		    'interiorContents' => $interiorContents,
		));
                        
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
		$styles = IdeaHeap::getStyles(Config::INTERIOR, (int) $_POST['Interior']['object_id']);
		$colors = IdeaHeap::getColors(Config::INTERIOR, (int) $_POST['Interior']['object_id']);

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

                $model = InteriorContent::model()->findByPk((int) $id);

                if ($model) {
			$interior = Interior::model()->findByPk($model->interior_id);
			if (!is_null($interior)) {
				$colors = IdeaHeap::getColors(Config::INTERIOR, $interior->object_id);

				return $this->renderPartial('_additionalInteriorColorForm', array(
					    'colors' => $colors,
					    'content_id' => $model->id,
					    'counter' => (int) $counter,
					    'color' => IdeaAdditionalColor::ajaxCreateRow($model->id, (int) $counter),
					));
			}
                }
                return $this->renderText('Error');
        }

        /**
         * Deletes a particular model.
         * If deletion is successful, the browser will be redirected to the 'list' page.
         * @param integer $id the ID of the model to be deleted
         */
        public function actionDelete($id)
        {
		Yii::import('application.modules.log.models.ModeratorLog');
                if (Yii::app()->request->isAjaxRequest) {
			
                        if (isset($_POST['ids'])) {
				$models = Interior::model()->findAll('id in ( ' . implode(',', Yii::app()->request->getParam('ids')) . ' )');
                                Interior::model()->updateAll(
                                        array('status' => Interior::STATUS_DELETED), 'id in ( ' . implode(',', Yii::app()->request->getParam('ids')) . ' )'
                                );
				foreach ($models as $model) {
					ModeratorLog::operationDelete($model);
				}
				
                        } elseif ($id) {
                                $model = Interior::model()->findByPk((int) $id);
                                if ($model) {
                                        $model->status = Interior::STATUS_DELETED;
                                        $model->save(false);
					
					ModeratorLog::operationDelete($model);
                                }
                        }

                        Yii::app()->end();
                } else {
                        $model = Interior::model()->findByPk((int) $id);
                        if ($model) {
                                $model->status = Interior::STATUS_DELETED;
                                $model->save(false);
				ModeratorLog::operationDelete($model);
                        }
                }

                // if AJAX request (triggered by deletion via list grid view), we should not redirect the browser
                if (!isset($_GET['ajax']))
                        $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('/idea/admin/interior/list'));
        }

        /**
         * Удаление созданного InteriorContent
         * @param int $id
         * @author Roman Kuzakov
         */
        public function actionDelete_sc($id)
        {
                $this->layout = false;
                $model = InteriorContent::model()->findByPk((int) $id);
                if ($model) {

                        $model->delete();

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
                $model = InteriorContent::model()->findByPk((int) $sc_id);
                if ($model) {
			$arrKeys = array_keys(Config::$ideaTypes, 'Interior');
                        $color = IdeaAdditionalColor::model()->find('item_id = :sc AND idea_type_id = :idea AND position = :pos', array(':sc' => $model->id, ':idea' => reset($arrKeys), ':pos' => (int) $pos));
                        $color->delete();
                        return $this->renderText('ok');
                }
                return $this->renderText('Error');
        }

	public function actionUpload()
	{
		$this->layout = false;

		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		$type = $request->getParam('type');
		$id = intval( $request->getParam('id') );
		if ($type === 'interior') {
			/** @var $interior Interior */
			$interior = Interior::model()->findByPk($id);
			if (is_null($interior))
				throw new CHttpException(400);

			$interior->setImageType('interior');
			$image = UploadedFile::loadImage($interior, 'file', '', true);
			if ( $image ) {
				//$rel = new IdeaUploadedFile();
				//$rel->idea_type_id = C
				$interior->image_id = $image->id;
				$interior->save(false);

				$html = $this->renderPartial('_mainImageItem', array('file'=>$image), true);
				die( json_encode( array('success'=>true, 'html'=>$html), JSON_NUMERIC_CHECK ) );

			} else {
				die( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );
			}

		} elseif ($type === 'layout') {
			/** @var $interior Interior */
			$interior = Interior::model()->findByPk($id);
			if (is_null($interior))
				throw new CHttpException(400);

			$interior->setImageType('layout');
			$image = UploadedFile::loadImage($interior, 'file', '', true);

			if ( $image ) {
				$rel = new LayoutUploadedFile();
				$rel->idea_type_id = Config::INTERIOR;
				$rel->item_id = $interior->id;
				$rel->uploaded_file_id = $image->id;
				$rel->save(false);

				$html = $this->renderPartial('_imageItem', array('file'=>$image, 'type'=>'layout', 'parentId'=>$interior->id), true);
				die( json_encode( array('success'=>true, 'html'=>$html), JSON_NUMERIC_CHECK ) );

			} else {
				die( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );
			}
		} elseif ( $type === 'interiorContent' ) {
			/** @var $content InteriorContent */
			$content = InteriorContent::model()->findByPk($id);
			if (is_null($content))
				throw new CHttpException(400);

			$content->setImageType('interiorContent');
			$image = UploadedFile::loadImage($content, 'file', '', true);
			if ( $image ) {
				$rel = new IdeaUploadedFile();
				$rel->idea_type_id = Config::INTERIOR;
				$rel->item_id = $content->id;
				$rel->uploaded_file_id = $image->id;
				$rel->save(false);

				if (is_null($content->image_id)) {
					$content->image_id = $image->id;
					$content->save(false);
				}

				$html = $this->renderPartial('_imageItem', array(
					'file'     => $image,
					'type'     => 'interiorContent',
					'parentId' => $content->id
				), true);
				die( json_encode( array('success'=>true, 'html'=>$html), JSON_NUMERIC_CHECK ) );

			} else {
				die( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );
			}
		}

		throw new CHttpException(400, 'Invalid image type');
	}

        /**
         * Remove image by id
         * @return JSON
         * @author Alexey Shvedov
         */
        public function actionRemoveimage()
        {
		/** @var $request CHttpRequest */
		$request = Yii::app()->getRequest();
		if (!$request->getIsAjaxRequest())
			throw new CHttpException(400);

		$id = intval( $request->getParam('id') );
		$type = $request->getParam('type');
		$parentId = intval( $request->getParam('parentId') );

		if ($type === 'layout') {
			$cnt = LayoutUploadedFile::model()->deleteByPk(array('item_id'=>$parentId, 'idea_type_id'=>Config::INTERIOR, 'uploaded_file_id'=>$id));
		} elseif ($type === 'interiorContent') {

			$cnt = IdeaUploadedFile::model()->deleteByPk(array('idea_type_id' => Config::INTERIOR, 'uploaded_file_id' => $id, 'item_id'=>$parentId));
			$interiorContent = InteriorContent::model ()->findByPk ($parentId);
			if ($cnt > 0 && ( is_null($interiorContent->image_id) || $interiorContent->image_id == $id ) ) {
				// after delete
				$link = IdeaUploadedFile::model()->findByAttributes(array('idea_type_id' => Config::INTERIOR, 'item_id' => $interiorContent->id), array('order' => 'uploaded_file_id'));
				if (is_null($link))
					$interiorContent->image_id = null;
				else
					$interiorContent->image_id = $link->uploaded_file_id;
				$interiorContent->save();
			}
		} else
			throw new CHttpException(400, 'Invalid image type');

		if ($cnt > 0) {
			die( json_encode(array('success' => true, 'message' => 'Файл удален'), JSON_NUMERIC_CHECK) );
		} else {
			die( json_encode(array('error' => 'Файл не существует'), JSON_NUMERIC_CHECK) );
		}
        }

        /**
         * Get autocomplete for coauthor without checkAccess
         * @return JSON
         * @author Alexey Shvedov
         */
        public function actionGetcoauthor()
        {
                if (!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(404);

                $interiorId = Yii::app()->request->getParam('interiorId');
                $interior = Interior::model()->findByPk($interiorId);
                if (is_null($interior))
                        throw new CHttpException(404);

                $this->layout = false;

                $result = $this->renderPartial('_getCoAuthorItem', array(
                    'interior' => $interior,
                    'coauthor' => Coauthor::createRow($interior->id, Config::INTERIOR),
                        ), true);
                echo CJSON::encode(array('success' => true, 'data' => $result));
                return;
        }

        /**
         * Remove coauthor from Interior without checkAccess
         * @return JSON
         * @author Alexey Shvedov
         */
        public function actionRemovecoauthor()
        {
                if (!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(404);
                $this->layout = false;
                $coauthorId = Yii::app()->request->getParam('coauthorId');

                $rows = Coauthor::model()->deleteByPk($coauthorId, 'idea_type_id=:typeId', array(':typeId' => Config::INTERIOR));
                echo CJSON::encode(array('success' => (bool) $rows));

                return;
        }
        
        
        public function actionAddjournalmessage()
        {
                $interior = Interior::model()->findByPk($_POST['journal-interior-id']);
                
                if(!$interior)
                        die(CJSON::encode(array('key'=>'error', 'val'=>'Ошибка 404')));
                
                if(!isset($_POST['journal-message']) || empty($_POST['journal-message']))
                        die(CJSON::encode(array('key'=>'error', 'val'=>'Необходимо указать сообщение')));
                
                $msg = InteriorJournal::setComment($interior, $_POST['journal-message']);
                
                die(CJSON::encode(array('key'=>'ok', 'val'=>'msg_'.$msg->id)));
        }
        
        public function actionGetjournalmessages($interior_id)
        {
                $interior = Interior::model()->findByPk($interior_id);
                
                if(!$interior)
                        throw new CHttpException(404);
                
                $journal = InteriorJournal::getJournal($interior);
                
                return $this->renderPartial('_journal', array('journal'=>$journal, 'interior'=>$interior));
        }


	/**
	 * Перемещает указанную фотографию $image_id в указанное помещение
	 * $interior_content_id
	 */
	public function actionAjaxMoveImage()
	{
		$success = false;
		$message = 'Неизвестная ошибка';

		// Идентификатор перемещаемой фотографии
		$image_id = Yii::app()->request->getParam('imageId');
		// Идентификатор помещения-цели.
		$content_id = Yii::app()->request->getParam('contentId');

		$iuf = IdeaUploadedFile::model()->findByAttributes(array(
			'idea_type_id'     => Config::INTERIOR,
			'uploaded_file_id' => (int)$image_id
		));

		if ($iuf) {
			$ic = InteriorContent::model()->findByPk((int)$content_id);
			if ($ic) {
				$iuf->item_id = $ic->id;
				$iuf->save(false);

				$success = true;

			} else {
				$success = false;
				$message = 'Указанную фотографию #' . $iuf->id
					.' невозможно прикрепить к не существующем помещению #'.(int)$content_id;
			}
		} else {
			$success = false;
			$message = 'Указанная фотография #' . (int)$image_id . ' не существует';
		}

		exit(json_encode(array(
			'success' => $success,
			'message' => $message
		)));
	}

}