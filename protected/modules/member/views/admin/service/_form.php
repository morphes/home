<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'service-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block">Поля, помеченные <span class="required">*</span> обязательны для заполнения.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->dropDownListRow($model,'parent_id', Service::getParentList() + array('0'=>'Без родителя'), array('class'=>'span5')); ?>

	<?php echo $form->dropDownListRow($model,'type', Config::getProjectTypesPlain(), array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'url',array('class'=>'span5','maxlength'=>50)); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textAreaRow($model,'seo_top_desc',array('class'=>'span7', 'rows' => 10, 'maxlength'=>3000)); ?>
	<?php echo $form->textAreaRow($model,'seo_bottom_desc',array('class'=>'span7', 'rows' => 10, 'maxlength'=>3000)); ?>

	<?php echo $form->textFieldRow($model,'position',array('class'=>'span5')); ?>



        <?php if(!$model->isNewRecord) : ?>
                <?php echo CHtml::hiddenField('service_id', $model->id); ?>
                <div class="clearfix">
                        <label>Синоним услуги</label>
                        <div class="input">
                            <?php echo CHtml::textField('synonym', ''); ?> <?php echo CHtml::button('добавть', array('id'=>'synonym-create'))?>
                            <ul id='synonym-list' style="padding-top: 20px;">
                                <?php foreach($model->synonyms as $synonym) : ?>
                                        <li>
                                            <span class="synonym-text"><?php echo $synonym['synonym']; ?></span>
                                            <input type="button" class="synonym-delete btn" value="удал." synonym_id="<?php echo $synonym['id']; ?>">
                                        </li>
                                <?php endforeach; ?>
                            </ul>
                            <div id="synonym-row-template" style="display: none;">
                                    <li>
                                        <span class="synonym-text"></span>
                                        <input type="button" class="synonym-delete btn" value="удал." synonym_id="">
                                    </li>
                            </div>
                        </div>
                </div>
                <?php Yii::app()->clientScript->registerScript('synonym', '
                        $("#synonym-create").click(function(){
                                $.get("/member/admin/service/createSynonym", {service_id: $("#service_id").val(), synonym: $("#synonym").val()}, function(response){
                                        response = $.parseJSON(response);
                                        if(response.success) {
                                                $("#synonym").val("");
                                                $("#synonym-row-template").find(".synonym-text").text(response.synonym).siblings(".synonym-delete").attr("synonym_id", response.synonym_id);
                                                var row_html = $("#synonym-row-template").html();
                                                $("#synonym-list").append(row_html);
                                        }
                                });
                        });

                        $(".synonym-delete").live("click", function(){
                                var $this = $(this);
                                $.get("/member/admin/service/deleteSynonym", {synonym_id: $this.attr("synonym_id")}, function(response){
                                        response = $.parseJSON(response);
                                        if(response.success) {
                                                $this.parent().remove();
                                        }
                                });
                        });
                ', CClientScript::POS_READY); ?>
        <?php endif; ?>


	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить',array('class'=>'btn primary')); ?>
                <?php echo CHtml::button('Отмена',array('class'=>'btn default','onclick'=>'document.location="/member/admin/service/"')); ?>
	</div>

<?php $this->endWidget(); ?>
