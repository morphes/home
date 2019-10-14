<div class="item" id="msg-body-<?php echo $data->id ?>">
	<div class="author">
		<p>
			<?php
			echo CHtml::openTag('a', array('href' => $data->recipient->getLinkProfile()));
			
			echo CHtml::image('/'.$data->recipient->getPreview( Config::$preview['crop_45'] ), $data->recipient->name, array('width'=>45, 'height'=>45));
			echo CHtml::value($data, 'recipient.name');
			
			echo CHtml::closeTag('a');
			?>
			<br>
			<em><?php echo CHtml::value($data->recipient, 'login');?></em>
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
	</div>
	<a href="#" onclick="msgdelete(<?php echo $data->id ?>); return false;" class="remove" title="Удалить">Удалить</a>
</div>