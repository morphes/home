<?php Yii::app()->clientScript->registerScript('select-user', '
        
$("#idea_id").change(function(){
        if (confirm("Заполнить форму пользовательскими данными?")) {
                $.ajax({
                        url: "' . $this->createUrl($this->id.'/ideainfo/') . '",
                        type: "POST",
                        async: false,
                        dataType: "json",
                        data: {ideaId: $(this).val(), typeId: $("#type_id").val()},
                        success: function(data){
				if (data.error){
					alert(data.error);
				} else {
					$("#name").val(data.name);
					$("#image").attr("src", "' . $this->createUrl($this->id."/imageupdate") . '/file_id/"+data.image);
					$("#image").attr("style", "height: 200px;");
				}
                        }
                });
        }	
});
'); ?>

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
                                        <?php echo CHtml::label('Тип идеи', 'type_id')?>
                                        <?php echo CHtml::dropDownList('type_id', Config::INTERIOR, Config::$ideaTypesName); ?>
                                </div>
                                
                                <div class="row">
                                        <?php echo CHtml::label('ID', 'idea_id')?>
                                        <?php echo CHtml::textField('idea_id'); ?>
                                </div>
                        
                                <div class="row">
                                        <?php echo CHtml::label('Название', 'name')?>
                                        <?php echo CHtml::textField('name'); ?>
                                </div>
                        
                                <div class="row">
                                        <?php echo CHtml::label('Статус', 'status')?>
                                        <?php echo CHtml::dropDownList('status', Unit::STATUS_ENABLED, Unit::$statusLabelForIdea); ?>
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