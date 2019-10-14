<?php
/**
 * @var $model Report
 */

$this->breadcrumbs=array(
	Report::$typeNames[$model->type_id]
);
$this->pageTitle = Report::$typeNames[$model->type_id];

Yii::app()->getClientScript()->registerScriptFile('/js/admin/CReport.js');
?>
<?php

/** @var $form BootActiveForm */
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
	'id'=>'report',
)); ?>

<h1>Отчеты</h1>
<?php if ( in_array($model->type_id, array(Report::TYPE_CONSOLIDATE, Report::TYPE_CITY, Report::TYPE_STORE, Report::TYPE_VENDOR, Report::TYPE_CONTRACTOR)) ) : ?>
<div class="clearfix">
	<?php echo CHtml::label('Период отчета от:', 'start_time')?>
	<div class="input">
		<?php
		$this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'htmlOptions' => array('name'=>'', 'id'=>'date1', 'class'=>'span5', 'name'=>'start_time'),
			'value'	=> '',
			'options' => array(
				'autoLanguage' => false,
				'dateFormat' => 'dd.mm.yy',
				'timeFormat' => 'hh:mm',
				'changeMonth' => true,
				'changeYear' => true,
			),
		));
		?>
	</div>
</div>

<div class="clearfix">
	<?php echo CHtml::label('Период отчета до:', 'end_time')?>
	<div class="input">
		<?php
		$this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'value'	=> '',
			'htmlOptions' => array('name'=>'', 'id'=>'date2', 'class'=>'span5', 'name'=>'end_time'),
			'options' => array(
				'autoLanguage' => false,
				'dateFormat' => 'dd.mm.yy',
				'timeFormat' => 'hh:mm',
				'changeMonth' => true,
				'changeYear' => true,
			),
		));
		?>
	</div>
</div>
<div class="clearfix span9">
<?php
	$this->widget('ext.NestedDynaTree.NestedDynaTree', array(
		//the class name of the model.
		'modelClass' => 'Category',
		// action taken on click on item. (default empty)
		'ajaxController' => '/admin/report/',
		'clickAction' => "/Cathegories/update/",
		//if given, AJAX load a result of clickAction to the container (default empty)
		'clickAjaxLoadContainer' => 'content',
		//can insert, delete and ( if enabled)drag&drop (default true)
		'manipulationEnabled' => false,
		//can sort items by drag&drop (default true)
		'dndEnabled' => false,
		'options' => array(
			'checkbox'=>true, 
			'persist' => false, 
			'selectMode'=>3,
			'debugLevel'=>0,
			'onSelect' => 'js:function(flag, node){
				var nodes = node.tree.getSelectedNodes();
				var nodeList={};
				var cnt=0;
				for(i in nodes){
					var item=nodes[i].data;
					if (!item.isFolder) {
					 	nodeList["Category["+cnt+"]"]=item.id;
					 	cnt++;
					}
				}
				report.setNodeList(nodeList);
			}',
		),

	));
?>
</div>
<?php endif; ?>

<?php if ( in_array($model->type_id, array(Report::TYPE_STORE, Report::TYPE_CONSOLIDATE, Report::TYPE_CITY, Report::TYPE_SPECIALIST)) ) : ?>
<div class="clearfix">
	<label>Города</label>
	<div class="input">
		<?php echo CHtml::dropDownList('city_type', '', Report::$citiesSelect, array('id'=>'cities')); ?>
		<div class="hide select_block offset2">
			<ul id="city_list">

			</ul>
			<?php
			$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'		=> '',
				'sourceUrl'	=> '/utility/autocompletecity',
				'options'	=> array(
					'select'=>'js:function(event, ui) {
						report.citySelect(ui);
					}',
					'minLength'=>3,
				),
				'htmlOptions'	=> array('id'=>'cities_autocomplete'),
			));
			?>
		</div>
	</div>
</div>
<?php endif; ?>

<?php if ( in_array($model->type_id, array(Report::TYPE_STORE, Report::TYPE_VENDOR)) ) : ?>
<div class="clearfix">
		<label>Производитель</label>
		<div class="input">
			<?php
			$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'		=> '',
				'sourceUrl'	=> '/admin/utility/acvendor',
				'options'	=> array(
					'select'=>'js:function(event, ui) {
						report.vendorSelect(ui);
					}',
					'minLength'=>3,
				),
				'htmlOptions'	=> array('id'=>'vendor_autocomplete'),
			));
			?>
			<ul id="vendor_list">

			</ul>
		</div>
</div>
<?php endif; ?>

<?php if ($model->type_id == Report::TYPE_STORE) : ?>
<div class="clearfix">
	<label>Ключевое слово в названии магазина или адресе</label>
	<div class="input">
		<?php
		$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
			'name'		=> '',
			'sourceUrl'	=> '/admin/utility/acstore',
			'options'	=> array(
				'select'=>'js:function(event, ui) {
						report.storeSelect(ui);
					}',
				'minLength'=>2,
			),
			'htmlOptions'	=> array('id'=>'store_autocomplete'),
		));
		?>
		<ul id="store_list">

		</ul>
	</div>
</div>
<?php endif; ?>

<?php if ($model->type_id == Report::TYPE_CONTRACTOR) : ?>
<div class="clearfix">
	<label>Контрагент</label>
	<div class="input">
		<?php
		$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
			'name'		=> '',
			'sourceUrl'	=> '/admin/utility/accontractor',
			'options'	=> array(
				'select'=>'js:function(event, ui) {
						report.contractorSelect(ui);
					}',
				'minLength'=>2,
			),
			'htmlOptions'	=> array('id'=>'contractor_autocomplete'),
		));
		?>
		<ul id="contractor_list">

		</ul>
	</div>
