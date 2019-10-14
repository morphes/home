<?php
/**
 * @var $model SeoRewrite
 * @var $form BootActiveForm
 */
$this->breadcrumbs=array(
	'Seo Rewrites'=>array('index'),
	$model->seo_url=>array('view','id'=>$model->seo_url),
	'Обновление',
);

?>

<h1>Редактирование Seo Rewrite "<?php echo $model->seo_url; ?>"</h1>

<?php
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'seo-rewrite-form',
	'enableAjaxValidation'=>false,
)); ?>

<?php echo $form->errorSummary($model); ?>

<?php echo $form->textFieldRow($model,'seo_url',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->dropDownListRow($model,'status', SeoRewrite::$statusNames, array('class'=>'span5')); ?>

<?php echo $form->textAreaRow($model,'desc',array('class'=>'span5')); ?>

<?php echo $form->uneditableRow($model,'normal_md5',array('class'=>'span5')); ?>

<div class="actions">
	<?php echo CHtml::submitButton('Сохранить',array('class'=>'btn primary')); ?>

	<?php echo CHtml::submitButton('Сохранить и перейти', array('class'=>'btn info span4', 'name' => 'forward'));?>

	<?php
		echo CHtml::link('Удалить',
			SeoRewrite::getLink('delete', array('normal_md5' => $model->normal_md5)),
			array('class'=>'btn danger', 'onclick' => 'if (!confirm("Удалить?")) return false;')
		);
	?>
</div>

<?php $this->endWidget(); ?>
