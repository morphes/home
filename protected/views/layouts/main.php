<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />
        
	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->

	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
        
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>
        <div class="test"></div>
<div class="container" id="page">

	<div id="header">
		<div id="logo"><?php echo CHtml::encode(Yii::app()->name); ?></div>
	</div><!-- header -->

	<div id="mainmenu">
		<?php 
                $this->widget('zii.widgets.CMenu', array(
                    'items' => array(
                        array('label' => 'Главная', 'url' => array('/site/index')),
                        array('label' => 'Регистрация пользователя', 'url' => array('/site/userregistration'), 'visible' => Yii::app()->user->isGuest),
                        array('label' => 'Регистрация магазина', 'url' => array('/site/shopregistration'), 'visible' => Yii::app()->user->isGuest),
                        array('label' => 'Вход', 'url' => array('/site/login'), 'visible' => Yii::app()->user->isGuest),
                        array('label' => 'Выход (' . Yii::app()->user->name . ')', 'url' => array('/site/logout'), 'visible' => !Yii::app()->user->isGuest),
                        array('label' => 'Панель администрирования', 'url' => array('/admin'), 'visible' => Yii::app()->user->checkAccess(User::ROLE_ADMIN)),
                    ),
                ));
                ?>
	</div><!-- mainmenu -->


               
                
	<div class="container">
                <div id="content">
                        
                        <?php echo $content; ?>
                </div><!-- content -->
        </div>

	<div id="footer">
		Copyright &copy; <?php echo date('Y'); ?> by MyHome.<br/>
		All Rights Reserved.<br/>
	</div><!-- footer -->

</div><!-- page -->

</body>
</html>