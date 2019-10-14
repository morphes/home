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


<?php /** @var $form BootActiveForm */
$form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'contractor-form',
	'enableAjaxValidation'=>false,
)); ?>

<?php echo $form->errorSummary($model); ?>

<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->textFieldRow($model,'site',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->dropDownListRow($model, 'worker_id', Contractor::getSalesList()); ?>

<?php echo $form->textAreaRow($model,'comment',array('class'=>'span5','maxlength'=>3000, 'rows'=>5)); ?>

<?php echo $form->dropDownListRow($model,'status', $model->getPublicStatuses(), array('class'=>'span5')); ?>


<h1>Юр. Данные</h1>

<?php echo $form->textFieldRow($model,'legal_person',array('class'=>'span5','maxlength'=>255, 'placeholder'=>'Иванов, ИП')); ?>

<?php echo $form->textFieldRow($model,'legal_address',array('class'=>'span5','maxlength'=>255, 'placeholder'=>'630047')); ?>

<?php echo $form->textFieldRow($model,'actual_address',array('class'=>'span5','maxlength'=>255, 'placeholder'=>'630047')); ?>

<?php echo $form->textFieldRow($model,'inn',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->textFieldRow($model,'kpp',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->textFieldRow($model,'ogrn',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->textFieldRow($model,'current_account',array('class'=>'span5','maxlength'=>255)); ?>

<div class="clearfix">
	<label><?php echo $model->getAttributeLabel('bank_id'); ?></label>
	<div class="input">
		<?php
		$this->widget('application.components.widgets.EAutoComplete', array(
			'valueName'	=> $model->getBankData(),
			'sourceUrl'	=> '/admin/utility/acBank',
			'value'		=> $model->bank_id,
			'options'	=> array(
				'showAnim'	=>'fold',
				'open' => 'js:function(){
						//$(".ui-autocomplete").css("width", "168px");
					}',
				'minLength' => 4
			),
			'htmlOptions'	=> array('id'=>'bank_id', 'name'=>'Contractor[bank_id]', 'class' => 'span12'),
			'cssFile' => null,
		));
		?>
	</div>

</div>



<?php echo $form->dropDownListRow($model, 'taxation_system', Contractor::$nalogNames, array('class'=>'span5')); ?>

<?php echo $form->textFieldRow($model,'office_phone',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->textFieldRow($model,'office_fax',array('class'=>'span5','maxlength'=>255)); ?>

<?php echo $form->textFieldRow($model,'email',array('class'=>'span5','maxlength'=>50)); ?>

<?php  /* Контактные лица */ ?>
<h1>Контактные лица</h1>
<div class="clearfix" id="contacts-container">
	<?php
	foreach ($contacts as $contact) {
		$this->renderPartial('_contactItem', array('model'=>$contact));
	}
	?>
</div>
<?php echo Chtml::button('+ Добавить контактное лицо', array('class'=>'btn primary', 'id'=>'contact-create-button')); ?>
<div class="clearfix"></div>
<?php /* Vendors */ ?>
<h1>Производители</h1>
<div class="clearfix">
	<label>Производитель</label>
	<div class="input">
		<?php
		$this->widget('application.components.widgets.EAutoComplete', array(
			'valueName'	=> '',
			'sourceUrl'	=> '/admin/utility/acVendor',
			'value'		=> 0,
			'options'	=> array(
				'showAnim'	=>'fold',
				'open' => 'js:function(){
						//$(".ui-autocomplete").css("width", "168px");
					}'
			),
			'htmlOptions'	=> array('id'=>'vendor_id', 'name'=>'', 'class' => 'span5'),
			'cssFile' => null,
		));
		?>
		<?php echo CHtml::button('Добавить', array('class'=>'btn default', 'id'=>'add-vendor')); ?>
	</div>

</div>

<div class="clearfix">
	<table class="zebra-striped" id="vendor-container">
		<thead>
			<tr>
				<th class="header">ID</th><th class="header">Название</th><th class="header">Сайт</th><th></th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach ($model->getVendors() as $vendor) {
			$this->renderPartial('_vendorItem', array('vendor'=>$vendor));
		}
		?>
		</tbody>
	</table>

</div>
<?php /* END Vendors */ ?>

<?php /* Stores */ ?>
<h1>Магазины</h1>
<div class="clearfix">
	<label>Магазин</label>
	<div class="input">
		<?php
		$this->widget('application.components.widgets.EAutoComplete', array(
			'valueName'	=> '',
			'sourceUrl'	=> '/admin/utility/acStore',
			'value'		=> 0,
			'options'	=> array(
				'showAnim'	=>'fold',
				'open' => 'js:function(){
						//$(".ui-autocomplete").css("width", "168px");
					}'
			),
			'htmlOptions'	=> array('id'=>'store_id', 'name'=>'', 'class' => 'span5'),
			'cssFile' => null,
		));
		?>
		<?php echo CHtml::button('Добавить', array('class'=>'btn default', 'id'=>'add-store')); ?>
	</div>

</div>

<div class="clearfix">
	<table class="zebra-striped" id="store-container">
		<thead>
		<tr>
			<th class="header">ID</th><th class="header">Название</th><th class="header">Сайт</th><th></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($model->getStores() as $store) {
			$this->renderPartial('_storeItem', array('store'=>$store));
		}
		?>
		</tbody>
	</table>

</div>

<div class="actions">
	<?php echo CHtml::submitButton('Сохранить', array('class'=>'btn primary large')); ?>
	<?php echo CHtml::button('Отмена', array('class'=>'btn default large','onclick' => 'document.location = "/catalog2/admin/contractor/view/id/'.$model->id.'"'));?>
	<?php echo CHtml::button('Удалить', array('class'=>'danger btn','onclick' => 'if (!confirm("Удалить")) { return false; } else { document.location="/catalog2/admin/contractor/delete/id/'.$model->id.'" }')); ?>
</div>

<?php $this->endWidget(); ?>

<script type="text/javascript">
	// Добавление контактного лица
	$('#contact-create-button').click(function(){
		$.ajax({
			url: "<?php echo '/'.$this->module->id.'/'.$this->id.'/getcontact'; ?>",
			data: {'contractor_id':<?php echo $model->id; ?>},
			dataType: "json",
			type: "post",
			async:false,
			success: function(response){
				if (response.success) {
					$('#contacts-container').append(response.data);
				}
				if (response.error) {
					alert(response.error);
				}
			}
		});
		return false;
	});

	/** Remove contact */
	$('#contacts-container').on('click', '.clearfix a.contractor_del', function(){
		var item = $(this);
		if (!confirm("Удалить")) {
			return false;
		} else {
			$.ajax({
				url:"<?php echo '/'.$this->module->id.'/'.$this->id.'/removecontact'; ?>",
				data:{"contact_id":item.data('id'), 'contractor_id':<?php echo $model->id; ?>},
				dataType:"json",
				type: "post",
				async:false,
				success: function(response){
					if (response.success) {
						var parent = item.parent().parent();
						//parent.next().remove();
						parent.remove();
					}
					if (response.error){
						alert(error.message);
					}
				},
				error: function(error){
					alert(error);
				}
			});
		}
		return false;
	});

	/** Append Vendor */
	$('#add-vendor').click(function(){
		var self=this;
		var vendorId = $('#val_vendor_id').val();
		if (vendorId==0)
			return false;
		$.ajax({
			url:"<?php echo '/'.$this->module->id.'/'.$this->id.'/getvendor'; ?>",
			data:{"contractor_id":<?php echo $model->id; ?>, 'vendor_id':vendorId},
			dataType:"json",
			type: "post",
			async:false,
			success: function(response){
				if (response.success) {
					$('#vendor-container tbody').append(response.data);
					$('#val_vendor_id').val(0);
					$('#vendor_id').val('');
				}
				if (response.error){
					alert(error.message);
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
	});

	/** Remove vendor */
	$('#vendor-container').on('click', '.button-column a.delete', function(){
		var item = $(this);
		if (!confirm("Удалить")) {
			return false;
		} else {
			$.ajax({
				url:"<?php echo '/'.$this->module->id.'/'.$this->id.'/removevendor'; ?>",
				data:{"contractor_id":<?php echo $model->id; ?>, 'vendor_id':item.data('id')},
				dataType:"json",
				type: "post",
				async:false,
				success: function(response){
					if (response.success) {
						item.parent().parent().remove();
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
		return false;
	});

	/** Append Store */
	$('#add-store').click(function(){
		var self=this;
		var storeId = $('#val_store_id').val();
		var url = "<?php echo '/'.$this->module->id.'/'.$this->id.'/getstore'; ?>";
		var data = {"contractor_id":<?php echo $model->id; ?>, 'store_id':storeId};
		if (storeId==0)
			return false;



		this.ajax = function(){
			$.ajax({
				url:url,
				data:data,
				dataType:"json",
				type: "post",
				async:false,
				success: function(response){
					if (response.success) {
						$('#store-container tbody').append(response.data);
						$('#val_store_id').val(0);
						$('#store_id').val('');
					} else
					if (response.confirm) {
						if ( confirm(response.message) ) {
							data.confirm = true;
							self.ajax();
						}
					} else
					if (response.error) {
						alert(response.message);
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

		self.ajax();

		return false;
	});

	/** Remove store */
	$('#store-container').on('click', '.button-column a.delete', function(){
		var item = $(this);
		if (!confirm("Удалить")) {
			return false;
		} else {
			$.ajax({
				url:"<?php echo '/'.$this->module->id.'/'.$this->id.'/removestore'; ?>",
				data:{"contractor_id":<?php echo $model->id; ?>, 'store_id':item.data('id')},
				dataType:"json",
				type: "post",
				async:false,
				success: function(response){
					if (response.success) {
						item.parent().parent().remove();
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
		return false;
	});




</script>
