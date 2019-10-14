<h1>Создание группы</h1>

<?php echo CHtml::openTag('p');?>
<?php echo CHtml::link('Список групп', $this->createUrl('admin'));?>
<?php echo CHtml::closeTag('p');?>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>