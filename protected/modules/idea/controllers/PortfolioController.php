<?php

class PortfolioController extends FrontController
{

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
					User::ROLE_SPEC_FIS,
					User::ROLE_SPEC_JUR,
					User::ROLE_POWERADMIN
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
	public function actionCreate($id = null, $service = null)
	{
		$this->layout = '//layouts/grid_main';
		$this->bodyClass = array('profile');

		$user = Cache::getInstance()->user;
		
                if( ( is_null($id) && is_null($service)) || !($user instanceof User) )
                        throw new CHttpException(404);

                
                 
                /**
                 * Если передан идентификатор проекта, то производится его загрузка
                 * Если идентификатор пустой, то создается новый проект и выполняется редирект
                 * клиента на страницу его редактирования
                 */
                if($id) {
                        $model=$this->loadModel($id);
                        if($model->author_id != Yii::app()->user->id || $model->status == Portfolio::STATUS_DELETED)
                                throw new CHttpException(404);
                        
                } elseif(!is_null($service)) {
                        $serv = Service::model()->findByPk((int)$service);

                        if(!$serv)
                                throw new CHttpException(404);

                        // Если переданная услуга - интерьер и указан его id, то редиректим на редактирование интерьеров
                        if($serv->type == Config::INTERIOR && $id)
                                return $this->redirect(array('/idea/create/interior', 'id'=>(int)$id));
                        // Если переданная услуга - интерьер и его id не указан, то редиректим на создание интерьеров
                        if($serv->type == Config::INTERIOR && is_null($id))
                                return $this->redirect(array('/idea/create/index'));

			// Если переданная услуга Арихтекрутра и указан его id, то редиректим на редактирование
			if($serv->type == Config::ARCHITECTURE && $id)
				return $this->redirect(array('/idea/architecture/update/', 'id'=>(int)$id));
			// Если переданная услгуа Архитектура и его id не указан, то редиректим на создание
			if($serv->type == Config::ARCHITECTURE && is_null($id))
				return $this->redirect(array('/idea/architecture/create'));

                        // Если тип услуги НЕ ИНТЕРЬЕР, то создаем новый проект портфолио
                        $model=new Portfolio('making');
                        $model->author_id = $user->id;
                        $model->status = Portfolio::STATUS_MAKING;
                        $model->service_id = (int)$service;

                        // Редирект на редактирование созданного проекта
                        if($model->save())
                                return $this->redirect("/users/{$user->login}/portfolio/create/{$model->id}");
                } else {
                        throw new CHttpException(404);
                }
		
                /**
                 * Редактирование уже сохраненного ранее портфолио
                 */
		if(!$model->isNewRecord && isset($_POST['Portfolio']))
		{
                        /**
                         * Для формы Simple
                         * Сохранение новых изображений с их описанием
                         */
                        if(isset($_POST['Portfolio']['new'])) {
                                foreach($_POST['Portfolio']['new'] as $file_key=>$desc){
					$model->setImageType('portfolio');
					$image = UploadedFile::loadImage($model, 'file_'.$file_key, $desc['filedesc']);
					if ($image) {
						$rel = new PortfolioUploadedfile();
						$rel->item_id = $model->id;
						$rel->file_id = $image->id;
						$rel->save();
					}
                                }
                        }

                        /**
                         * Для формы FileAPI и Simple
                         * Удаление изображений (проход по массиву id изображений для удаления)
                         */
                        if(isset($_POST['Portfolio']['delete'])) {
                                foreach($_POST['Portfolio']['delete'] as $file_id){
                                        $img = PortfolioUploadedfile::model()->findByAttributes(array('item_id'=>$model->id, 'file_id'=>(int)$file_id));
                                        if($img && $img->portfolio->author_id == Yii::app()->user->id) 
                                                $img->delete();
                                }
                        }
                        
                        /**
                         * Для формы FileAPI и Simple
                         * Обновление описаний для уже созданых ранее изображений
                         */
                        if(isset($_POST['Portfolio']['filedesc'])) {
                                foreach($_POST['Portfolio']['filedesc'] as $file_id => $file_desc) {
                                        $img = PortfolioUploadedfile::model()->findByAttributes(array('item_id'=>$model->id, 'file_id'=>(int)$file_id));
                                        if(!$img || $img->portfolio->author_id != Yii::app()->user->id) 
                                                continue;
                                        
                                        $uf = UploadedFile::model()->findByPk((int)$file_id);
                                        $uf->desc = CHtml::encode($file_desc);
                                        $uf->save();
                                }
                        }

                        $toDraft = isset($_POST['toDraft']) ? true : false;

                        /**
                         * Сохранение проекта в черновик
                         */
                        if($toDraft) {
                                $model->setScenario('making');
                                $model->attributes=$_POST['Portfolio'];
                                $model->author_id = Yii::app()->user->id;
                                $model->status = Portfolio::STATUS_MAKING;

                                if($model->save())
                                        $this->redirect("/users/{$user->login}/portfolio/draft");
                        }

                        /**
                         * Публикация проекта
                         */
                        if(!$toDraft) {
                                $model->setScenario('create');
                                $model->attributes=$_POST['Portfolio'];
                                $model->author_id = Yii::app()->user->id;
                                $model->status = Portfolio::STATUS_MODERATING;

                                if($model->save())
                                        $this->redirect("/users/{$user->login}/portfolio/service/{$model->service_id}");
                        }

		}
                
        	$this->render('//idea/portfolio/create', array(
				'model'=>$model,
				'user'=>$user,
				'pageVersion'=> Yii::app()->user->fileApiSupport ? 'fileApi' : 'simple',
			), false,
			array( 'profileSpecialist', array('user'=>$user ) )
		);
	}

        /**
         * Загрузка изображений портфолио на fileApi
         * @param pid - Portfolio id
         */
        public function actionUpload($pid = null)
        {
		$portfolio = Portfolio::model()->findByPk(intval($pid));
		if (is_null($portfolio))
			die( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );

		$portfolio->setImageType('portfolio');

		$image = UploadedFile::loadImage($portfolio, 'file', '');

		if ($image) {
			$rel = new PortfolioUploadedfile();
			$rel->item_id = $portfolio->id;
			$rel->file_id = $image->id;
			$rel->save();

			$html = $this->renderPartial('_imageItem', array('file'=>$image), true);
			die( json_encode( array('success'=>true, 'html'=>$html), JSON_NUMERIC_CHECK ) );
		} else {
			die( json_encode( array('error'=>true), JSON_NUMERIC_CHECK ) );
		}
        }
        
	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
                $model = $this->loadModel($id);
                if(Yii::app()->user->id == $model->author_id && $model->status != Portfolio::STATUS_DELETED) {
                        $user = Yii::app()->user->model;
                        $model->status = Portfolio::STATUS_DELETED;
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
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Portfolio::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}


}
