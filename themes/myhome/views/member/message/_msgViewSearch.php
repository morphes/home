<?php
// Определяем данные, которые нужно вывести в блоке "Автор"
// и статус сообщения.
if (Yii::app()->user->id == $data->author->id) {
	$people = $data->recipient;
	$status = $data->author_status;
}
else {
	$people = $data->author;
	$status = $data->recipient_status;
}
if ($status == MsgBody::STATUS_DELETE)
	$deleted = true;
else
	$deleted = false;
		
?>

<div class="item" id="msg-body-<?php echo $data->id ?>">
	<div class="author">
		<p>
			<?php
			echo CHtml::openTag('a', array('href' => $people->getLinkProfile()));
			
			echo CHtml::image('/'.$people->getPreview( Config::$preview['crop_45'] ), $people->name, array('width'=>45, 'height'=>45));
			echo CHtml::value($people, 'name');
			
			echo CHtml::closeTag('a');
			?>
			<br>
			<em><?php echo CHtml::value($data, 'author.login');?></em>
		</p>
		<p class="date"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy <br> в HH:mm:ss', $data->create_time);?></p>
	</div>
	<div class="body">
		<p>
			<?php echo CHtml::link(
				Amputate::getLimb(CHtml::encode($data->message), 200),
				$this->createUrl($this->id.'/show', array('id'=>$data->id))
			);?>
		</p>		
		<?php
		// Количество файлов и их общий вес
		if ( ! empty($data->uploadedFiles)) {
			
			echo CHtml::openTag('p', array('class' => 'files'));
			echo 'Вложенные файлы';
			$total_size = 0;
			foreach ($data->uploadedFiles as $file) {
				$total_size += $file->size;
			}
			$total_size = round($total_size/1024/1024, 3);
			echo '&nbsp;('.count($data->uploadedFiles).')';
			echo ', '.$total_size.' Мб';
			echo CHtml::closeTag('p');
		}
		?>
		<?php
		$type = (Yii::app()->user->id == $data->author->id)
			? 'Исходящее'
			: 'Входящее';
		echo CHtml::tag('p', array('class' => 'ml-box'), $type, true);
		?>
	</div>
	<?php if (!$deleted) { ?>
	<a href="#" onclick="msgdelete(<?php echo $data->id ?>); return false;" class="remove" title="Удалить">Удалить</a>
	<?php } ?>
</div>