<?php
/**
 * @var $model Contractor
 * @var $data array
 */

$this->breadcrumbs=array(
	'Контрагенты'=>array('index'),
	$model->name=>array('view', 'id'=>$model->id),
	'Статистика'
);

if (empty($data)) {
	echo CHtml::tag('h1', array(), 'Товары не найдены');
	return;
}

$totalData = array();
foreach ($data as $item) {
	if (!isset( $totalData[$item['category_id']] ))
		$totalData[$item['category_id']] = array('name'=>$item['name'], 'cnt'=>$item['cnt']);
	else
		$totalData[$item['category_id']]['cnt'] += $item['cnt'];
}

?>
<h1>Товаров по категориям</h1>
<table class="detail-view">
	<tbody>

	<?php
	$total = 0;
	foreach ($totalData as $item) : ?>
	<tr class="odd">
		<th><?php echo $item['name']; ?></th>
		<td><?php echo $item['cnt']; ?></td>
	</tr>
	<?php
		$total += $item['cnt'];
	endforeach;
	?>
	<tr class="odd">
		<th>Всего</th>
		<td><?php echo $total; ?></td>
	</tr>
	</tbody>
</table>


<?php
$lastVendor = 0;
$totalCnt = 0;
$total = 0;
foreach ($data as $item) {
	// new category
	if ($lastVendor != $item['vendor_id']) {
		if ($totalCnt != 0) {
			echo CHtml::openTag('tr', array('class'=>'odd'));
				echo CHtml::tag('th', array(), 'Всего');
				echo CHtml::tag('td', array(), $total);
			echo CHtml::closeTag('tr');

			echo CHtml::closeTag('tbody');
			echo CHtml::closeTag('table');

		}
		$lastVendor = $item['vendor_id'];

		echo CHtml::tag('h1', array(), $item['vendor_name']);
		echo CHtml::openTag('table', array('class'=>'detail-view'));
			echo CHtml::openTag('tbody');
		$total = 0;

	}

	echo CHtml::openTag('tr', array('class'=>'odd'));
		echo CHtml::tag('th', array(), $item['name']);
		echo CHtml::tag('td', array(), $item['cnt']);
	echo CHtml::closeTag('tr');
	$total += $item['cnt'];

	$totalCnt++;
}

echo CHtml::openTag('tr', array('class'=>'odd'));
	echo CHtml::tag('th', array(), 'Всего');
	echo CHtml::tag('td', array(), $total);
echo CHtml::closeTag('tr');

echo CHtml::closeTag('tbody');
echo CHtml::closeTag('table');


?>

