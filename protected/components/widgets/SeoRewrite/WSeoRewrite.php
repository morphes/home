<?php

/**
 * @brief виджет управления реврайтами
 */
class WSeoRewrite extends CWidget
{
	/** @var bool Флаг означающий, что UI уже был отрендерен */
	private static $_uiRendered = false;

        public function init()
	{
	}

	public function run()
	{

		if ( ! in_array(Yii::app()->getUser()->getRole(), array(User::ROLE_POWERADMIN, User::ROLE_ADMIN, User::ROLE_SEO)))
			return;

		if (self::$_uiRendered)
			return;

		self::$_uiRendered = true;

		$mall = Cache::getInstance()->mallBuild;
		if ( $mall!==null && $mall instanceof MallBuild) {
			$subdomain = $mall->key;
		} else {
			$subdomain = '';
		}

		$url = trim( urldecode(Yii::app()->getRequest()->getRequestUri()), ' /' );
		$md5 = md5( $subdomain.'|'.$url );
		$model = SeoRewrite::model()->find(array('condition'=>'seo_md5=:s1 OR normal_md5=:s1', 'params'=>array(':s1'=>$md5)));

		if ( $model===null ) {
			$model = new SeoRewrite();
			$model->status = SeoRewrite::STATUS_NO;
		}

		$this->render('control', array(
			'model' => $model
		));
	}
}