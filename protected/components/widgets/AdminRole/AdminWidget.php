<?php
/**
 * Виджет для подмены роли poweradmin
 */
class AdminWidget extends CWidget
{
	public $isFront = false;

	private $_stop = true;
	private $_role = User::ROLE_POWERADMIN;

	public function init()
	{
		$role = Yii::app()->getUser()->getRole();
		if ( $role == User::ROLE_POWERADMIN ||
			( (Yii::app()->session->get('REAL_ROLE', null)==User::ROLE_POWERADMIN ) && !Yii::app()->getUser()->getIsGuest() )
		) {
			$this->_role = $role;
			$this->_stop = false;
		}
	}

	public function run()
	{
		if ($this->_stop)
			return false;

		if ( !$this->isFront ) {
			// Получаем путь до файлов компонента
			$dir = dirname(__FILE__);
			$assets = Yii::app()->getAssetManager()->publish($dir . DIRECTORY_SEPARATOR . 'assets');

			/** @var $cs CClientScript */
			$cs = Yii::app()->getClientScript();
			$cs->registerCssFile($assets . '/css/admstyle.css');

			$this->render('index', array('role'=>$this->_role));
		} else {
			$this->render('front', array('role'=>$this->_role));
		}

	}
}
