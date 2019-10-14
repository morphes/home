<?php
$this->breadcrumbs=array(
	'Контент'=>array('index'),
	'Список страниц',
);
?>

<h1>Список статических страниц</h1>

<?php echo CHtml::link('Новая страница', array('create'), array('class' => 'btn primary'));?>

<?php $this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'=>'content-grid',
	'dataProvider'=>$model->search(),
	'itemsCssClass' => 'condensed-table zebra-striped',
	'columns'=>array(
		'id',
                'title',
		array(            
                        'name'=>'category_id',
                        'type'=>'raw',
                        'value' => '$data->category->title',
                ),
		array(            
                        'name'=>'author_id',
                        'type'=>'raw',
                        'value' => 'Chtml::link($data->author->login, Yii::app()->createUrl("/member/profile/user/", array("id"=>$data->author_id)))',
                ),
		array(            
                        'name'=>'status',
                        'type'=>'raw',
                        'value' => 'Content::$statuses[$data->status]',
                ),
		array(            
                        'name'=>'URL',
                        'type'=>'raw',
                        'value' => 'Chtml::link(Yii::app()->homeUrl."/article/".$data->category->alias."/".$data->alias, Yii::app()->homeUrl."/article/".$data->category->alias."/".$data->alias)',
                ),
		array(            
                        'name'=>'Ключ меню',
                        'value' => '$data->menu_key',
                ),
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
