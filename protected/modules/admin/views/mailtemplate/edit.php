<?php
$this->breadcrumbs=array(
	'Управление почтой' => array('index'),
	'Шаблоны сообщений' => array('index'),
	'Редактирование'
);
?>

<?php if ($template->getIsNewRecord()) : ?>
	<h1>Новый шаблон</h1>
<?php else: ?>
	<h1>Редактирование шаблона #<?php echo CHtml::value($template, 'key');?></h1>
<?php endif; ?>


<?php if(Yii::app()->user->hasFlash('success')):?>
    <div class="flash-success">
	<?php echo Yii::app()->user->getFlash('success'); ?>
    </div>
<?php endif; ?>


<?php echo CHtml::form('', 'post'); ?>
	<?php echo CHtml::errorSummary($template); ?>

	<div class="clearfix">
		<?php echo CHtml::activeLabelEx($template, 'key'); ?>
		<div class="input">
		<?php echo CHtml::activeTextField($template, 'key', array('class'=>'span7','maxlength'=>45)); ?>
		</div>
	</div>
	<div class="clearfix">
		<?php echo CHtml::activeLabelEx($template, 'name'); ?>
		<div class="input">
		<?php echo CHtml::activeTextField($template, 'name', array('class'=>'span7','maxlength'=>255)); ?>
		</div>
	</div>
        <div class="clearfix">
		<?php echo CHtml::activeLabelEx($template, 'author'); ?>
		<div class="input">
		<?php echo CHtml::activeTextField($template, 'author', array('class'=>'span7','maxlength'=>500)); ?>
		</div>
	</div>
	<div class="clearfix">
		<?php echo CHtml::activeLabelEx($template, 'from'); ?>
		<div class="input">
		<?php echo CHtml::activeTextField($template, 'from', array('class'=>'span7','maxlength'=>255)); ?>
		</div>
	</div>
	<div class="clearfix">
		<?php echo CHtml::activeLabelEx($template, 'subject'); ?>
		<div class="input">
		<?php echo CHtml::activeTextField($template, 'subject', array('class'=>'span7','maxlength'=>255)); ?>
		</div>
	</div>
	<div class="clearfix">
		<?php echo CHtml::activeLabelEx($template, 'data'); ?>           
		<div class="input">
		<?php
		$this->widget('application.extensions.tinymce.ETinyMce', array(
			'model' => $template, 
			'attribute' => 'data',
			'options'=>array(
				'theme'=>'advanced',
				'forced_root_block' => false,
				'force_br_newlines' => true,
				'force_p_newlines' => false,
				'width'=>'600px',
				'height'=>'300px',
				'theme_advanced_toolbar_location'=>'top',
				'language'=>'ru',
				'cleanup_on_startup'=> false,
    				'trim_span_elements:'=> false,
    				'verify_html'=> false,
    				'cleanup'=> false,
    				'convert_urls'=> false
			),
		));
		?>
		</div>
	</div>
	<div class="clearfix">
		<?php echo CHtml::activeLabelEx($template, 'keywords'); ?>
		<div class="input">
		<?php echo CHtml::activeTextField($template, 'keywords', array('class'=>'span7','maxlength'=>512)); ?>
		</div>
	</div>


        <?php if(!$template->isNewRecord) $this->widget('application.components.widgets.email.EmailTemplateTest', array('template_key'=>$template->key)); ?>


	<div class="actions">
		<?php echo CHtml::submitButton($template->isNewRecord ? 'Создать' : 'Сохранить', array('class' => 'btn large primary')); ?>
		
		<?php echo CHtml::link('Отменить', array('index'), array('class' => 'btn large'));?>
	</div>
	
<?php echo CHtml::endForm(); ?>