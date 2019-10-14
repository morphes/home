<?php
$this->breadcrumbs=array(
	'Общий поиск ("Возможно вы искали")'=>array('admin'),
);
?>

<h1>Общий поиск ("Возможно вы искали")</h1>

<?php echo CHtml::button('Добавить', array('onclick'=>'document.location = \''.$this->createUrl($this->id.'/meansCreate').'\'', 'class' => 'btn primary'))?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'search-means-grid',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		'id',
		'name',
		array(
                        'type'=>'url',
                        'value'=>'$data->url'
                ),
		array(
			'class'=>'CButtonColumn',
                        'template'=>'{update}{delete}',
                        'updateButtonUrl'=>'Yii::app()->createUrl("/content/admin/search/meansUpdate", array("id"=>$data->id));',
                        'deleteButtonUrl'=>'Yii::app()->createUrl("/content/admin/search/meansDelete", array("id"=>$data->id));',
		),
	),
)); ?>
