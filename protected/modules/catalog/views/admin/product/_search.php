<?php
Yii::app()->clientScript->registerCoreScript('jquery');
$cs = Yii::app()->clientScript;
$cssCoreUrl = $cs->getCoreScriptUrl();
$cs->registerCssFile($cssCoreUrl . '/jui/css/base/jquery-ui.css');
$cs->registerCoreScript('jquery.ui');
?>

<div class="row">

<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>


        <?php echo $form->textFieldRow($model,'id'); ?>
        <?php echo $form->textFieldRow($model,'name'); ?>

        <div class="clearfix">
                <?php echo CHtml::activeLabel($model, 'vendor_id'); ?>
                <div class="input">
                        <?php
                        $vendor = Yii::app()->db->createCommand()->select('name')->from('cat_vendor')->where('id=:id', array(':id'=>(int)$model->vendor_id))->limit(1)->queryScalar();
                        $this->widget('application.components.widgets.CAjaxAutoComplete', array(
                                'name'=>'Vendor',
                                'value'=>$vendor,
                                'sourceUrl'=>'/admin/utility/acVendor',
                                'options'=>array(
                                        'minLength'=>'1',
                                        'showAnim'=>'fold',
                                        'select'=>'js:function(event, ui) {$("#Product_vendor_id").val(ui.item.id).keyup();}',
                                        'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Product_vendor_id").val("");}}',
                                ),
                        ));
                        ?>
                        <?php echo CHtml::activeHiddenField($model,  "vendor_id");?>
                </div>
        </div>

	<div class="clearfix">
		<label><?php echo $model->getAttributeLabel('contractor'); ?></label>
		<div class="input">
			<?php
			$contractor = Contractor::model()->findByPk($model->contractor);
			$this->widget('application.components.widgets.EAutoComplete', array(
				'valueName'	=> is_null($contractor) ? '' : $contractor->name.' ('.$contractor->id.')',
				'sourceUrl'	=> '/admin/utility/accontractor',
				'value'		=> $model->contractor,
				'options'	=> array(
					'showAnim'	=>'fold',
					'open' => 'js:function(){
							//$(".ui-autocomplete").css("width", "168px");
						}'
				),
				'htmlOptions'	=> array('id'=>'contractor', 'name'=>'Product[contractor]', 'class' => ''),
				'cssFile' => null,
			));
			?>
		</div>
	</div>


        <?php echo $form->textFieldRow($model,'barcode'); ?>

        <?php echo $form->dropDownListRow($model, 'status', array(0=>'Не выбран') + Product::$statuses); ?>

	<?php
	$mods = User::getUsersByRoles(array(User::ROLE_ADMIN, User::ROLE_MODERATOR, User::ROLE_FREELANCE_PRODUCT), User::STATUS_ACTIVE, 20);
	echo $form->dropDownListRow($model, 'user_id', array(''=>'Все')+CHtml::listData($mods, 'id', 'name'));
	?>

        <div class="clearfix">
                <?php echo CHtml::label('Добавлен от', 'date_from')?>
                <div class="input">
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                        'name'=>'date_from',
                        'value'	=> $date_from,
                        'language'	=> 'ru',
                        'options'=>array('dateFormat'=>'dd.mm.yy'),
                        'htmlOptions'=>array(
                                'style'=>'width:150px;'
                        ),
                ));?>
                </div>
        </div>



        <div class="clearfix">
                <?php echo CHtml::label('Добавлен до', 'date_to')?>
                <div class="input">
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                        'name'=>'date_to',
                        'value'=> $date_to,
                        'language'	=> 'ru',
                        'options'=>array('dateFormat'=>'dd.mm.yy'),
                        'htmlOptions'=>array(
                                'style'=>'width:150px;'
                        ),
                ));?>
                </div>
        </div>

        <div class="actions">
                <?php echo CHtml::submitButton('Найти', array('class' => 'btn')); ?>
        </div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->