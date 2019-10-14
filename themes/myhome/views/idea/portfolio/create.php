<?php $this->pageTitle = 'Редактирование проекта — MyHome.ru'?>

<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>

<?php echo $this->renderPartial('_serviceNavigator', array('user'=>$user,'currentServiceId'=>$model->service_id)); ?>

<?php echo $this->renderPartial('_form_' . $pageVersion, array('model'=>$model)); ?>



