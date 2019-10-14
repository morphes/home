<?php

class HelpController extends AdminController
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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('list', 'uploadImage'),
				'roles'=>array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_JOURNALIST,
				),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionList($base = Help::BASE_USER)
	{
		if ( empty(Help::$baseNames[$base]))
			throw new CHttpException(404);

		$this->render('list', array('baseId'=>$base));
	}

	public function actionUploadImage()
	{
		$sectionId = intval( Yii::app()->getRequest()->getParam('section_id') );
		$callback = Yii::app()->getRequest()->getParam('CKEditorFuncNum');

		$section = HelpSection::model()->findByPk($sectionId);
		if ( is_null($section) || empty($callback) )
			throw new CHttpException(404);

		$path = Help::UPLOAD_DIR . '/'.$section->id;
		if ( !file_exists($path) )
			mkdir($path, 0755, true);

		$saveName = $path . '/' . time().'_'.$_FILES['upload']['name'];

		$imageHandler = new imageHandler($_FILES['upload']['tmp_name'], imageHandler::FORMAT_JPEG);
		$imageHandler->resizeImage(700, 700, 80, imageHandler::RESIZE_DECREASE);
		$imageHandler->saveImage($saveName);

		$callback = $_GET['CKEditorFuncNum'];
		$url = $this->createAbsoluteUrl('/' . $saveName);

		$output = '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(' . $callback . ', "' . $url . '","' . '' . '");</script>';
		echo $output;
		die();
	}
}
