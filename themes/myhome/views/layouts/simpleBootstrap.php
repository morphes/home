<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<?php echo Yii::app()->bootstrap->registerBootstrap(); ?>
	<link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/admin/add.css" />
	<?php Yii::app()->getClientScript()->registerCoreScript('jquery');?>
</head>

<body>
	<?php echo $content; ?>
</body>
</html>