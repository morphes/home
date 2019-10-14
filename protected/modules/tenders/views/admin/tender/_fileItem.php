<tr class="line" data-value="<?php echo $file->id; ?>">
	<th><?php echo CHtml::link($file->name.'.'.$file->ext, Yii::app()->controller->createUrl('/download/tenderfile/', array('id'=>$file->id))); ?></th>
	<td class="file_description">
		<?php echo CHtml::tag('span', array(), empty($tenderFile->desc) ? 'Добавить описание' : $tenderFile->desc ); ?>
		<?php echo CHtml::textArea('File[desc]['.$file->id.']', $tenderFile->desc, array('class'=>'textInput hide', 'maxlength'=>255)); ?>
	</td>

	<th><?php echo CFormatterEx::formatFileSize($file->size); ?></th>
	<th><?php echo CHtml::tag('span', array('onclick'=>'removeFile(this);'), 'Удалить'); ?></th>
</tr>