<?php $this->pageTitle = 'Черновики пользователя — MyHome.ru'?>

<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/profile.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>

<?php $this->renderPartial('//idea/portfolio/_serviceNavigator', array('user' => $user, 'currentServiceId'=>'draft')); ?>

<?php $this->widget('application.components.widgets.ProjectList.ProjectListWidget',
	array(
		'projects'=>$projects,
		'galleryAdditionalClass'=>'user_profile'
	)
); ?>