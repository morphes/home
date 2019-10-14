<tr class="source-item">
        <td class="clearfix">

                        <?php
                        $this->widget('application.components.widgets.CAjaxAutoComplete', array(
                                'name'		=> 'SourceMultiple['.$sourceMultiple->id.'][source_name]',
                                'sourceUrl'	=> '/content/admin/source/autocomplete',
                                'value'		=> @$sourceMultiple->source_name,
                                'options'	=> array(
                                        'showAnim' => 'fold',
                                        'delay' => 300,
                                        'autoFocus' => true,
                                        'select' => 'js:function(event, ui) {
                                                $("#SourceMultiple_'.$sourceMultiple->id.'_source_url").val(ui.item.url);
                                        }'
                                ),
                                'htmlOptions'	=> array('class' => 'textInput')
                        ));
                        ?>
                        <?php echo CHtml::error($sourceMultiple, "[$sourceMultiple->id]source_name", array('class'=>'help-inline')); ?>
        </td>
        <td class="clearfix <?php $error = $sourceMultiple->getError('source_url'); echo !empty($error) ? ' error' : '';?>">
		<?php echo CHtml::activeTextField($sourceMultiple, "[$sourceMultiple->id]source_url");?>
		<?php echo CHtml::error($sourceMultiple, "[$sourceMultiple->id]source_url", array('class'=>'help-inline'));?>
        </td>
        <td class="clearfix">
                <?php echo CHtml::button('Удалить', array('class'=>'btn danger', 'onclick'=>"removeSource(this,{$sourceMultiple->id});"));?>
        </td>        
</tr>