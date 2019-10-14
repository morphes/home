<?php
$this->breadcrumbs=array(
	'Помощь' => array('/help/admin/help/list'),
	Help::$baseNames[$section->base_path_id] => array('/help/admin/help/list', 'base'=>$section->base_path_id),
	'Разделы' => array('/help/admin/section/list', 'base'=>$section->base_path_id),
	'Статьи' => array('/help/admin/article/list', 'section_id'=>$section->id),
	'Главы' => array('list', 'article_id'=>$article->id),
);
?>
<h2><?php echo Help::$baseNames[$section->base_path_id]; ?> / Раздел: <?php echo CHtml::encode($section->name); ?> / Статья: <?php echo CHtml::encode($article->name); ?> / </h2>
<?php if ($chapter->getIsNewRecord()) : ?>
	<h2>Добавление главы</h2>
<?php
	$this->breadcrumbs[] = 'Добавление главы';
else : ?>
	<h2>Редактирование главы</h2>
<?php
	$this->breadcrumbs[] = 'Редактирование главы';
endif; ?>



<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'help-section-form',
	'enableAjaxValidation'=>false,
)); ?>

<?php echo $form->errorSummary($chapter); ?>

<?php echo $form->textFieldRow($chapter,'anchor',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->textFieldRow($chapter,'name',array('class'=>'span5','maxlength'=>255)); ?>

<div class="clearfix">
	<?php echo CHtml::activeLabelEx($chapter, 'data'); ?>
	<div class="input">
		<?php
		$this->widget('ext.editMe.ExtEditMe', array(
			'model' => $chapter,
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

<?php echo $form->dropDownListRow($chapter, 'status', HelpChapter::$statusNames, array('class'=>'span5')); ?>

<div class="actions">
	<?php echo CHtml::submitButton('Сохранить', array('class'=>'btn primary')); ?>
	<?php echo CHtml::button('Отмена', array('class'=>'btn default','onclick' => "document.location = '".$this->createUrl('/help/admin/chapter/list', array('article_id' => $article->id))."'"));?>
</div>

<?php $this->endWidget(); ?>
