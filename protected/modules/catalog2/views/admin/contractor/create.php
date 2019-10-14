<?php
/**
 * @var $model Contractor
 */

$this->breadcrumbs=array(
	'Контрагенты'=>array('index'),
	'Новый контрагент',
);

?>

<h1>Новый контрагент</h1>

<?php /** @var $form BootActiveForm */
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'contractor-form',
	'enableAjaxValidation'=>false,
)); ?>

<?php echo $form->errorSummary($model); ?>

<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->textFieldRow($model,'site',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->dropDownListRow($model, 'worker_id', Contractor::getSalesList()); ?>

<?php echo $form->textAreaRow($model,'comment',array('class'=>'span5','maxlength'=>3000, 'rows'=>5)); ?>

<?php echo $form->dropDownListRow($model,'status', $model->getPublicStatuses(), array('class'=>'span5')); ?>


<h1>Юр. Данные</h1>

<?php echo $form->textFieldRow($model,'legal_person',array('class'=>'span5','maxlength'=>255, 'placeholder'=>'Иванов, ИП')); ?>

<?php echo $form->textFieldRow($model,'legal_address',array('class'=>'span5','maxlength'=>255, 'placeholder'=>'630047')); ?>

<?php echo $form->textFieldRow($model,'actual_address',array('class'=>'span5','maxlength'=>255, 'placeholder'=>'630047')); ?>

<?php echo $form->textFieldRow($model,'inn',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->textFieldRow($model,'kpp',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->textFieldRow($model,'ogrn',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->textFieldRow($model,'current_account',array('class'=>'span5','maxlength'=>255)); ?>

<div class="clearfix">
	<label><?php echo $model->getAttributeLabel('bank_id'); ?></label>
	<div class="input">
		<?php
		$this->widget('application.components.widgets.EAutoComplete', array(
			'valueName'	=> $model->getBankData(),
			'sourceUrl'	=> '/admin/utility/acBank',
			'value'		=> $model->bank_id,
			'options'	=> array(
				'showAnim'	=>'fold',
				'open' => 'js:function(){
						//$(".ui-autocomplete").css("width", "168px");
					}',
				'minLength' => 4
			),
			'htmlOptions'	=> array('id'=>'bank_id', 'name'=>'Contractor[bank_id]', 'class' => 'span12'),
			'cssFile' => null,
		));
		?>
	</div>

</div>

<?php echo $form->dropDownListRow($model, 'taxation_system', Contractor::$nalogNames, array('class'=>'span5')); ?>

<?php echo $form->textFieldRow($model,'office_phone',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->textFieldRow($model,'office_fax',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->textFieldRow($model,'email',array('class'=>'span5','maxlength'=>50)); ?>

<div class="actions">
	<?php echo CHtml::submitButton('Создать', array('class'=>'btn primary large')); ?>
	<?php echo CHtml::button('Отмена', array('class'=>'btn default large','onclick' => 'document.location = "/catalog2/admin/contractor/index"'));?>
	<?php echo CHtml::button('Удалить', array('class'=>'danger btn','onclick' => 'if (!confirm("Удалить")) { return false; } else { document.location="/catalog2/admin/contractor/delete/id/'.$model->id.'" }')); ?>
</div>

<?php $this->endWidget(); ?>


