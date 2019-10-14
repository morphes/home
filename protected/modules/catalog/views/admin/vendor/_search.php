<?php
/**
 * @var $model Vendor
 */
?>
<div class="row">

        <?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
                'action'=>Yii::app()->createUrl($this->route),
                'method'=>'get',
        )); ?>

                <?php echo $form->textFieldRow($model,'id'); ?>

                <div class="clearfix">
                        <?php echo CHtml::label($model->getAttributeLabel('name'), 'Name'); ?>
                        <div class="input">
                                <?php
                                $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                        'model'=>$model,
                                        'attribute'=>'name',
                                        'sourceUrl'=>'/admin/utility/acVendor',
                                        'options'=>array(
                                                'minLength'=>'1',
                                                'showAnim'=>'fold',
                                        ),
                                ));
                                ?>
                        </div>
                </div>

                <div class="clearfix" id="city">
                        <?php echo CHtml::label($model->getAttributeLabel('country_id'), 'Country_id'); ?>
                        <div class="input">
                                <?php
                                $country = Country::model()->findByPk($model->country_id);
                                $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                        'name'=>'Country_id',
                                        'value'=> !is_null($country) ? $country->name : '',
                                        'sourceUrl'=>'/utility/autocompleteCountry',
                                        'options'=>array(
                                                'minLength'=>'1',
                                                'showAnim'=>'fold',
                                                'select'=>'js:function(event, ui) {$("#Vendor_country_id").val(ui.item.id).keyup();}',
                                                'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Vendor_country_id").val("");}}',
                                        ),
                                ));
                                ?>
                                <?php echo CHtml::activeHiddenField($model, 'country_id'); ?>
                                <?php echo CHtml::error($model, "country_id");?>

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
					'htmlOptions'	=> array('id'=>'contractor', 'name'=>'Vendor[contractor]', 'class' => ''),
					'cssFile' => null,
				));
				?>
			</div>
		</div>

                <div class="actions">
                        <?php echo CHtml::submitButton('Найти', array('class' => 'btn')); ?>
                </div>

        <?php $this->endWidget(); ?>

</div>