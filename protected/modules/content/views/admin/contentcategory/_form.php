<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id'=>'content-category-form',
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($model); ?>
  
	
	<?php echo $form->textFieldRow($model,'title',array('maxlength'=>200, 'class' => 'span6')); ?>
	
        
        <?php 
	$cats = array($root_id => 'Без родителя');
	foreach($categories as $cat){
		$cats+=array($cat->id=>str_repeat('--', $cat->level-1). ' ' .$cat->title);
	}
        echo $form->dropDownListRow($model,'node_id', $cats);
	?>
	
	<?php echo $form->dropDownListRow($model,'status', ContentCategory::$statuses); ?>
        
	<?php echo $form->textFieldRow($model,'alias',array('maxlength'=>100, 'class' => 'span6')); ?>
	
	<?php echo $form->textFieldRow($model,'desc',array('maxlength'=>100, 'class' => 'span6')); ?>
	

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', array('class' => 'btn large primary')); ?>
	</div>

<?php $this->endWidget(); ?>