</div>
<?php endif; ?>

<?php if ($model->type_id == Report::TYPE_SPECIALIST) : ?>
<div class="clearfix">
	<label>Услуги</label>
	<div class="input">
		<?php echo CHtml::dropDownList('service_type', '', Report::$serviceNames, array('id'=>'services')); ?>
		<div class="hide select_block">
			<ul class="level_1">
				<?php

				$services = Service::model()->findAll(array('order' => 'case when parent_id = 0 then id else parent_id end,parent_id', 'limit' => 200));
				$blockNumber = 0;
				foreach ($services as $service) {

					if ( $service->parent_id == 0 ) {
						if ($blockNumber != 0) {
							echo CHtml::closeTag('ul');
							echo CHtml::closeTag('li');
						}
						echo CHtml::openTag('li');
						echo CHtml::checkBox('');
						echo CHtml::link($service->name);
						echo CHtml::openTag('ul', array('class' => 'level_2'));
						$blockNumber++;
						continue;
					}

					if ( $service->parent_id != 0 ) {
						echo CHtml::openTag('li');
						echo CHtml::openTag('label');
						echo CHtml::checkBox('service['.$service->id.']') . $service->name;
						echo CHtml::closeTag('label');
						echo CHtml::closeTag('li');
					}
				}

				if ($blockNumber != 0) {
					echo CHtml::closeTag('ul');
					echo CHtml::closeTag('li');
				}
				?>
			</ul>
		</div>
	</div>
</div>
<div class="clearfix filter_row">
	<label>Критерии</label>
	<ul>
		<?php foreach (Report::$criteriaNames as $key => $name) : ?>
		<li><label><?php echo CHtml::checkBox('criteria['.$key.']').$name;?></label></li>
		<?php endforeach; ?>
	</ul>
</div>

<?php endif; ?>


<?php if ($model->type_id == Report::TYPE_STORE_VIEW) { ?>
	<div class="clearfix">
		<label>Выберите месяц</label>
		<div class="input">
			<?php
			echo CHtml::dropDownList('month', date('j'), Yii::app()->locale->getMonthNames('wide', true), array('class' => 'span2'));
			echo CHtml::hiddenField('year', date('Y'));
			echo date('Y') . ' год';
			?>
		</div>
	</div>
<?php } ?>


<div class="actions">
	<?php echo CHtml::button('Сгенерировать отчет',array('class'=>'btn danger', 'id'=>'generate')); ?>
</div>

<?php $this->endWidget(); ?>


<table id="report-list">
	<thead>
		<tr>
			<th>ID</th>
			<th>Автор</th>
			<th>Тип отчета</th>
			<th>Статус</th>
			<th>Дата создания</th>
			<th>Операции</th>
		</tr>
	</thead>
	<tbody>
	<?php /** @var $report Report */
	foreach ($reports as $report) : ?>
		<?php $class = (in_array($report->status, array(Report::STATUS_NEW, Report::STATUS_PROGRESS))) ? ' for_update' : ''; ?>
		<tr id="report_<?php echo $report->id; ?>" class="<?php echo $class; ?>" data-id="<?php echo $report->id; ?>">
			<td><?php echo $report->id; ?></td>
			<td><?php echo $report->getUserName(); ?></td>
			<td><?php echo Report::$typeNames[$report->type_id]; ?></td>
			<td><?php echo Report::$statusNames[$report->status]; ?></td>
			<td><?php echo date('Y-m-d H:i:s', $report->create_time); ?></td>
			<td><?php
				if ($report->status == Report::STATUS_SUCCESS) {
					echo CHtml::link('Скачать', '/download/reportfile/id/'.$report->id, array('class'=>'btn success report_download'));
				} else {
					echo CHtml::link('Скачать', '#', array('class'=>'btn success report_download disabled'));
				}
				echo '&nbsp';
				if ( !in_array($report->status, array(Report::STATUS_PROGRESS, Report::STATUS_NEW)) ) {
					echo CHtml::link('Удалить', '#', array('class'=>'btn danger report_delete', 'data-id'=>$report->id));
				} else {
					echo CHtml::link('Удалить', '#', array('class'=>'btn danger report_delete disabled', 'data-id'=>$report->id));
				}
			?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<script type="text/javascript">
var report={};
$(document).ready(function(){
	report=new CReport(<?php echo json_encode( array(
		'cityHand'=>Report::CITY_HAND,
		'serviceHand'=>Report::SERVICE_HAND,
		'typeId'=>$model->type_id
	), JSON_NUMERIC_CHECK ); ?>);
<?php if ( in_array($model->type_id, array(Report::TYPE_STORE, Report::TYPE_CONSOLIDATE, Report::TYPE_CITY, Report::TYPE_SPECIALIST)) ) : ?>
	report.cityInit();
<?php endif; ?>

<?php if ( in_array($model->type_id, array(Report::TYPE_STORE, Report::TYPE_VENDOR)) ) : ?>
	report.vendorInit();
<?php endif; ?>

<?php if ($model->type_id==Report::TYPE_STORE) : ?>
	report.storeInit();
<?php endif; ?>

<?php if ($model->type_id==Report::TYPE_CONTRACTOR) : ?>
	report.contractorInit();
<?php endif; ?>

<?php if ($model->type_id==Report::TYPE_SPECIALIST) : ?>
	report.specialistInit();
<?php endif; ?>
});
</script>
<style>
	.hide{
		display:none;
	}
	.level_2{
		display:none;
	}
	.level_2 label {
		float: none;
	}

	.filter_row label {
		float: none;
	}
	.filter_row {
		margin-bottom: 10px;
	}
	.select_block input {
		margin-left: 46px;
	}
</style>