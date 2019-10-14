<?php // Если новое сообщение, добавляем класс
/**
 * @var $data MsgBody
 */
$data->readMessage();
$cls_new = ($data->recipient_status == MsgBody::STATUS_UNREAD) ? 'item-new' : '';


?>

<?php if(isset($data->spam->status))
{
	$spamStatusMessage='Это не спам';
	$classSpam='';
	$classItemDisabled = '-disabled';
}
else
{
	$spamStatusMessage='Это спам';
	$classSpam='-icon-spam-xs';
	$classItemDisabled = '';
}
?>

<div class="item <?php echo $cls_new;?> <?php echo $classItemDisabled?>" id="msg-body-<?php echo $data->id ?>">
	<div class="author">
		<p>
			<?php
			echo CHtml::openTag('a', array('href' => $data->author->getLinkProfile()));
			
			echo CHtml::image('/'.$data->author->getPreview( Config::$preview['crop_45'] ), $data->author->name, array('width'=>45, 'height'=>45));
			echo CHtml::value($data, 'author.name');
			
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

	</div>




	<div class="-col-wrap -inset-left-hf -hidden">
		<a class="-icon-cross-circle-xs -red -border-all -inline -small remove -gutter-bottom-hf" data-id=<?php echo $data->id ?> href="#">Удалить</a>
		<a class= "<?php  echo $classSpam; ?> -red -border-all -inline -small spam" data-id=<?php echo $data->id ?> href="#"><?php echo $spamStatusMessage ?> </a>

	</div>



</div>



