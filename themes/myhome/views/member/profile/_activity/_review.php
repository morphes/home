<?php
/**
 * @var $review Review
 */

$htmlOptions = array(
	'data-id' => $review->id,
);
$htmlOptions['class'] = 'item';
$htmlOptions['data-mark'] = 'good';
$htmlOptions['data-recomended'] = 1;


echo CHtml::openTag('div', $htmlOptions);

echo CHtml::openTag('div', array('class'=>'item_head'));
echo CHtml::image('/'.$user->getPreview(User::$preview['crop_23']), '', array('width'=>23, 'height'=>23));
echo CHtml::link($user->name, $user->getLinkProfile());
echo CHtml::tag('span', array(), Yii::app()->getDateFormatter()->format('d MMMM в HH:mm', $review->create_time) );
echo CHtml::closeTag('div');
?>
<div class="review_body">
	<div class="item_text">
		<p><?php echo CHtml::link($review->message, $review->getReviewLink()); ?></p>
	</div>

	<div class="mark">
		<?php /*echo $mark; */?>
	</div>
	<?php
	$answer = $review->getReviewAnswer();
	if (!is_null($answer)) :
		$answerUser = $answer->getAuthor();
	?>
	<div class="item_answer">
		<div class="item_head">
			<?php
			echo CHtml::image('/'.$answerUser->getPreview(User::$preview['crop_23']), '', array('width'=>23, 'height'=>23));
			echo CHtml::link($answerUser->name, $answerUser->getLinkProfile());
			echo CHtml::tag('span', array(), Yii::app()->getDateFormatter()->format('d MMMM в HH:mm', $review->create_time) );
			?>
		</div>
		<div class="review_body" data-id="<?php echo $answer->id; ?>">
			<div class="item_text">
				<i></i>
				<p><?php echo $answer->message; ?></p>
			</div>
			<?php if ( $answer->author_id == Yii::app()->getUser()->getId() ) : ?>
			<div class="item_tools">
				<span class="edit"><i></i><a href="#">Редактировать</a></span>
				<span class="del"><i></i><a href="#">Удалить</a></span>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>
</div>

<?php
echo CHtml::closeTag('div');
?>