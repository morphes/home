<?php

class UsergroupController extends AdminController
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
				User::ROLE_MODERATOR,
				User::ROLE_SENIORMODERATOR,
				User::ROLE_POWERADMIN,
				User::ROLE_SALEMANAGER
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
                $users=new CActiveDataProvider('UserGroupuser', array(
                    'criteria'=>array(
                        'condition'=>'group_id = '.(int)$id,
                    ),
                    'pagination'=>array(
                        'pageSize'=>50,
                    ),
                ));
                
		$this->render('view',array(
			'model'=>$this->loadModel($id),
                        'users'=>$users,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Usergroup;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Usergroup']))
		{
			$model->attributes=$_POST['Usergroup'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Usergroup']))
		{
			$model->attributes=$_POST['Usergroup'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Usergroup');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Usergroup('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Usergroup']))
			$model->attributes=$_GET['Usergroup'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Usergroup::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='usergroup-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
        
        
        public function actionAppend()
        {
                $gid = Yii::app()->request->getParam('group_id');
                $uid = Yii::app()->request->getParam('user_id');
                $append = Yii::app()->request->getParam('append'); // true - добавление, false - удаление
                
                if($append && !UserGroupuser::model()->exists('user_id = :uid AND group_id = :gid', array(':uid'=>$uid, ':gid'=>$gid))){
                        
                        // Добавление в группу
                        $usergroup = new UserGroupuser();
                        $usergroup->user_id = $uid;
                        $usergroup->group_id = $gid;
                        if($usergroup->save()){
                                $response = CJSON::encode(array('result'=>'appended', 'gid'=>$gid));
                                die($response);
                        }
                        
                } else {
                        
                       // Удаление из группы
                       $usergroup = UserGroupuser::model()->find('user_id = :uid AND group_id = :gid', array(':uid'=>$uid, ':gid'=>$gid));
                       if($usergroup && $usergroup->delete()){
                               $response = CJSON::encode(array('result'=>'deleted', 'gid'=>$gid));
                               die($response);
                       }
                }
                
                // Ошибка операции
                $response = CJSON::encode(array('result'=>'error', 'gid'=>$gid));
                die($response);
        }
        
        public function actionDeleteuser($uid = null, $gid = null){
                if(Yii::app()->request->isAjaxRequest && Yii::app()->request->getParam('ajax') == 'users-grid'){
                        $useringroup = UserGroupuser::model()->findByAttributes(array('user_id'=>(int)$uid, 'group_id'=>(int)$gid));
                        $useringroup->delete();
                        die('ok');
                }
                throw new CHttpException(404);
        }
        
}
