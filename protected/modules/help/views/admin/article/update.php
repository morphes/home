<?php
$this->breadcrumbs=array(
	'Помощь' => array('/help/admin/help/list'),
	Help::$baseNames[$section->base_path_id] => array('/help/admin/help/list', 'base'=>$section->base_path_id),
	'Разделы' => array('/help/admin/section/list', 'base'=>$section->base_path_id),
	'Статьи' => array('list', 'section_id'=>$section->id),
);
?>
<h2><?php echo Help::$baseNames[$section->base_path_id]; ?> / Раздел: <?php echo CHtml::encode($section->name); ?> / </h2>
<?php if ($article->getIsNewRecord()) : ?>
	<h2>Добавление статьи</h2>
<?php
	$this->breadcrumbs[] = 'Добавление статьи';
else : ?>
	<h2>Редактирование статьи</h2>
<?php
	$this->breadcrumbs[] = 'Редактирование статьи';
endif; ?>



<?php /** @var $form BootActiveForm */
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'help-section-form',
	'enableAjaxValidation'=>false,
)); ?>

<?php echo $form->errorSummary($article); ?>

<?php echo $form->textFieldRow($article,'name',array('class'=>'span5','maxlength'=>255)); ?>

<?php //echo $form->textAreaRow($article,'description',array('class'=>'span5','maxlength'=>2048)); ?>

<div class="clearfix">
	<?php echo CHtml::activeLabelEx($article, 'data'); ?>
	<div class="input">
		<?php
		$this->widget('ext.editMe.ExtEditMe', array(
			'model' => $article,
			'attribute' => 'data',
			'htmlOptions' => array('class' => 'span8'),
			'contentsCss' => array('/css/ckeditor.css'),
			'filebrowserImageUploadUrl' => '/help/admin/help/uploadimage/section_id/'.$section->id,
			'enabledPlugins' => array('buttonh3'),
			'height' => '600px',
			'width' => '800px',
			'toolbar' =>
			array(
				array(
					'Bold', 'Italic', '-', 'buttonH3', '-'
				),
				array(
					'Link', 'Unlink', 'RemoveFormat',
				),
				array(
					'Image',
				),
				array(
					'Source',
				),
			),
		));
		?>
	</div>
</div>

<?php echo $form->dropDownListRow($article, 'status', HelpArticle::$statusNames, array('class'=>'span5')); ?>

<div class="actions">
	<?php echo CHtml::submitButton('Сохранить', array('class'=>'btn primary')); ?>
	<?php echo CHtml::button('Отмена', array('class'=>'btn default','onclick' => "document.location = '".$this->createUrl('/help/admin/article/list', array('section_id' => $section->id))."'"));?>
</div>

<?php $this->endWidget(); ?>
