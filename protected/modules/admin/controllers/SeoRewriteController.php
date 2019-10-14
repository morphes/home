<?php

class SeoRewriteController extends AdminController
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
				'roles' => array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_SEO,
				),
			),
		);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new SeoRewrite;

		$model->normal_url = trim( urldecode(Yii::app()->getRequest()->getParam('url', '')), ' /' );


		if(isset($_POST['SeoRewrite']))
		{
			$model->attributes=$_POST['SeoRewrite'];
			$forward = isset($_POST['forward']);

			$model->normal_url = trim( urldecode($model->normal_url), ' /' );
			$model->seo_url = trim( urldecode($model->seo_url), ' /' );


			$server = $_SERVER;
			$get = $_GET;

			$host = parse_url( $model->normal_url, PHP_URL_HOST );
			$requestUri = $model->normal_url;

			if(strpos($requestUri,$host)!==false)
				$requestUri=trim( preg_replace('/^\w+:\/\/[^\/]+/','', $requestUri), ' /');

			$_SERVER['HTTP_HOST'] = $host;
			$_SERVER['REQUEST_URI'] = $requestUri;

			$data = array();
			mb_parse_str( parse_url( $model->normal_url, PHP_URL_QUERY), $data );
			$_GET = $data;

			$class = get_class(Yii::app()->getRequest());
			$request2 = new $class;
			$route=Yii::app()->getUrlManager()->parseUrl($request2);
			$_GET['cache'] = Cache::getCacheInfo();

			$model->param = serialize($_GET);
			$model->path = $route;

			$model->subdomain = $model->getSubdomain($host);
			if ($model->subdomain===null)
				$model->subdomain = '';

			/* Правило формирования md5 */
			$model->normal_md5 = md5( $model->subdomain.'|'.$requestUri );
			$model->seo_md5 = md5( $model->subdomain.'|'.$model->seo_url );

			$_GET = $get;
			$_SERVER = $server;

			if($model->save()) {
				if ($forward) {
					$host = parse_url( $model->normal_url, PHP_URL_HOST );
					$this->redirect('http://'.$host.'/'.$model->seo_url);
				} else
					$this->redirect(array('index'));
			}
			if ($model->hasErrors('seo_md5')) {
				$model->clearErrors('seo_md5');
				$model->addError('seo_url', 'Адрес уже используется');
			}

		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionUpdate()
	{
		$md5 = Yii::app()->getRequest()->getParam('normal_md5', '');
		/** @var $model SeoRewrite */
		$model = SeoRewrite::model()->findByAttributes(array('normal_md5'=>$md5));
		if ( $model===null )
			throw new CHttpException(404);

		if(isset($_POST['SeoRewrite']))
		{
			$model->attributes=$_POST['SeoRewrite'];
			$forward = isset($_POST['forward']);
			$model->seo_url = trim( urldecode($model->seo_url), ' /' );
			$model->seo_md5 = md5( $model->subdomain.'|'.$model->seo_url );

			if($model->save()) {
				if ($forward) {
					$host = parse_url( $model->normal_url, PHP_URL_HOST );
					$this->redirect('http://'.$host.'/'.$model->seo_url);
				} else
					$this->redirect(array('index'));
			}
			if ($model->hasErrors('seo_md5')) {
				$model->clearErrors('seo_md5');
				$model->addError('seo_url', 'Адрес уже используется');
			}

		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 */
	public function actionDelete()
	{
		$md5 = Yii::app()->getRequest()->getParam('normal_md5', '');

		/** @var $model SeoRewrite */
		$model = SeoRewrite::model()->findByAttributes(array('normal_md5'=>$md5));
		if ( $model===null )
			throw new CHttpException(404);

		$model->delete();
		if (Yii::app()->getRequest()->getIsAjaxRequest()) {
			die( json_encode(array('success'=>true), JSON_NUMERIC_CHECK) );
		} else {
			$this->redirect(array('index'));
		}
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model=new SeoRewrite('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['SeoRewrite']))
			$model->attributes=$_GET['SeoRewrite'];

		$this->render('index',array(
			'model'=>$model,
		));

	}
}
