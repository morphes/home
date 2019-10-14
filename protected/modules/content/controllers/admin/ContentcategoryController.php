<?php

/**
 * @brief Управление категориями статического контента
 * @see NestedSetBehavior
 * @author Roman Kuzakov
 */
class ContentcategoryController extends AdminController
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
                        'actions' => array('index', 'view', 'create', 'update', 'delete'),
                        'roles' => array(
				User::ROLE_ADMIN,
				User::ROLE_MODERATOR,
				User::ROLE_POWERADMIN,
				User::ROLE_SENIORMODERATOR,
			),
                    ),
                    array('deny',
                        'users' => array('*'),
                    ),
                );
        }

        public function actionView($id)
        {
                throw new CHttpException(404);

                $this->render('view', array(
                    'model' => $this->loadModel($id),
                ));
        }

        /**
         * Создание
         */
        public function actionCreate()
        {
                $model = new ContentCategory('create');
                $contentCategory = ContentCategory::model()->roots()->find();

                if ($contentCategory) {
                        $categories = $contentCategory->descendants(true)->findAll();
                } else {
                        ContentCategory::checkRoot();
                        $contentCategory = ContentCategory::model()->roots()->find();
                }

                // Uncomment the following line if AJAX validation is needed
                // $this->performAjaxValidation($model);

                if (isset($_POST['ContentCategory'])) {
                        $model->attributes = $_POST['ContentCategory'];
                        $model->author_id = Yii::app()->user->id;

                        if ($model->validate() && isset(ContentCategory::$statuses[$model->status])) {
                                $root = ContentCategory::model()->findByPk($model->node_id);
                                if (!$root) {
                                        ContentCategory::checkRoot();
                                        $root = ContentCategory::model()->roots()->find();
                                }


                                if ($model->appendTo($root, true))
                                        $this->redirect(array('index'));
                        }
                }

                $this->render('create', array(
                    'model' => $model,
                    'categories' => $categories,
                    'root_id' => $contentCategory->id,
                ));
        }
        
        /**
         * Удаление
         * @param integer $id 
         */
        public function actionUpdate($id)
        {
                $model = $this->loadModel($id);

                $contentCategory = ContentCategory::model()->roots()->find();

                if ($contentCategory) {
                        $categories = $contentCategory->descendants(true)->findAll();
                } else {
                        ContentCategory::checkRoot();
                        $contentCategory = ContentCategory::model()->roots()->find();
                }

                $model->node_id = $model->getParent();

                if (isset($_POST['ContentCategory'])) {
                        $model->attributes = $_POST['ContentCategory'];

                        if ($model->validate()) {

                                $target = ContentCategory::model()->findByPk($model->node_id);
                                if (!$target) {
                                        ContentCategory::checkRoot();
                                        $target = ContentCategory::model()->roots()->find();
                                }
                                if ($target->isDescendantOf($model))
                                        $model->addError('node_id', 'Нельзя переместить родительский пункт в дочерний');
                                if ($target->equals($model))
                                        $model->addError('node_id', 'Нельзя перемещать категорию в себя');

                                if (!$model->getErrors() && $model->saveNode() && $model->moveAsLast($target))
                                        $this->redirect(array('index'));
                        }
                }


                $this->render('update', array(
                    'model' => $model,
                    'categories' => $categories,
                    'root_id' => $contentCategory->id,
                ));
        }

        /**
         * Удаление
         * @param integer $id 
         */
        public function actionDelete($id)
        {

                // we only allow deletion via POST request
                $this->loadModel($id)->deleteNode();

                // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
                if (!isset($_GET['ajax']))
                        $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        }

        public function actionIndex()
        {
                $contentCategory = ContentCategory::model()->roots()->find();
                if ($contentCategory) {
                        $categories = $contentCategory->descendants(true)->findAll();
                } else {
                        ContentCategory::checkRoot();
                        $contentCategory = ContentCategory::model()->roots()->find();
                }

                $this->render('index', array(
                    'categories' => $categories,
                ));
        }

        public function loadModel($id)
        {
                $model = ContentCategory::model()->findByPk($id);
                if ($model === null)
                        throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
        }

        protected function performAjaxValidation($model)
        {
                if (isset($_POST['ajax']) && $_POST['ajax'] === 'content-category-form') {
                        echo CActiveForm::validate($model);
                        Yii::app()->end();
                }
        }

}
