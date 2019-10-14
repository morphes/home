<?php
$this->breadcrumbs=array(
	'Контент'=>array('index'),
	'Список страниц' => array('admin'),
	'Просмотр',
);
?>

<h1><?php echo $model->title; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		array(   
                    'label'=>$model->getAttributeLabel('category_id'),
                    'type'=>'raw',
                    'value'=>$model->category->title,
                ),
		array(   
                    'label'=>$model->getAttributeLabel('sharebox'),
                    'type'=>'raw',
                    'value'=>Content::$shareboxStatus[$model->sharebox],
                ),
		array(   
                    'label'=>$model->getAttributeLabel('author_id'),
                    'type'=>'raw',
                    'value'=>$model->author->login,
                ),
		array(            
                        'name'=>$model->getAttributeLabel('status'),
                        'type'=>'raw',
                        'value' => Content::$statuses[$model->status],
                ),
		array(            
                        'name'=>'URL',
                        'type'=>'raw',
                        'value' => CHtml::link(Yii::app()->homeUrl."/article/".$model->category->alias."/".$model->alias, Yii::app()->homeUrl."/article/".$model->category->alias."/".$model->alias),
                ),
		array(            
                        'name'=>$model->getAttributeLabel('menu_key'),
                        'value' => $model->menu_key,
                ),
		'title',
		'desc',
		array(   
                    'label'=>$model->getAttributeLabel('content'),
                    'type'=>'raw',
                    'value'=> CHtml::decode($model->content),
                ),
		array(   
                    'label'=>$model->getAttributeLabel('create_time'),
                    'type'=>'raw',
                    'value'=>date("d.m.Y", $model->create_time),
                ),
		array(   
                    'label'=>$model->getAttributeLabel('update_time'),
                    'type'=>'raw',
                    'value'=>date("d.m.Y", $model->update_time),
                ),
	),
)); ?>

<div class="actions">
	<?php echo CHtml::button('Редактировать', array('onclick' => 'location = "'.$this->createUrl('update', array('id' => $model->id)).'"', 'class' => 'btn large primary'));?>
</div>

