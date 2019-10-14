<?php $cacheKey = 'ContentPreview_' . get_class($data) . '_' . $data->id . '_' . $data->update_time; 
if($this->beginCache($cacheKey, array('duration' => Cache::DURATION_MENU) )) :
?>

<?php 
	$patern="#<[\s]*myhomecut[\s]*>([^<]*)<[\s]*/myhomecut[\s]*>#i";
	
	if (strpos($data->content, '<myhomecut>') !== false)
	{
		$content = substr($data->content, 0, strpos($data->content, '</myhomecut>')).'</myhomecut>';
		$content = preg_replace($patern, "<p class=\"read-more\"><a href='/content/news/view/id/".$data->id."/#myhomecut'> \${1} </a></p>", $content);
		$content = strip_tags($content, '<a><img><br><br/><p>');
	}
	else
		$content = strip_tags($data->content, '<a><img><br><br/><p>');
?>

<div class="news-item">
	<h2>
		<?php echo CHtml::link( $data->title, $this->createUrl('/content/news/view', array('id' => $data->id)) ); ?>
	</h2>
	<p class="date"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy', $data->public_time); ?></p>
	<?php echo CHtml::tag('p', array(), $content); ?>
	<?php $commentText = $data->count_comment > 0 ? CFormatterEx::formatNumeral($data->count_comment) : 'Нет комментариев'; 
		$commentText .= '<i></i>';
	?>
	<p class="news-item-comments">
		<?php echo CHtml::link( $commentText, $this->createUrl('/content/news/view/id/'.$data->id.'/#comment') ); ?>
	</p>
</div>

<?php 
$this->endCache(); 
endif;
?>
