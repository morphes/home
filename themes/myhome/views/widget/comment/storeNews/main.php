
<script>
	$(function(){
		$('#comment-form').submit(function(){
			$('.errors').css('display', 'none');

			btn = $(this).find('button');
			if ( ! btn.hasClass('disabled')) {
				btn.addClass('disabled');
			} else {
				return false;
			}

			$.post(
				'<?php echo $this->createUrl('/member/comment/create/', array('id' => $model->id, 'type' => get_class($model), 'rating'=>$showRating));?>',
				$(this).serialize(),
				function(response){
					if (response.success) {
						$('.comments').append(response.html_comment);
						$('.count_comments').html(response.count_comment);
						$('#Comment_message').val('');
					} else {
						$('.errors').html(response.errors).css('display', 'block');
					}

					btn.removeClass('disabled');
				},
				'json'
			);
			return false;
		});
	});
</script>


<a name="comments"></a>

<h3><span class="-giant"><?php echo $title;?></span><span class="count_comments -gutter-left-hf -normal -huge -small -gray"><?php echo $cntTotal;?></span></h3>

<div class="-grid comments">

	<?php // Выводим все коменты к элементу ?>
	<?php $countComments = count($comments); ?>
	<?php $lastAuthor = 0; ?>
	<?php for ($ind = ($countComments - 1); $ind >= 0; $ind--) { ?>
		<?php $comment = $comments[$ind];?>

		<div class="-col-3">
			<?php
			if(!$comment->guest_id) {
				?>
				<div class="-col-wrap">
					<?php echo  CHtml::image('/'.$comment->author->getPreview( Config::$preview['crop_45'] ), '', array('width' => 45, 'height' => 45, 'class' => '-quad-45'));?>
				</div>
				<div class="-col-wrap -inset-left-hf">
					<a href="<?php echo $comment->author->getLinkProfile();?>"><?php echo $comment->author->name;?></a>
					<span class="-block -gray -small -gutter-top-hf"><?php echo CFormatterEx::formatDateToday($comment->create_time);?></span>
				</div>
				<?php
			} else {
				echo '<div class="-col-wrap -inset-left-hf">';
				echo CHtml::tag('span', array('class' => 'guest'), $comment->author->name);
				echo '</div>';
			}
			?>
		</div>
		<div class="-col-5 -gray">
			<?php echo nl2br(CHtml::value($comment, 'message'));?>
		</div>
		<hr class="-col-8 -dotted -gutter-top -gutter-bottom-dbl">

	<?php } ?>
</div>



<?php if ($isGuest && $guestComment) { ?>

	<div class="-border-all -border-rounded -inset-left -inset-right -inset-top-dbl -inset-bottom-dbl item-comment">
		<?php $form = $this->beginWidget('CActiveForm', array(
			'id'                   => 'comment-form',
			'htmlOptions'          => array('class' => 'shadow_block white padding-18',),
			'enableAjaxValidation' => false,
		)); ?>

		<div class="-inset-all-hf -light-green-bg -push-right -gutter-bottom -block">
			Уважаемый Гость, оставленный вами комментарий
			будет опубликован в течение нескольких часов.
			<a class="-login"
			   href="#">Войдите</a> или <a href="/site/registration">Зарегистрируйтесь</a>,
			чтобы моментально оставлять ответы.
		</div>

		<div class="-col-wrap -text-align-right -inset-right">Комментарий</div>
		<div class="-col-wrap">
			<?php echo $form->textArea($newComment, 'message', array('class' => '-gutter-bottom', 'rows' => '8'));?>
			<button class="-button -button-skyblue" onclick="_gaq.push(['_trackEvent','Comments','Опубликовать']); yaCounter11382007.reachGoal('cmpub'); return true;">Опубликовать</button>
			<span class="hint -gutter-top errors error-title "></span>
		</div>

		<?php $this->endWidget(); ?>
	</div>

<?php } elseif ($isGuest) { ?>

	<div class="-border-all -border-rounded -inset-left -inset-right -inset-top-dbl -inset-bottom-dbl item-comment">
		<form action="#">
			<p class="lc-not">Чтобы оставить комментарий, <a href="#" class="-login">авторизуйтесь</a> или <a href="/site/registration">зарегистрируйтесь</a></p>
		</form>
	</div>

<?php } elseif (Yii::app()->user->model->data->ban_comment == 1) { ?>

	<div class="-border-all -border-rounded -inset-left -inset-right -inset-top-dbl -inset-bottom-dbl item-comment">
		<form action="#">
			<p class="lc-not">
				Вы лишены права оставлять комментарии.
			</p>
		</form>
	</div>

<?php } else { ?>

	<div class="-border-all -border-rounded -inset-left -inset-right -inset-top-dbl -inset-bottom-dbl item-comment">
		<?php $form = $this->beginWidget('CActiveForm', array(
			'id'                   => 'comment-form',
			'htmlOptions'          => array('class' => 'shadow_block white padding-18',),
			'enableAjaxValidation' => false,
		)); ?>
			<div class="-col-wrap -text-align-right -inset-right">Комментарий</div>
			<div class="-col-wrap">
				<?php echo $form->textArea($newComment, 'message', array('class' => '-gutter-bottom', 'rows' => '8'));?>
				<button class="-button -button-skyblue" onclick="_gaq.push(['_trackEvent','Comments','Опубликовать']); yaCounter11382007.reachGoal('cmpub'); return true;">Опубликовать</button>
				<span class="hint -gutter-top errors error-title "></span>
			</div>
		<?php $this->endWidget(); ?>
	</div>

<?php } ?>

