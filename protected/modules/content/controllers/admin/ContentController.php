<?php

/**
 * @brief Управление статьями на сайте
 * @author Roman Kuzakov
 */
class ContentController extends AdminController
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
                        'actions' => array('index', 'view', 'create', 'upload', 'update', 'admin', 'delete'),
                        'roles' => array(
				User::ROLE_ADMIN,
				User::ROLE_MODERATOR,
				User::ROLE_SENIORMODERATOR,
				User::ROLE_POWERADMIN
			),
                    ),
                    array('deny',
                        'users' => array('*'),
                    ),
                );
        }

        /**
         * @brief Просмотр статьи
         */
        public function actionView($id)
        {
                $this->render('view', array(
                    'model' => $this->loadModel($id),
                ));
        }

        /**
         * @brief Создание статьи
         */
        public function actionCreate()
        {
                $model = new Content;

                $contentCategory = ContentCategory::model()->roots()->find();

                if ($contentCategory) {
                        $categories = $contentCategory->descendants(true)->findAll();
                        $cats = array($contentCategory->id => 'Без родителя');
                        foreach ($categories as $cat) {
                                $cats+=array($cat->id => str_repeat('--', $cat->level - 1) . ' ' . $cat->title);
                        }
                } else {
                        $cats = array();
                }

                if (isset($_POST['Content'])) {
                        $model->attributes = $_POST['Content'];
                        $model->author_id = Yii::app()->user->id;
                        if ($model->save())
                                $this->redirect(array('view', 'id' => $model->id));
                }

                $this->render('create', array(
                    'model' => $model,
                    'cats' => $cats,
                ));
        }

        /**
         * @brief Редактирование статьи
         */
        public function actionUpdate($id)
        {
                $model = $this->loadModel($id);

                $contentCategory = ContentCategory::model()->roots()->find();

                if ($contentCategory) {
                        $categories = $contentCategory->descendants(true)->findAll();
                        $cats = array($contentCategory->id => 'Без родителя');
                        foreach ($categories as $cat) {
                                $cats+=array($cat->id => str_repeat('--', $cat->level - 1) . ' ' . $cat->title);
                        }
                } else {
                        $cats = array();
                }
                if (isset($_POST['Content'])) {
                        $model->attributes = $_POST['Content'];
                        if ($model->save())
                                $this->redirect(array('view', 'id' => $model->id));
                }

                $this->render('update', array(
                    'model' => $model,
                    'cats' => $cats,
                ));
        }

        /**
         * @brief Удаление статьи
         */
        public function actionDelete($id)
        {
                if (Yii::app()->request->isPostRequest) {
                        // we only allow deletion via POST request
                        $this->loadModel($id)->delete();

                        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
                        if (!isset($_GET['ajax']))
                                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
                }
                else
                        throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }

        public function actionIndex()
        {
                $dataProvider = new CActiveDataProvider('Content');
                $this->render('index', array(
                    'dataProvider' => $dataProvider,
                ));
        }

        public function actionAdmin()
        {
		$this->rightbar = null;
		
                $model = new Content('search');
                $model->unsetAttributes();  // clear any default values
                if (isset($_GET['Content']))
                        $model->attributes = $_GET['Content'];

                $this->render('admin', array(
                    'model' => $model,
                ));
        }

        public function loadModel($id)
        {
                $model = Content::model()->findByPk($id);
                if ($model === null)
                        throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
        }

        protected function performAjaxValidation($model)
        {
                if (isset($_POST['ajax']) && $_POST['ajax'] === 'content-form') {
                        echo CActiveForm::validate($model);
                        Yii::app()->end();
                }
        }
        
        public function actionUpload()
        {
                $uploaded = Yii::app()->file->set('upload');
                $fname = time() . $_FILES["upload"]['name'];
                
                $image = Yii::app()->image->load($_FILES["upload"]["tmp_name"]);
                $image->quality(85)->save(Content::UPLOAD_IMAGE_DIR . $fname);

                $url = $this->createAbsoluteUrl('/' . Content::UPLOAD_IMAGE_DIR . $fname);
                $callback = $_GET['CKEditorFuncNum'];
                $output = '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(' . $callback . ', "' . $url . '","' . '' . '");</script>';
                echo $output;
                die();
        }

}
