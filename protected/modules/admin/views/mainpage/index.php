<?php Yii::app()->clientScript->registerScript('switch-status', "
$('.switch-status').live('click', function(){
        $.ajax({
                url: '".Yii::app()->createUrl($this->module->id.'/'.$this->id.'/switchstatus/')."/unit/'+$(this).attr('unit'),
                type: 'POST',
                async: false,
                success: function(){
                        $.fn.yiiGridView.update('unit-grid');
                }
        });
	return false;
});
");?>

<style>
.switch-status, .group-operations {
        color: #0066CC;
        border-bottom: 1px dotted #0066CC;
        cursor: pointer;
} 
</style>


<div class="well">
	<h4>Главная</h4>
	<ul>
		<?php foreach($units->getData() as $unit):?>
		<li>
			<?php echo CHtml::link($unit->alias, Yii::app()->createUrl("/admin/unit/{$unit->name}"))?>
		</li>
		<?php endforeach;?>
	</ul>
</div>

<div class="span-18">	

<?php $this->widget('ext.bootstrap.widgets.BootGridView', array(
		'dataProvider'=>$units,
		'id'=>'unit-grid',
		'hideHeader' => true,
		'selectableRows'=>0,
		'columns'=>array(
		array(            
			'name'=>'alias',
			'type'=>'raw',
			'value' => 'CHtml::encode($data->alias)',
		),
		array(            
			'name'=>'status',
			'type'=>'raw',
			'value'=>'"<span unit=\"{$data->name}\" class=\"switch-status\">".Unit::$statusLabel[$data->status]."</span>"',
		),
		array(
			'class'=>'CButtonColumn',
			'template'=>'{update}',
			'updateButtonUrl'=>'Yii::app()->createUrl("/admin/unit/{$data->name}")',
		),
		),
	));?>


</div>