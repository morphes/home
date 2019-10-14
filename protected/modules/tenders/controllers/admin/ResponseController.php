<?php
/**
 * Description of ResponseController
 *
 * @author alexsh
 */
class ResponseController extends AdminController
{
	public $layout = 'webroot.themes.myhome.views.layouts.backend';
	
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
			    'actions' => array('list', 'view', 'update', 'delete'),
			    'roles'=>array(User::ROLE_ADMIN, User::ROLE_MODERATOR, User::ROLE_POWERADMIN, User::ROLE_SENIORMODERATOR),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actionList()
	{
		// configure filter
		$model = new TenderResponse('search');
		if(isset($_GET['TenderResponse']))
			$model->attributes=$_GET['TenderResponse'];
		
		$date_from = Yii::app()->request->getParam('date_from');
		$date_to = Yii::app()->request->getParam('date_to');
		
		
		$criteria = new CDbCriteria();
		if ($date_from)
			$criteria->compare('t.create_time', '>='.(strtotime($date_from)));
		if ($date_to)
			$criteria->compare('t.create_time', '<='.(strtotime($date_to)+86400));
		
		$criteria->compare('t.author_id', $model->author_id);
		if ($model->id)
			$criteria->compare('t.id', explode(',', $model->id), true);
		
		$criteria->order = 'id DESC';
		
		$pageSize = 50;
		
		$dataProvider = new CActiveDataProvider('TenderResponse', array( 
		    'criteria' => $criteria, 
		    'pagination' => array('pageSize' => $pageSize, 'pageVar'=>'page'),
		));
		
		
		$this->render('list', array(
			'model' => $model,
			'dataProvider' => $dataProvider,
			'date_from' => $date_from,
			'date_to' => $date_to,
		));
	}
	
	/**
	 * Страница отклика на тендер
	 * @param integer $id
	 * @throws CHttpException 
	 */
	public function actionView($id = null)
	{
		$response = TenderResponse::model()->findByPk(intval($id));
		if (is_null($response) )
			throw new CHttpException(404);
		
		$user = $response->getUser();
		
		$this->render('view', array(
			'response'=>$response, 
			'user'=>$user, 
		));
	}
	
	/**
	 * Обновление отклика на тендер
	 * @param integer $id
	 * @throws CHttpException 
	 */
	public function actionUpdate($id = null)
	{
		$id = intval($id);
		if ( empty($id) )
			throw new CHttpException(404);

		$response = TenderResponse::model()->findByPk( intval($id) );
		
		if( is_null($response) )
			throw new CHttpException(404);
		
		$user = $response->getUser();
		
		$response->setScenario('admUpdate');
		if (Yii::app()->request->isPostRequest && isset($_POST['TenderResponse'])) {
			$response->attributes = $_POST['TenderResponse'];
			
			if ( $response->validate() ) {
				$response->save(false);
				$this->redirect("/tenders/admin/response/view/id/{$response->id}");
			}
		}
		
		return $this->render('update', array(
				'user' => $user,
				'response' => $response,
		    ));

	}
	
	/**
	 * Удаление отклика
	 */
	public function actionDelete($id = null)
	{
		$response = TenderResponse::model()->findByPk(intval($id));
		if ( is_null($response) || !Yii::app()->request->isAjaxRequest ) 
			throw new CHttpException(404);
		
		$response->delete();
		die( CJSON::encode( array('success'=>true) ) );
	}
}

