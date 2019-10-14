<?php
$this->breadcrumbs=array(
	'Управление пользователями' => array('/admin/user/userlist'),
	'Группы пользователей' => array('admin'),
	'Редактирование'
);
?>


<h1>Редактирование группы #<?php echo $model->id; ?></h1>


<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>