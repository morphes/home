<?php
$this->breadcrumbs=array(
	'Specialist Rate Cities',
);

$this->menu=array(
	array('label'=>'Create SpecialistRateCity','url'=>array('create')),
	array('label'=>'Manage SpecialistRateCity','url'=>array('admin')),
);
?>

<h1>Добавление условий к тарифу <?php echo $rate->name ?></h1>

<form>
<div class="clearfix ">
	<label>Город</label>
	<div class="input">
		<?php
		$this->widget('application.components.widgets.EAutoComplete', array(
			'sourceUrl'	=> '/utility/autocompletecity',
			'options'	=> array(
				'showAnim'	=>'fold',
				'open' => 'js:function(){}',
				'minLength' => 3
			),
			'htmlOptions'	=> array('id'=>'city_id', 'name'=>'Rate[city_id]', 'class' => 'span6'),
			'cssFile' => null,
		));
		?>
	</div>
</div>

<div class="clearfix ">
	<label>Услуга</label>
	<div class="input">
		<?php
		$this->widget('application.components.widgets.EAutoComplete', array(
			'sourceUrl'	=> '/utility/autocompleteservice',
			'options'	=> array(
				'showAnim'	=>'fold',
				'open' => 'js:function(){}',
				'minLength' => 3
			),
			'htmlOptions'	=> array('id'=>'service_id', 'name'=>'Rate[service_id]', 'class' => 'span6'),
			'cssFile' => null,
		));
		?>
	</div>
</div>
	<input value="<?php echo $rate->id?>" type="hidden" name="Rate[rate_id]">

<div class="actions">
	<?php echo CHtml::submitButton('Добавить', array('class' => 'btn primary'));?>
</div>
</form>

<?php
$this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'		=> 'review-grid',
	'updateSelector'=> '#dummy', // Устанавливаем пустым, чтобы без ajax'а была постраничка
	'dataProvider'	=> $dataProvider,
	'selectableRows'=> 2, // Multiple selection
	'columns' => array(
		array(
			'name'=>'city_id',
			'value' => 'City::model()->findByPk($data->city_id) ? City::model()->findByPk($data->city_id)->name:"Не указан"',
		),
		array(
			'name'=>'service_id',
			'value' => 'Service::model()->findByPk($data->service_id) ? Service::model()->findByPk($data->service_id)->name:"Не указан"',
		),
		array(
			'class'=>'CButtonColumn',
			'template'=>'{update} {delete}',
		),


	),
));

?>
