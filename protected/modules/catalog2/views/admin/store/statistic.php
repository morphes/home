<?php
$this->breadcrumbs = array(
	'Магазины' => array('index'),
	'Статистика',
);
?>


<h1>Статистика по магазину "<?php echo $model->name; ?>"</h1>

<?php
$total = 0;
foreach ($model->vendors as $vendor) {

	echo '<b>' . $vendor->name . '</b><br>';

	foreach ($categories as $cat) {
		$qt = $vendor->getProductQt($cat->id);
		if ($qt) {
			echo $cat->name . ' - ' . $qt . '<br>';
			$total += $qt;
		}
	}
}
echo '<br><strong>Итого</strong> - ' . $total;

?>

<br>
<br>
<br>

<h1>Статистика просмотров магазина "<?php echo $model->name; ?>"</h1>


<?php $this->renderPartial('_searchStatShop', array(
	'model'    => $model,
	'timeTo'   => $timeTo,
	'timeFrom' => $timeFrom,
)); ?>





<?php
if ($flagFromSearch) {
	$this->widget('ext.bootstrap.widgets.BootGridView', array(
		'id'           => 'stat',
		'dataProvider' => $statStoreModel,
		'columns'      => array(
			array(
				'name'  => 'view',
				'value' => '$data->view',
			),
			array(
				'name'  => 'type',
				'value' => 'StatStore::$typeLabels[$data->type]',
			),

		),
	));
} else {
	$this->widget('ext.bootstrap.widgets.BootGridView', array(
		'id'           => 'stat',
		'dataProvider' => $statStoreModel,
		'columns'      => array(
			array(
				'name'  => 'view',
				'value' => '$data->view',
			),
			array(
				'name'  => 'type',
				'value' => 'StatStore::$typeLabels[$data->type]',
			),

			array(
				'name'  => 'time',
				'value' => 'date("d.m.Y", $data->time)',
			),
		),
	));
}



?>




