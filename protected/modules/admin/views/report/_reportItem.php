<?php $class = (in_array($report->status, array(Report::STATUS_NEW, Report::STATUS_PROGRESS))) ? ' for_update' : ''; ?>
<tr id="report_<?php echo $report->id; ?>" class="<?php echo $class; ?>" data-id="<?php echo $report->id; ?>">
	<td><?php echo $report->id; ?></td>
	<td><?php echo $report->getUserName(); ?></td>
	<td><?php echo Report::$typeNames[$report->type_id]; ?></td>
	<td><?php echo Report::$statusNames[$report->status]; ?></td>
	<td><?php echo date('Y-m-d H:i:s', $report->create_time); ?></td>
	<td><?php
		if ($report->status == Report::STATUS_SUCCESS) {
			echo CHtml::link('Скачать', '/download/reportfile/id/'.$report->id, array('class'=>'btn success report_download'));
		} else {
			echo CHtml::link('Скачать', '#', array('class'=>'btn success report_download disabled'));
		}
		echo '&nbsp';
		if ( !in_array($report->status, array(Report::STATUS_PROGRESS, Report::STATUS_NEW)) ) {
			echo CHtml::link('Удалить', '#', array('class'=>'btn danger report_delete', 'data-id'=>$report->id));
		} else {
			echo CHtml::link('Удалить', '#', array('class'=>'btn danger report_delete disabled', 'data-id'=>$report->id));
		}
		?></td>
</tr>