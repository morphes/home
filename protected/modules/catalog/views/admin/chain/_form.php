<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'chain-form',
	'enableAjaxValidation'=>false,
        'htmlOptions'=>array(
                'enctype'=>'multipart/form-data'
        ),
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->fileFieldRow($model,'logo',array('class'=>'span5')); ?>

        <?php if($model->uploadedFile) :?>
        <div class="clearfix">
                <div class="input">
                        <?php echo CHtml::image('/' . $model->uploadedFile->getPreviewName(Config::$preview['crop_150'])); ?>
                </div>
        </div>
        <?php endif; ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

        <div class="clearfix">
            <?php echo CHtml::label('ID администратора', 'admin_id'); ?>
            <div class="input">
                    <?php echo CHtml::activeTextField($model, 'admin_id'); ?>
                    <span id='admin-name'>
                        <?php if($model->admin) echo "{$model->admin->name}, {$model->admin->login}";?>
                    </span>
            </div>
        </div>

	<?php echo $form->textFieldRow($model,'email',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textFieldRow($model,'site',array('class'=>'span5','maxlength'=>255)); ?>

        <div class="clearfix">
                <?php echo CHtml::label($model->getAttributeLabel('city_id'), 'City_id'); ?>
            <div class="input">
                    <?php
                    $city = City::model()->findByPk($model->city_id);
                    $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                            'name'=>'City_id',
                            'value'=> !is_null($city) ? "{$city->name} ({$city->region->name}, {$city->country->name})" : '',
                            'sourceUrl'=>'/utility/autocompletecity',
                            'options'=>array(
                                    'minLength'=>'3',
                                    'showAnim'=>'fold',
                                    'select'=>'js:function(event, ui) {$("#Chain_city_id").val(ui.item.id).keyup();}',
                                    'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Chain_city_id").val("");}}',
                            ),
                    ));
                    ?>
                    <?php echo CHtml::activeHiddenField($model,  "city_id");?>
                    <?php echo CHtml::error($model, "city_id");?>
            </div>
        </div>

	<?php echo $form->textFieldRow($model,'address',array('class'=>'span5','maxlength'=>1000)); ?>

        <?php echo $form->textAreaRow($model,'phone',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textAreaRow($model,'about',array('class'=>'span5','maxlength'=>3000)); ?>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

        <?php if(!$model->isNewRecord) : ?>

                <h3>Магазины сети</h3>

                <?php echo CHtml::hiddenField('chain_id', $model->id); ?>

                <div class="clearfix" style="margin-top: 10px;">
                        <?php echo CHtml::label('ID магазина', 'store_id'); ?>
                        <div class="input">
                                <?php echo CHtml::textField('store_id');?>
                                <?php echo CHtml::button('Добавить магазин в сеть', array('class'=>'btn', 'id'=>'button-add-store')); ?>
                        </div>
                </div>

                <?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
                        'id'=>'stores-grid',
                        'dataProvider'=>$model->getStores(true),
                        'htmlOptions'=>array('style'=>'padding-top:0px;'),
                        'columns'=>array(
                                'id',
                                'name',
                                array(
                                        'class'=>'CButtonColumn',
                                        'template'=>'{update}{delete}',
                                        'updateButtonUrl'=>'"/catalog/admin/store/update/id/".$data->id',
                                        'deleteButtonUrl'=>'"/catalog/admin/chain/deleteStore/chain_id/'.$model->id.'/store_id/".$data->id'
                                ),
                        ),
                )); ?>

                <?php Yii::app()->clientScript->registerScript('store', '
                        $("#button-add-store").click(function(){

                                var sid = $("#store_id").val();
                                var cid = $("#chain_id").val();

                                $.post("/catalog/admin/chain/addStore", {store_id: sid, chain_id: cid}, function(response) {
                                        if(response.success)
                                                $.fn.yiiGridView.update("stores-grid");
                                        else
                                                alert(response.message);
                                }, "json");


                                $("#store_id").val("");
                                $("#store_name").val("");
                        });
                ', CClientScript::POS_READY);?>

        <?php endif; ?>

        <?php if(!$model->isNewRecord) : ?>

                <h3>Производители в магазине</h3>

                <?php echo CHtml::hiddenField('store_id', $model->id); ?>

                <div class="clearfix" style="margin-top: 10px;">
                        <?php echo CHtml::label('Производитель', 'vendor_name'); ?>
                    <div class="input">
                            <?php
                            $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                    'name'=>'vendor_name',
                                    'value'=> '',
                                    'sourceUrl'=>'/admin/utility/acVendor',
                                    'options'=>array(
                                            'minLength'=>'3',
                                            'showAnim'=>'fold',
                                            'select'=>'js:function(event, ui) {$("#vendor_id").val(ui.item.id).keyup();}',
                                            'change'=>'js:function(event, ui) {if(ui.item === null) {$("#vendor_id").val("");}}',
                                    ),
                            ));
                            ?>
                            <?php echo CHtml::hiddenField('vendor_id');?>
                            <?php echo CHtml::button('Добавить', array('class'=>'btn', 'id'=>'button-add-vendor')); ?>
                    </div>
                </div>

                <?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
                        'id'=>'vendors-grid',
                        'dataProvider'=>$model->getVendors(),
                        'htmlOptions'=>array('style'=>'padding-top:0px;'),
                        'columns'=>array(
                                'id',
                                'name',
                                array(
                                        'class'=>'CButtonColumn',
                                        'template'=>'{update}{delete}',
                                        'updateButtonUrl'=>'"/catalog/admin/vendor/update/id/".$data->id',
                                        'deleteButtonUrl'=>'"/catalog/admin/chain/deleteVendor/chain_id/'.$model->id.'/vendor_id/".$data->id',
                                ),
                        ),
                )); ?>

                <?php Yii::app()->clientScript->registerScript('vendor', '

                        $("#button-add-vendor").click(function(){

                                var cid = $("#chain_id").val();
                                var vid = $("#vendor_id").val();

                                $.post("/catalog/admin/chain/addVendor", {chain_id: cid, vendor_id: vid}, function(response) {
                                        if(response.success)
                                                $.fn.yiiGridView.update("vendors-grid");
                                        else
                                                alert(response.message);
                                }, "json");


                                $("#vendor_id").val("");
                                $("#vendor_name").val("");
                        });

                        $("#Chain_admin_id").change(function(){
                                 var $this = $(this);
                                 if($this.val() == "") {
                                        $this.val("");
                                        $("#admin-name").html("");
                                        return false;
                                 }
                                 $.post("/catalog/admin/store/checkAdmin", {admin_id: $this.val()}, function(response) {
                                        if(response.success) {
                                                $("#admin-name").html(response.html);
                                        } else {
                                                $this.val("");
                                                $("#admin-name").html("");
                                                alert(response.message);
                                        }
                                }, "json");
                        });
                ', CClientScript::POS_READY);?>

        <?php endif; ?>

<?php $this->endWidget(); ?>

