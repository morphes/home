<div class="item">
	<?php
	$link = $comment->getElementLink();
	?>
	<div class="review_type">Комментарий к проекту</div>
	<div class="item_head project_comment">
		<?php echo CHtml::link($comment->getElementName(), $link); ?>
		<span><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy в HH:mm', $comment->create_time); ?></span>
	</div>
	<div class="review_body">
		<div class="item_text">
			<p><?php echo nl2br(CHtml::value($comment, 'message'));?></p>
		</div>
	</div>
</div>