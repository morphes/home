<?php
/**
 * @var $model ContractorContact
 */
?>
<div class="clearfix">
	<div class="span3">
		<a class="contractor_del" data-id="<?php echo $model->id; ?>" href="#">Удалить</a>
	</div>
	<?php $class = $model->hasErrors('name') ? ' error' : ''; ?>
	<div class="clearfix<?php echo $class; ?>">
		<label><?php echo $model->getAttributeLabel('name'); ?></label>
		<div class="input">
			<?php echo CHtml::textField('ContractorContact['.$model->id.'][name]', $model->name, array('class'=>'span5'.$class,'maxlength'=>255)); ?>
		</div>
	</div>
	<?php $class = $model->hasErrors('post') ? ' error' : ''; ?>
	<div class="clearfix<?php echo $class; ?>">
		<label><?php echo $model->getAttributeLabel('post'); ?></label>
		<div class="input">
			<?php echo CHtml::textField('ContractorContact['.$model->id.'][post]', $model->post, array('class'=>'span5'.$class,'maxlength'=>255)); ?>
		</div>
	</div>
	<?php $class = $model->hasErrors('mobile') ? ' error' : ''; ?>
	<div class="clearfix<?php echo $class; ?>">
		<label><?php echo $model->getAttributeLabel('mobile'); ?></label>
		<div class="input">
			<?php echo CHtml::textField('ContractorContact['.$model->id.'][mobile]', $model->mobile, array('class'=>'span5'.$class,'maxlength'=>255)); ?>
		</div>
	</div>
	<?php $class = $model->hasErrors('phone') ? ' error' : ''; ?>
	<div class="clearfix<?php echo $class; ?>">
		<label><?php echo $model->getAttributeLabel('phone'); ?></label>
		<div class="input">
			<?php echo CHtml::textField('ContractorContact['.$model->id.'][phone]', $model->phone, array('class'=>'span5'.$class,'maxlength'=>255)); ?>
		</div>
	</div>
	<?php $class = $model->hasErrors('email') ? ' error' : ''; ?>
	<div class="clearfix<?php echo $class; ?>">
		<label><?php echo $model->getAttributeLabel('email'); ?></label>
		<div class="input">
			<?php echo CHtml::textField('ContractorContact['.$model->id.'][email]', $model->email, array('class'=>'span5'.$class,'maxlength'=>50)); ?>
		</div>
	</div>

</div>