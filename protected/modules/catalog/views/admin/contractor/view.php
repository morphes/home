<?php
/**
 * @var $model Contractor
 */
$this->breadcrumbs=array(
	'Контрагенты'=>array('index'),
	$model->name,
);


?>

<h1><?php echo $model->name; ?></h1>

<?php $this->widget('ext.bootstrap.widgets.BootDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		array(
			'name' => 'status',
			'value' => Contractor::$statusNames[$model->status],
		),
		array(
			'name' => 'worker_id',
			'value' => $model->getWorkerName(),
		),
		'name',
		array(
			'name' => 'site',
			'type' => 'raw',
			'value' => CHtml::link($model->site, $model->site, array('target'=>'_blanck')),
		),
		array(
			'name' => 'create_time',
			'value' => date("d.m.Y H:i:s", $model->create_time),
		),
		array(
			'name' => 'update_time',
			'value' => date("d.m.Y H:i:s", $model->update_time),
		),
		'comment',
		'legal_address',
		'actual_address',
		'inn',
		'kpp',
		'ogrn',
		'current_account',
		array(
			'name' => 'taxation_system',
			'value' => Contractor::$nalogNames[$model->taxation_system],
		),

		'office_phone',
		'office_fax',
		'email',
	),
)); ?>
<?php
/** @var $bank Bank */
$bank = Bank::model()->findByPk($model->bank_id);
if (!empty($bank)) :
	?>
<h1>Банк</h1>
<div class="clearfix">
	<table class="zebra-striped">
		<tbody>
			<tr class="even">
				<th>Название</th>
				<td><?php echo $bank->name; ?></td>
			</tr>
			<tr class="even">
				<th>БИК</th>
				<td><?php echo $bank->bic; ?></td>
			</tr>
			<tr class="even">
				<th>Корр. счет</th>
				<td><?php echo $bank->corr_account; ?></td>
			</tr>
		</tbody>
	</table>

</div>
<?php endif; ?>


<div class="clearfix"></div>
<?php if (!empty($contacts)) : ?>
<h1>Контактные лица</h1>
<?php
/** @var $contact ContractorContact */
foreach ($contacts as $contact) {
	$this->widget('ext.bootstrap.widgets.BootDetailView',array(
		'data'=>$contact,
		'attributes'=>array(
			'name',
			'post',
			'mobile',
			'phone',
			'email',
			array(
				'name' => 'create_time',
				'value' => date("d.m.Y H:i:s", $model->create_time),
			),
			array(
				'name' => 'update_time',
				'value' => date("d.m.Y H:i:s", $model->update_time),
			),
		),
	));
}
endif;
?>
<div class="clearfix"></div>
<?php
$vendors = $model->getVendors();
if (!empty($vendors)) :
?>
	<h1>Производители</h1>
	<div class="clearfix">
		<table class="zebra-striped" id="vendor-container">
			<thead>
			<tr>
				<th class="header">ID</th><th class="header">Название</th><th class="header">Сайт</th>
			</tr>
			</thead>
			<tbody>
				<?php foreach ($vendors as $vendor) : ?>
				<tr class="odd">
					<td><?php echo $vendor->id; ?></td>
					<td><?php echo $vendor->name; ?></td>
					<td><?php echo $vendor->site; ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	</div>
<?php endif; ?>
<div class="clearfix"></div>
<?php
$stores = $model->getStores();
if (!empty($stores)) :
?>
	<h1>Магазины</h1>
	<div class="clearfix">
		<table class="zebra-striped" id="store-container">
			<thead>
			<tr>
				<th class="header">ID</th><th class="header">Название</th><th class="header">Сайт</th>
			</tr>
			</thead>
			<tbody>
				<?php foreach ($model->getStores() as $store) : ?>
				<tr class="odd">
					<td><?php echo $store->id; ?></td>
					<td><?php echo $store->name.' ('.$store->address.')'; ?></td>
					<td><?php echo $store->site; ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	</div>
<?php endif; ?>

<div class="actions">
	<?php echo CHtml::submitButton('Редактировать', array('class'=>'btn primary large', 'onclick'=>'document.location=\'/catalog/admin/contractor/update/id/'.$model->id.'\''  )); ?>
	<?php echo CHtml::button('Удалить', array('class'=>'danger btn large','onclick' => 'if (!confirm("Удалить")) { return false; } else { document.location="/catalog/admin/contractor/delete/id/'.$model->id.'" }')); ?>
	<?php echo CHtml::button('Статистика', array('class'=>'info btn large','onclick' => 'document.location=\'/catalog/admin/contractor/statistic/id/'.$model->id.'\'')); ?>
	<?php echo CHtml::button('Экспорт', array('class'=>'btn large','onclick' => 'exportCsv()')); ?>
</div>
<script type="text/javascript">

function exportCsv(){
	if ( !confirm('Экспортировать?') )
		return false;

	$.ajax({
		url:"<?php echo '/'.$this->module->id.'/'.$this->id.'/export'; ?>",
		data:{"contractor_id":<?php echo $model->id; ?>},
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

}
</script>



