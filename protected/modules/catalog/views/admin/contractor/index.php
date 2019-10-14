<?php
$this->breadcrumbs=array(
	'Контрагенты',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('contractor-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Контрагенты</h1>

<?php echo CHtml::link('Расширенный поиск','#',array('class'=>'search-button btn')); ?>

<div class="search-form" style="display:none">
	<div class="spacer-18"></div>
	<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<div>
	<?php echo CHtml::button('Добавить контрагента', array('class'=>'primary btn','style'=>'float:right; margin-top: -25px;', 'onclick'=>'document.location = \''.$this->createUrl('/catalog/admin/contractor/create/').'\''))?>
</div>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'contractor-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
		'id',
		'name',
		array(
			'name' => 'site',
			'type' => 'raw',
			'value' => 'CHtml::link($data->site, $data->site, array("target"=>"_blanck"))',
			'sortable' => false,
		),
		array(
			'name'	=> 'create_time',
			'value' => 'date("d.m.Y H:i:s", $data->create_time)',
			'sortable' => true
		),
		array(
			'name'=>'status',
			'value'=>'Contractor::$statusNames[$data->status]',
			'sortable'=>true,
		),
		'email',
		array(
			'name' => 'Товаров',
			'value' => '$data->getProductCount()',
			'sortable' => false,
		),
		array(
			'htmlOptions' => array(
				'width' => '125px'
			),
			'buttons'=>array(
				'stat' => array(
					'label'=>'Статистика',
					'url'=>'Yii::app()->getController()->createUrl("/catalog/admin/contractor/statistic", array("id" =>$data->id))',
					'options'=>array('target'=>'_blanck'),
					'imageUrl'=>'/img/admin/small/statistics.png'
				),
				'csv' => array(
					'label'=>'CSV',
					'url'=>'Yii::app()->getController()->createUrl("/catalog/admin/contractor/export", array("contractor_id" =>$data->id))',
					'options'=>array('onclick'=>'return exportCsv(this);'),
					'imageUrl'=>'/img/admin/small/download.png'
				),
			),
			'class'=>'CButtonColumn',
			'template'=>'{stat} {csv} {view} {update} {delete}',

		),
	),
)); ?>

<script type="text/javascript">

	function exportCsv(self){
		if ( !confirm('Экспортировать?') )
			return false;

		var url = self.href;

		$.ajax({
			url:url,
			dataType:"json",
			type: "get",
			async:false,
			success: function(response){
				if (response.success) {
					document.location=response.redirectUrl;
				}
			},
			error: function(error){
				if (error.responseText)
					alert(error.responseText);
				else
					alert(error);
			}
		});
		return false;

	}
</script>
