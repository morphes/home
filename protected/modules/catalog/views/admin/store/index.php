<?php
$this->breadcrumbs=array(
        'Магазины'=>array('index'),
        'Управление',
);
Yii::app()->clientScript->registerScript('search', "
$('#search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('#search-form form').submit(function(){
	$.fn.yiiGridView.update('vendor-grid', {
		data: $(this).serialize()
	});
	return false;
});
");

Yii::app()->clientScript->registerCss('check_store', '
	.check_store {
		display: inline-block; width: 18px; height: 18px; background-color: #42B642; border-radius: 50%;
	}
');
?>

<h1>Магазины</h1>

<div>

        <?php /*echo CHtml::beginForm('/catalog/admin/chain/addStores', 'get'); */?><!--
                <?php /*echo CHtml::hiddenField('stores_ids', implode(',', $keys));*/?>
                <?php /*echo CHtml::submitButton('Привязать текущую выборку магазинов к сети', array('class'=>'primary btn','style'=>'float:left;'))*/?>
        --><?php /*echo CHtml::endForm();*/?>

        <?php echo CHtml::button('Новый магазин', array('class'=>'primary btn','style'=>'float:right; margin-left: 10px;', 'onclick'=>'document.location = \''.$this->createUrl('/catalog/admin/store/create/').'\''))?>
</div>

<div style="clear: both;"></div>

<div style="margin-top: 10px;">
        <?php echo CHtml::button('Фильтр', array('class'=>'btn', 'id'=>'search-button', 'style'=>'margin-bottom: 10px;'));?>
</div>

<div class="search-form" style="display:none">
        <?php $this->renderPartial('_search',array(
                'model'=>$model,
                'date_from'=>$date_from,
                'date_to'=>$date_to,
                'category_id' =>$category_id
        )); ?>
</div><!-- search-form -->


<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'           => 'store-grid',
	'dataProvider' => $dataProvider,
	'columns'      => array(
		'id',
                'name',
		array(
			'name' => 'type',
			'value' => 'isset(Store::$types[$data->type]) ? Store::$types[$data->type] : ""',
		),
		array(
			'value' => '$data->city ? $data->city->name : ""',
		),
                'address',
		array(
			'name' => 'tariff_id',
			'value' => 'isset(Store::$tariffs[$data->tariff_id]) ? Store::$tariffs[$data->tariff_id] : "—"'
		),
                array(
                        'name'=> 'create_time',
                        'value'=>'date("d.m.Y", $data->create_time)',
                ),
		array(
			'name'=> 'user_id',
			'value' => '($data->user_id) ? $data->author->name : "N/A"'
		),
		array(
			'name'  => 'Магазин',
			'value' => '($data->admin_id == $data->user_id) ? "<div class=\"check_store\" title=\"Товар добавлен магазином из ЛК\"></div>" : ""',
			'type'  => 'html'

		),
                array(
			'htmlOptions' => array(
				'width' => '80px'
			),
                        'buttons'=>array(
                                'clone' => array(
                                        'label'=>'Клон',
                                        'url'=>'Yii::app()->createUrl("/catalog/admin/store/clone", array("id"=>$data->id))',
                                ),
				'goods' => array(
					'label'=>'Товары',
					'url'=>'Yii::app()->createUrl("/catalog/admin/store/goods", array("id"=>$data->id))',
				),
                                'stat' => array(
                                        'label'=>'Статистика',
					'imageUrl' => '/img/admin/small/statistics.png',
                                        'url'=>'Yii::app()->createUrl("/catalog/admin/store/statistic", array("id"=>$data->id))',
                                ),
                        ),
                        'class'=>'CButtonColumn',
                        'template'=>'{stat} {update} {delete} {clone} {goods}',

                ),
        ),
)); ?>
