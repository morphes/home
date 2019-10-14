<tr class="coauthor-item">
        <?php if (!empty($errors)) $coauthor->addErrors($errors); ?>
        <td class="clearfix<?php echo !empty($errors)? ' error' : ''; ?>">
                <?php
                $this->widget('application.components.widgets.CAjaxAutoComplete', array(
                        'name'=>'Coauthor['.$coauthor->id.'][name]',
                        'id'=>'coauthor_'.$coauthor->id,
                        'value' => $coauthor->name,
                        'sourceUrl'=>'/utility/autocompleteuser',
                        'options'=>array(
                        'showAnim'=>'fold',
                        'minLength' => 2,
                        'change'=>'js:function(event, ui){
                                if (ui.item == null) {
                                        $(".coauthor-item [name=\"Coauthor['.$coauthor->id.'][user_id]\"]").val("");
                                        $(".coauthor-item [name=\"Coauthor['.$coauthor->id.'][specialization]\"]").removeAttr("readonly");
                                        $(".coauthor-item [name=\"Coauthor['.$coauthor->id.'][url]\"]").removeAttr("readonly");
                                }
                        }',
                        'select'=>'js:function(event, ui) {
                                $(".coauthor-item [name=\"Coauthor['.$coauthor->id.'][user_id]\"]").val(ui.item.id);
                                $(".coauthor-item [name=\"Coauthor['.$coauthor->id.'][specialization]\"]").attr("readonly", "readonly");
                                $(".coauthor-item [name=\"Coauthor['.$coauthor->id.'][url]\"]").attr("readonly", "readonly");
                        }'
                        ),
                )); 
                ?>
                <?php echo CHtml::activeHiddenField($coauthor, '['.$coauthor->id.']user_id', array('value' => $coauthor->user_id)); ?>
                <?php 
                        echo CHtml::error($coauthor, 'user_id', array('class'=>'help-inline'));
                        echo CHtml::error($coauthor, 'name', array('class'=>'help-inline')); 
                ?>
        </td>
        <td class="clearfix">
                <?php echo CHtml::activeTextField($coauthor, '['.$coauthor->id.']specialization'); ?>
        </td>
        <td class="clearfix">
                <?php echo CHtml::activeTextField($coauthor, '['.$coauthor->id.']url'); ?>
        </td>
        <td class="clearfix">
                <?php echo CHtml::button('Удалить', array('class'=>'btn danger', 'onclick'=>"removeCoauthor(this,{$coauthor->id});"));?>
        </td>        
</tr>