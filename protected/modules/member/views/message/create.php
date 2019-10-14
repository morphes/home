<?php
Yii::app()->clientScript->registerScript(
   'myHideEffect',
   '$(".flash-success").animate({opacity: 1.0}, 3000).fadeOut("slow");',
   CClientScript::POS_READY
);
?>

<h3>Новое сообщение</h3>

<?php $this->renderPartial('_topMenu', array('current'=>'create')); ?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'msg-body-create-form',
	'enableAjaxValidation'=>false,
        'htmlOptions'=>array('enctype'=>'multipart/form-data'),
)); ?>
        <?php if(Yii::app()->user->hasFlash('msg_success')):?>
            <div class="flash-success">
                <?php echo Yii::app()->user->getFlash('msg_success'); ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
		<?php echo $form->labelEx($body,'recipient_id'); ?>
                <?php echo $form->error($body,'recipient_id'); ?>
                <?php if($rcp){
                                echo $rcp->login;
                                $rcp_id = $rcp->id;
                        } else {
                                $rcp_id = $body->recipient_id;
                                if(!$body->getError('recipient_id')){
                                        $create = '';
                                } else {
                                        $create = "\$(\"#recipient\").autocomplete( \"search\");";
                                }
                                

                                $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                         'name'=>'recipient',
                                         'sourceUrl'=>'/utility/autocompleteuser',
                                         'value'=>@$_POST['recipient'],
                                         'options'=>array(
                                                'showAnim'=>'fold',
                                                'delay'=>10,
                                                'autoFocus'=>true,
                                                'create'=>"js:function(event, ui) {
                                                                                 ".$create."
                                                                                }",
                                                'select'=>"js:function(event, ui) {
                                                                                 \$(\"#MsgBody_recipient_id\").val(ui.item.id);
                                                                                }"
                                         ),
                                 ));
                                
                        }            
                ?>
                <?php echo $form->hiddenField($body, 'recipient_id', array('value'=>$rcp_id,'class'=>'recipient')); ?>
                
        </div>

        <div class="row">
		<?php echo $form->labelEx($body,'message'); ?>
		<?php echo $form->textArea($body,'message'); ?>
		<?php echo $form->error($body,'message'); ?>
	</div>
        
        <div class="row">
		<?php echo $form->labelEx($body,'attach'); ?>
		<?php  $this->widget('CMultiFileUpload',
                  array(
                       'model'=>$body,
                       'attribute' => 'attach',
                       'accept'=> 'jpg|png|bmp|zip',
                       'denied'=>'Данный тип файла запрещен к загрузке', 
                       'max'=>10,
                       'remove'=>'[x]',
                       'duplicate'=>'Уже выбран',

                       )
                );?>
		<?php echo $form->error($body,'attach'); ?>
	</div>
        


	<div class="row buttons">
		<?php echo CHtml::submitButton('Отправить'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->


<script type="text/javascript">    
        $(document).ready(function() {
                $("#recipient").change(function() {
                        if($("#recipient").val() == ''){
                                $(".recipient").val('');
                        }
                });
                <?php 
                        if(isset($_POST['recipient'])){
                                echo "$('#recipient').autocomplete( \"search\" )";
                        }
                ?>
        });
</script>