<?php Yii::app()->clientScript->registerCoreScript('jquery'); ?>

<?php $this->widget('ext.FileUpload.FileUpload', array(
	'url'=>'/test/fileapi',
	'config'=> array(
		'onSuccess'=>'js:function(response){console.log("fuck!!!");}',
	),
)); ?>

