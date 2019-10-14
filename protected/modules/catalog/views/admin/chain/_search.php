<div class="row">

        <?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
                'action'=>Yii::app()->createUrl($this->route),
                'method'=>'post',
        )); ?>

                <?php echo $form->textFieldRow($model,'id'); ?>
                <?php echo $form->textFieldRow($model,'name'); ?>

                <div class="clearfix">
                        <?php echo CHtml::label($model->getAttributeLabel('user_id'), 'User_id'); ?>
                        <div class="input">
                                <?php
                                $user = User::model()->findByPk($model->user_id);
                                $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                        'name'=>'User_id',
                                        'value'=> !is_null($user) ? $user->name : '',
                                        'sourceUrl'=>'/utility/autocompleteuser',
                                        'options'=>array(
                                                'minLength'=>'1',
                                                'showAnim'=>'fold',
                                                'select'=>'js:function(event, ui) {$("#Chain_user_id").val(ui.item.id).keyup();}',
                                                'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Chain_user_id").val("");}}',
                                        ),
                                ));
                                ?>
                                <?php echo CHtml::activeHiddenField($model, 'user_id'); ?>
                        </div>
                </div>
                <div class="clearfix">
                        <?php echo CHtml::activeLabel($model, 'vendor_id'); ?>
                        <div class="input">
                                <?php
                                $vendor = Yii::app()->db->createCommand()->select('name')->from('cat_vendor')->where('id=:id', array(':id'=>(int)$model->vendor_id))->limit(1)->queryScalar();
                                $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                        'name'=>'Vendor',
                                        'value'=>$vendor,
                                        'sourceUrl'=>'/admin/utility/acVendor',
                                        'options'=>array(
                                                'minLength'=>'1',
                                                'showAnim'=>'fold',
                                                'select'=>'js:function(event, ui) {$("#Chain_vendor_id").val(ui.item.id).keyup();}',
                                                'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Chain_vendor_id").val("");}}',
                                        ),
                                ));
                                ?>
                                <?php echo CHtml::activeHiddenField($model,  "vendor_id");?>
                        </div>
                </div>
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

</div>