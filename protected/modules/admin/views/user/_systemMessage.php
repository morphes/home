<style>        
        #coment #messages .message{color:black;}
        #coment #messages ul li{margin-bottom: 10px;}
</style>        
<div  id="coment">
        <h3>Комментарии</h3>
        <div id="messages">
                
                <div class="">
                        <?php echo CHtml::beginForm($this->createUrl('/admin/user/create_message_ajax'));?>

                                <div class="clearfix">
                                        <div class="input" style="margin-left: 0;">
                                                <textarea id="textarea2" class="span4" rows="3" name="message"></textarea>
                                        </div>
                                        <?php echo CHtml::hiddenField('model_id', $user->id)?>
                                </div>
                                                
                                <div class="clearfix">
                                        <?php echo CHtml::ajaxSubmitButton('Написать', $this->createUrl('/admin/user/create_message_ajax'), array('dataType'=>'json','success'=>'js:function(response){if(response.key == "ok"){$.fn.yiiListView.update("messages-block"); $("textarea[name=message]").val("")} else {alert(response.val)}}'), array('class'=>'btn primary'));?>
                                </div> 
                        
                        <?php echo CHtml::endForm();?>
                </div>                
                
                
                <?php echo CHtml::openTag('ul');?>
                <?php 
                $this->widget('zii.widgets.CListView', array(
                    'dataProvider'=>$messages,
                    'itemView'=>'application.modules.admin.views.user._systemMessageItem',
                    'template'=>'{items}{pager}',
                    'id'=>'messages-block',
                    'pager'=>array(
                        'class'=>'CLinkPager',
                        'header'=>'',
                        'firstPageLabel'=>'&lt;&lt;',
                        'prevPageLabel'=>'&lt;',
                        'nextPageLabel'=>'&gt;',	
                        'lastPageLabel'=>'&gt;&gt;',
                    )
                ));
                ?>
                <?php echo CHtml::closeTag('ul');?>
        </div>
</div>