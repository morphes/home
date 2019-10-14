<div class="full-review" id="comment-<?php echo $data->id; ?>">
	<div class="-col-wrap -inset-right-qr">
		<?php echo CHtml::image('/'.$data->author->getPreview( Config::$preview['crop_25'] ), '', array('class' => '-quad-25', 'width' => 25, 'height' => 25));?>
	</div>
	<div class="-col-wrap -small">
		<span class="-gutter-right"><?php echo $data->author->name; ?></span>
		<span class="-gray"><?php echo CFormatterEx::formatDateToday($data->create_time);?></span>
	</div>

	<div class="-gutter-top -gutter-bottom">
		<?php $this->widget('application.components.widgets.WStarGrid', array(
			'selectedStar'   => $data->mark,
			'itemClass'      => '-icon-star-xs',
			'itemClassEmpty' => '-icon-star-empty-xs',
			'labels'         => array(
				1 => 'Ужасный магазин',
				2 => 'Плохой магазин',
				3 => 'Обычный магазин',
				4 => 'Хороший магазин',
				5 => 'Отличный магазин'
			),
		));?>
	</div>

	<p><?php echo $data->message; ?></p>



	<?php $answer = StoreFeedback::model()->find('parent_id = :pid', array(':pid' => $data->id)); ?>

	<?php if ($answer) { ?>

		<!--<div class="reply -tinygray-box">
			<p class="-gray -em-all">Сообщение удалено. <a href="#" class="-acronym -red">Восстановить</a></p>
		</div>-->

		<div class="reply" data-commentid="<?php echo $data->id; ?>" data-answerid="<?php echo $answer->id; ?>">
			<p class="-gray -em-all">
				<span class="-black">Ответ магазина:</span><?php echo $answer->message; ?>
				<span class="controls">
					<i class="-icon-pencil-xs edit_answer_to_review"></i>
					<i class="-icon-cross-circle-xs delete_answer_to_review"></i>
				</span>
			</p>
			<form class="-hidden">
				<textarea rows="8" class="-gutter-bottom"><?php echo $answer->message;?></textarea>
				<button class="-button -button-skyblue answer_to_review">Сохранить</button>
				<span class="-acronym -gutter-left -gray hideReplyForm">Отмена</span>
			</form>
		</div>

	<?php } elseif ($model->isOwner(Yii::app()->user->id)) { ?>

		<div class="reply" data-commentid="<?php echo $data->id; ?>">
			<span class="-inline -pseudolink -red -em-all toggleReplyForm"><i>Ответить на отзыв</i></span>
			<form class="-hidden">
				<textarea rows="8" class="-gutter-bottom"></textarea>
				<button class="-button -button-skyblue answer_to_review">Сохранить</button>
				<span class="-acronym -gutter-left -gray hideReplyForm">Отмена</span>
			</form>
		</div>

	<?php } ?>


</div>