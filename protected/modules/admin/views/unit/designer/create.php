<?php Yii::app()->clientScript->registerScript('select-user', "
        
$('#user_id').change(function(){
        if (confirm('Заполнить форму пользовательскими данными?')) {
                $.ajax({
                        url: '" . $this->createUrl($this->id.'/user_info/') . "',
                        type: 'POST',
                        async: false,
                        dataType: 'json',
                        data: {uid: $(this).val()},
                        success: function(data){
				if (data.error) {
					alert(data.error);
				}else{
					$('#name').val(data.name);
					$('#image').attr('src', '" . $this->createUrl($this->id.'/image_update') . "/file_id/'+data.image);
					$('#image').attr('style', 'height: 200px;');
				}
                        }
                });
        }	
});
"); ?>

<style>
        .switch-status, .group-operations {
                color: #0066CC;
                text-decoration: underline;
                cursor: pointer;
        } 
</style>

<div class="container">
        <div class="span-6 last">
                <div id="sidebar" style="padding-left: 10px; background-color: #ececec; margin-right: 10px;">

                        <?php $this->renderPartial('_sidebar', array('unit'=>$unit, 'settings'=>$settings)); ?>

                </div>
        </div>
        <div class="span-18">	
                <div class="flash-notice" style="width:80%;">
                                Все поля обязательны для заполнения!
                </div>
                <div class="form">
                        
                        
                        <?php echo CHtml::beginForm();?>
                                <?php echo CHtml::hiddenField('file_id', ''); ?>
                                <div class="row">
                                        <?php echo CHtml::label('ID пользователя', 'user_id')?>
                                        <?php echo CHtml::textField('user_id'); ?>
                                </div>
                        
                                <div class="row">
                                        <?php echo CHtml::label('ФИО', 'name')?>
                                        <?php echo CHtml::textField('name'); ?>
                                </div>
                        
                                <div class="row">
                                        <?php echo CHtml::label('Анонс', 'desc')?>
                                        <?php echo CHtml::textArea('desc', '',array('style'=>'width: 300px;')); ?>
                                </div>

				<div class="row">
					<?php echo CHtml::label('Услуга', 'service_id')?>
					<?php echo CHtml::textField('service_id'); ?>
				</div>
                        
                                <div class="row">
                                        <?php echo CHtml::label('Статус', 'status')?>
                                        <?php echo CHtml::dropDownList('status', Unit::STATUS_ENABLED, Unit::$statusLabelForDesigner); ?>
                                </div>
                        
                                <div class="row">
                                        <?php echo CHtml::label('Изображение', 'uploadfile');?>
                                        <iframe id="image" scrolling="no" style="border:none; width: 300px; height: 50px;"></iframe>
                                </div>
                        
                                <div class="row">
                                        <?php echo CHtml::label('Внутренний комментарий', 'system_message')?>
                                        <?php echo CHtml::textArea('system_message', '',array('style'=>'width: 300px;')); ?>
                                </div> 
                        
                                <div class="buttons">
                                        <?php echo CHtml::submitButton('Сохранить');?>
                                        <?php echo CHtml::button('Отмена', array('onclick'=>'document.location = \''.$this->createUrl($this->id.'/index').'\''))?>
                                </div>
                        
                        <?php echo CHtml::endForm();?> 
                </div>
        </div>

</div>