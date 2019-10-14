<?php

class InteriorController extends FrontController
{
	public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {

                return array(
                    array('allow',
                        'actions' => array('show', 'view', 'popup'),
                        'users' => array('*'),
                    ),
                    array('allow',
                        'actions' => array('delete'),
                        'roles' => array(
				User::ROLE_POWERADMIN,
				User::ROLE_SPEC_FIS,
				User::ROLE_SPEC_JUR
			),
                    ),
                    array('deny',
                        'users' => array('*'),
                    ),
                );
        }

	/**
	 * Show list solutions with pagination for current authorized user
	 * @param type $page
	 */
	public function actionShow($page = null)
	{
		$criteria = new CDbCriteria();

		$criteria->condition='author_id=:author_id';
		$criteria->params=array(':author_id'=>Yii::app()->user->id);

		$count = Interior::model()->count($criteria);
		$pages = new CPagination($count);

		// results per page
		$pages->pageSize = 5;
		$pages->applyLimit($criteria);
		$interiors = Interior::model()->findAll($criteria);


                $this->render('show', array(
                        'interiors' => $interiors,
                        'pages' => $pages,
			'user_name' => Yii::app()->user->name
                ));
	}

	/**
	 * Просмотр интерьера на фронте
	 * @param integer $interior_id
	 * @param integer $image
	 * @throws CHttpException
	 */
	public function actionView($interior_id = NULL)
	{
		// Отмечаем нужный пункт меню
		$this->menuActiveKey = 'interior';
		$this->menuIsActiveLink = true;

		/** @var $interior Interior */
		$interior = Interior::model()->findByPk( intval($interior_id) );
		if (is_null($interior) || !in_array($interior->status, array(Interior::STATUS_ACCEPTED, Interior::STATUS_CHANGED) ) )
			throw new CHttpException(404);


		$styles = IdeaHeap::getStyles(Config::INTERIOR, $interior->object_id);
		$colors = IdeaHeap::getColors(Config::INTERIOR, $interior->object_id);
		$rooms = IdeaHeap::getRooms(Config::INTERIOR, $interior->object_id);

		$contents = InteriorContent::model()->findAllByAttributes(array('interior_id'=>$interior->id), array('index'=>'id'));
		$author = User::model()->findByPk($interior->author_id);

		Yii::import('application.modules.content.models.SourceMultiple');
		$sources = SourceMultiple::model()->findAllByAttributes(array('model' => get_class($interior), 'model_id' => $interior->id ));
		$layouts = $interior->getLayouts();
		// Соавторы
		$coauthors = Coauthor::model()->findAllByAttributes(array('idea_id' => $interior->id, 'idea_type_id' => Config::INTERIOR));

		$viewCount = Interior::appendView($interior->id);
		/** Получение image для open graph */
		Yii::app()->openGraph->title = $interior->name;
		$image = Yii::app()->getRequest()->getParam('image');
		if (!is_null($image)) {
			$image = intval($image);
			/** Проверка доступа для чтения картинки */
			$criteria = new CDbCriteria();
			$criteria->join = 'INNER JOIN idea_uploaded_file as iuf ON iuf.uploaded_file_id=t.id AND iuf.idea_type_id=:type ';
			$criteria->join .= 'INNER JOIN interior_content as ic ON ic.id=iuf.item_id';
			$criteria->condition = 'ic.interior_id=:intId AND t.id=:id';
			$criteria->params = array(':type'=>Config::INTERIOR, ':intId'=>$interior->id, ':id'=>$image);
			/** @var $imageObj UploadedFile */
			$imageObj = UploadedFile::model()->find( $criteria );
			/** Проверка в layouts */
			if ( is_null($imageObj) && isset($layouts[$image])) {
				$imageObj = $layouts[$image];
			}

			if (!is_null($imageObj)) {
				Yii::app()->openGraph->description = $imageObj->desc;
				Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$imageObj->getPreviewName(InteriorContent::$preview['crop_210']);
			} else {
				Yii::app()->openGraph->description = $interior->desc;
				Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$interior->getPreview(Interior::$preview['crop_210']);
			}

		} else {
			Yii::app()->openGraph->description = $interior->desc;
			Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$interior->getPreview(Interior::$preview['crop_210']);
		}

		//Если профиль просматривает не владелец
		//И автор идеи специалист
		//то наращиваем счетчик просмотров
		if (in_array($author->role, array( User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR ))) {

			if ($author->id !== Yii::app()->user->id)
			{
				StatProject::hit($interior->id, get_class($interior), $author->id, StatProject::TYPE_PROJECT_VIEW );

				$urlReferrer = Yii::app()->request->getUrlReferrer();

				if($urlReferrer)
				{
					$urlParse = parse_url($urlReferrer);
				}

				$parseUrlHost = parse_url(Yii::app()->request->getHostInfo());

				/**
				 * Если переход со списков специалистов то наращиваем счетчик
				 */
				if(isset($urlParse) && $parseUrlHost['host'] == $urlParse['host'])
				{
					$urlPath = $urlParse['path'];
					$stringArray = explode("/", $urlPath);
					if(isset($stringArray[1]) && $stringArray[1] == 'idea')
					{
						StatProject::hit($interior->id, get_class($interior),$author->id, StatProject::TYPE_CLICK_PROJECT_IN_LIST);
					}
				}
			}
		}


		$this->render('//idea/interior/view', array(
			'interior' => $interior,
			'contents' => $contents,
			'styles' => $styles,
			'colors' => $colors,
			'rooms' => $rooms,
			'author' => $author,
			'sources' => $sources,
			'viewCount' => $viewCount,
			'layouts' => $layouts,
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

		$interiorId = intval( $request->getParam('idea_id') );
		$interior = Interior::model()->findByPk($interiorId);
		if ( is_null($interior) )
			throw new CHttpException(404);

		$styles = IdeaHeap::getStyles(Config::INTERIOR, $interior->object_id);
		$colors = IdeaHeap::getColors(Config::INTERIOR, $interior->object_id);
		$rooms = IdeaHeap::getRooms(Config::INTERIOR, $interior->object_id);

		$contents = InteriorContent::model()->findAllByAttributes(array('interior_id'=>$interior->id), array('index'=>'id'));
		$layouts = $interior->getLayouts();

		$html = $this->renderPartial('//idea/interior/_popup', array(
			'interior' => $interior,
			'contents' => $contents,
			'styles' => $styles,
			'colors' => $colors,
			'rooms' => $rooms,
			'layouts' => $layouts,
		), true);

		Yii::app()->end( json_encode( array('success'=>true, 'html'=>$html), JSON_NUMERIC_CHECK ) );
	}

	/**
	 * Удаляет указанный интерьер, который принадлежит текущего пользователю
	 * 
	 * @param integer $id Идентификатор Интерьреа
	 */
	public function actionDelete($id)
	{
		$model = Interior::model()->findByPk(
			(int)$id,
			'author_id = :author_id',
			array(':author_id' => Yii::app()->user->model->id)
			);
		
		if ($model) {
			$model->status = Interior::STATUS_DELETED;
			$model->save(false);
			
			$this->render('deleted');
		} else {
			throw new CHttpException(404);
		}
	}
}