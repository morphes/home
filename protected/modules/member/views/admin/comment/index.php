<?php
$this->breadcrumbs=array(
	'Комментарии'
);
?>

<?php
Yii::app()->clientScript->registerScript('comments', "
	function searchComments(authorId)
	{
		$('#Comment_author_id').val(authorId);
		$('.search-form form').submit();
	}
", CClientScript::POS_HEAD);

Yii::app()->clientScript->registerCss('comment-grid', "
	#comment-grid .author { text-decoration: none; border-bottom: 1px dotted blue; }
");
?>

<?php Yii::app()->clientScript->registerScript('search', "
	$('.search-button').click(function(){
		$('.search-form').toggle();
		return false;
	});
");?>

<h1>Комментарии пользователей</h1>

<div style="margin-bottom: 15px;"><?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?></div>

<div class="search-form" style="display:none">
	
	<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
		'action'=>Yii::app()->createUrl($this->route),
		'method'=>'get',
	)); ?>

	<div class="clearfix">
		<?php echo CHtml::label('ID автора', 'Comment_author_id');?>
		<div class="input">
			<?php echo $form->textField($model,'author_id',array('maxlength'=>255, 'class'=>'span6')); ?>
		</div>
	</div>
	

	<?php echo $form->dropDownListRow($model, 'model', array_merge( array(null => 'Все'), Config::$commentType), array('class' => 'span6') ); ?>

	<?php echo $form->dropDownListRow($model, 'status',array(''=>'Все') + Comment::$statusLabels, array('class' => 'span6') ); ?>

	<div class="actions">
		<?php echo CHtml::submitButton('Найти', array('class' => 'btn')); ?>
	</div>

	<?php $this->endWidget(); ?>
	
</div><!-- search-form -->


		
<?php
$this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'		=> 'comment-grid',
	'ajaxUpdate'	=> 'comment-grid',
	'dataProvider'	=> $dataProvider,
	'selectableRows'=> 1, // Multiple selection
	'rowCssClass'	=> array(), // reset classes for rows
	'itemsCssClass' => 'condensed-table zebra-striped',
	'updateSelector'=> '#dummy',
	'columns' => array(
		array(
			'class' => 'CCheckBoxColumn'
		),
		array(
			'name' => 'id',
			'value' => '$data->id',
		),
		array(
			'name' => 'author',
			'value' => '"<a href=\"#\" class=\"author\" onclick=\"searchComments(".(int)$data->author->id."); return false;\">".$data->author->name." (".$data->author->login.").<br> (".long2ip($data->author_ip).")</a>"',
			'type' => 'raw'
		),
		array(
			'name' => 'model',
			'value' => 'Config::$commentType[ $data->model ]',
			'type' => 'raw'
		),
		array(
			'name' => 'Название элемента',
			'value' => '"<a href=\"".$data->getElementLink()."\" target=\"_blank\">".$data->getElementName()."</a>"',
			'type' => 'raw'

		),
		array(
			'name' => 'Статус',
			'value' => 'Comment::$statusLabels[$data->status]',
			'type' => 'raw'

		),

		array(
			'name'	=> 'Добавлен',
			'value' => 'date("d.m.Y H:i", $data->create_time)',
			'sortable' => true
		),
		array(
			'class' => 'CButtonColumn',
		),
	),
));
?>