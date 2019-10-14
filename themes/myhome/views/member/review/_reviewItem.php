<?php
$htmlOptions = array(
'data-id' => $review->id,
);
if ($review->rating == Review::RATING_RECOMMEND) {
	$htmlOptions['class'] = 'item good';
	$htmlOptions['data-mark'] = 'good';
	$htmlOptions['data-recomended'] = 1;
	$mark = '<i></i><span>Рекомендую!</span>';
} elseif ($review->rating == Review::RATING_PLUS) {
	$htmlOptions['class'] = 'item good';
	$htmlOptions['data-mark'] = 'good';
	$htmlOptions['data-recomended'] = 0;
	$mark = '<span>Хорошо</span>';
} else {
	$htmlOptions['class'] = 'item bad';
	$htmlOptions['data-mark'] = 'bad';
	$htmlOptions['data-recomended'] = 0;
	$mark = '<span>Плохо</span>';
}
echo CHtml::openTag('div', $htmlOptions);

	echo CHtml::openTag('div', array('class'=>'item_head'));
		echo CHtml::image('/'.$author->getPreview(User::$preview['crop_23']), '', array('width'=>23, 'height'=>23));
		echo CHtml::link($author->name, $author->getLinkProfile());
		echo CHtml::tag('span', array(), Yii::app()->getDateFormatter()->format('d MMMM yyyy в HH:mm', $review->create_time) );
	echo CHtml::closeTag('div');
	?>
	<div class="review_body">
		<div class="item_text">
			<p><?php echo $review->message; ?></p>
			<div class="item_tools review">
				<span class="edit"><i></i><a href="#">Редактировать</a></span>
				<span class="del"><i></i><a href="#">Удалить</a></span>
			</div>
		</div>
		<div class="mark">
			<?php echo $mark; ?>
		</div>
	</div>

	<?php
echo CHtml::closeTag('div');
?>