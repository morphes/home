<style>
        
        #journal #messages .message{color:black;}
        #journal #messages ul li{margin-bottom: 10px;}
</style>        
<div  id="journal">
        <h3>Журнал проекта</h3>
        <div id="messages">
                
                <div class="">
                        <?php echo CHtml::beginForm($this->createUrl('/idea/admin/create/addjournalmessage'));?>

                                <div class="clearfix">
                                        <div><strong>Сообщение</strong></div>
                                        <div class="input" style="margin-left: 0;">
                                                <textarea id="textarea2" class="span4" rows="3" name="journal-message"></textarea>
                                        </div>
                                        <?php echo CHtml::hiddenField('journal-interior-id', $interior->id)?>
                                        <?php echo CHtml::hiddenField('journal-new-message')?>
                                </div>
                                                
                                <div class="clearfix">
                                        <?php echo CHtml::ajaxSubmitButton('Написать', $this->createUrl('/idea/admin/create/addjournalmessage'), array('dataType'=>'json','success'=>'js:function(response){if(response.key == "ok"){$.fn.yiiListView.update("messages-block");$("#journal-new-message").val("#"+response.val);$("#journal-message").val("")} else {alert(response.val)}}'), array('class'=>'btn primary'));?>
                                </div> 
                        
                        <?php echo CHtml::endForm();?>
                </div>                
                
                
                <?php echo CHtml::openTag('ul');?>
                <?php 
                $this->widget('zii.widgets.CListView', array(
                    'dataProvider'=>$journal,
                    'itemView'=>'application.modules.idea.views.admin.create._journalMsg',
                    'afterAjaxUpdate'=>'js:function(id){var new_msg = $("#journal-new-message").val();$(new_msg).animate({ backgroundColor: "yellow" }, 500, "", function(){$(new_msg).animate({ backgroundColor: "#ECECEC" }, 1000)});}',
                    'template'=>'{items}{pager}',
                    'ajaxUrl'=>$this->createUrl('/idea/admin/create/getjournalmessages', array('interior_id'=>$interior->id)),
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