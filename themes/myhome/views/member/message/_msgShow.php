
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

<div class="item <?php echo $classItemDisabled?>"  id="msg-body-<?php echo $data->id ?>">
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
		<p><?php echo nl2br(CHtml::encode($data->message)); ?></p>

		<?php
	
		if ( ! empty($data->uploadedFiles)) {
			
			echo CHtml::openTag('p', array('class' => 'files-list'));
			echo CHtml::tag('strong', array(), 'Вложенные файлы ('.count($data->uploadedFiles).')', true);
			echo '<br>';
			foreach ($data->uploadedFiles as $file) {
				echo CHtml::link($file->name.'.'.$file->ext, Yii::app()->createUrl('/download/attachfile/', array('id'=>$file->id)));
				$size = round($file->size/1024/1024, 3);
				echo ", &nbsp{$size} Мб<br>";
			}
			echo CHtml::closeTag('p');
		}

		?>
	</div>



	<div class="-col-wrap -inset-left-hf -hidden">
		<a class="-icon-cross-circle-xs -red -border-all -inline -small remove -gutter-bottom-hf" data-id=<?php echo $data->id ?> href="#">Удалить</a>
		<?php
		if(Yii::app()->user->id!=$data->author_id)
		{?>
		<a class= "<?php  echo $classSpam; ?> -red -border-all -inline -small spam" data-id=<?php echo $data->id ?> href="#"><?php echo $spamStatusMessage ?> </a>
		<?php };?>

	</div>


</div>