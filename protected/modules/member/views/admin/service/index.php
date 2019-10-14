<?php
$this->breadcrumbs=array(
	'Услуги'=>array('index'),
	'Управление',
);
/*
Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('service-grid', {
		data: $(this).serialize()
	});
	return false;
});
");*/
?>

<style>
       tr.bolded td {font-size: 14px; font-weight: bold;} 
</style>        

<h1>Управление списком услуг</h1>

<!--
<div style="margin-bottom: 15px;">
        <?php //echo CHtml::link('Расширенный поиск','#',array('class'=>'search-button')); ?>
        <div class="search-form" style="display:none">
        <?php //$this->renderPartial('_search',array('model'=>$model,)); ?>
        </div>
</div> -->

<div>
        <?php echo CHtml::button('Новая услуга', array('class'=>'btn primary', 'onclick'=>'document.location = "/member/admin/service/create"'))?>
</div>


<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'service-grid',
	'dataProvider'=>$dataProvider,
        'rowCssClassExpression'=>'($data->parent_id == 0) ? "bolded" : ""',
	'columns'=>array(
		'id',
                array(
                    'type'=>'raw',
                    'name'=>'name',
                    'value'=>'CHtml::link($data->name, "/member/admin/service/view/id/" . $data->id)',  
                ),
                array(
                    'type'=>'raw',
                    'name'=>'type',
                    'value'=>'(is_array($tmp = Config::getProjectTypesPlain())) ? $tmp[$data->type] : Config::$projectTypes[$data->type]',
                ),
		array(
			'name'=>'popular',
			'type'=>'raw',
			'value'=>'($data->parent_id > 0) ? ( $data->popular ? " <a href=\"\" data-id=\"".$data->id."\" class=\"btn small success popular_change\">&nbsp;да&nbsp;</a> " : "<a href=\"\" data-id=\"".$data->id."\" class=\"btn small popular_change\">нет</a>" ) : ""',
		),
		array(
                    'type'=>'raw',
                    'name'=>'create_time',
                    'value'=>'date("d.m.Y H:i", $data->create_time)',  
                ),
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>

<script type="text/javascript">
	$(function(){
		$('.content').on('click', '.popular_change', function(){
			var $link = $(this);
			var serviceId = $link.data('id');
			$.get(
				'/member/admin/service/changePopular/id/'+serviceId,
				function(response){
					if (response.success) {
						if (response.popular)
							$link.addClass('success ').html('&nbsp;да&nbsp;');
						else
							$link.removeClass('success ').html('нет');
					}
				}, 'json'
			);
			return false;
		});
	})
</script>