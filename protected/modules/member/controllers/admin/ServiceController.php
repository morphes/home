<?php

class ServiceController extends AdminController
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
                        'actions' => array('index', 'view', 'update', 'create', 'delete', 'ChangePopular', 'createSynonym', 'deleteSynonym'),
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
         * Displays a particular model.
         * @param integer $id the ID of the model to be displayed
         */
        public function actionView($id)
        {
                $this->render('view', array(
                    'model' => $this->loadModel($id),
                ));
        }

        /**
         * Creates a new model.
         * If creation is successful, the browser will be redirected to the 'view' page.
         */
        public function actionCreate()
        {
                $model = new Service;

                // Uncomment the following line if AJAX validation is needed
                // $this->performAjaxValidation($model);

                if (isset($_POST['Service'])) {
                        $model->attributes = $_POST['Service'];
                        if ($model->save())
                                $this->redirect(array('create'));
                }

                $this->render('create', array(
                    'model' => $model,
                ));
        }

        /**
         * Updates a particular model.
         * If update is successful, the browser will be redirected to the 'view' page.
         * @param integer $id the ID of the model to be updated
         */
        public function actionUpdate($id)
        {
                $model = $this->loadModel($id);

                // Uncomment the following line if AJAX validation is needed
                // $this->performAjaxValidation($model);

                if (isset($_POST['Service'])) {
                        $model->attributes = $_POST['Service'];
                        if ($model->save())
                                $this->redirect(array('view', 'id' => $model->id));
                }

                $this->render('update', array(
                    'model' => $model,
                ));
        }

        /**
         * Deletes a particular model.
         * If deletion is successful, the browser will be redirected to the 'admin' page.
         * @param integer $id the ID of the model to be deleted
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

        /**
         * Manages all models.
         */
        public function actionIndex($pid = 0)
        {
                $dataProvider = new CActiveDataProvider('Service', array(
                            'criteria' => array(
                                'order'=>'case when parent_id = 0 then id else parent_id end,parent_id'
                            ),
                            'pagination' => array(
                                'pageSize' => 20,
                            )
                        ));

                $this->render('index', array(
                    'dataProvider' => $dataProvider,
                ));
        }

	/**
	 * Меняет галочку "Популярный" указанного сервися на проитвоположный
	 * @param $id ID сервиса
	 */
	public function actionChangePopular($id)
	{
		$success = false;

		$model = Service::model()->findByPk((int)$id);
		if ($model) {
			$newPopular = ($model->popular == Service::POPULAR_NO)
			              ? Service::POPULAR_YES
				      : Service::POPULAR_NO;
			Service::model()->updateByPk($model->id, array('popular' => $newPopular));
			$success = true;
		}

		die(json_encode(array(
			'success' => $success,
			'popular' => (bool)$newPopular
		)));
	}

        /**
         * Returns the data model based on the primary key given in the GET variable.
         * If the data model is not found, an HTTP exception will be raised.
         * @param integer the ID of the model to be loaded
         */
        public function loadModel($id)
        {
                $model = Service::model()->findByPk($id);
                if ($model === null)
                        throw new CHttpException(404, 'The requested page does not exist.');
                return $model;
        }

        /**
         * Performs the AJAX validation.
         * @param CModel the model to be validated
         */
        protected function performAjaxValidation($model)
        {
                if (isset($_POST['ajax']) && $_POST['ajax'] === 'service-form') {
                        echo CActiveForm::validate($model);
                        Yii::app()->end();
                }
        }

        /**
         * Добавление синонима к услуге
         * @param $service_id - id услуги
         * @param $synonym - текст синонима
         * @throws CHttpException
         */
        public function actionCreateSynonym($service_id, $synonym)
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $service = Service::model()->findByPk((int) $service_id);

                if(!$service || !$synonym)
                        throw new CHttpException(404);

                Yii::app()->db->createCommand()->insert('service_synonym', array(
                        'service_id'=>$service->id,
                        'synonym'=>CHtml::encode($synonym),
                        'is_servicename'=>0,
                ));
                $synonym_id = Yii::app()->db->createCommand()->select('max(id)')->from('service_synonym')->queryScalar();

                die(CJSON::encode(array('success'=>true, 'synonym_id'=>$synonym_id, 'synonym'=>$synonym)));
        }

        /**
         * Удаление синонима услуги
         * @param $synonym_id - id синонима
         * @throws CHttpException
         */
        public function actionDeleteSynonym($synonym_id)
        {
                if(!Yii::app()->request->isAjaxRequest)
                        throw new CHttpException(400);

                $synonym = Yii::app()->db->createCommand()->from('service_synonym')->where('id=:id', array(':id'=>(int) $synonym_id));

                if(!$synonym)
                        throw new CHttpException(404);

                Yii::app()->db->createCommand()->delete('service_synonym', 'id=:id', array(':id'=>(int) $synonym_id));

                die(CJSON::encode(array('success'=>true)));
        }
}
