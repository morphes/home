
<script>
	$(function(){
		$('#comment-form').submit(function(){
			$('.errors').css('display', 'none');

			btn = $(this).find('input[type=submit]');
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
						$('.comments .spacer-30').before(response.html_comment);
						$('.comments .header h2 span').html('('+response.count_comment+')');
						$('.field_mess').val('');

						$('.rating-leave').parents('p').remove();
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
<div class="comments" id="comments">
    <?php
    if($showDirect) {
    Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_item_under');
    }
    ?>
    <?php
    // Between
    Yii::app()->controller->renderPartial('//widget/google/adsense_728x90_idea_card');
    ?>
    <div class="header">
		<h2>
			<?php
			if ( ! is_null($urlToAllComments))
				echo CHtml::link($title, $urlToAllComments);
			else
				echo $title;
			?>
			<span>(<?php echo $cntTotal;?>)</span>
		</h2>
	</div>


	<?php // Выводим все коменты к элементу ?>
	<?php $countComments = count($comments); ?>
	<?php $lastAuthor = 0; ?>
	<?php for ($ind = ($countComments - 1); $ind >= 0; $ind--) { ?>
		<?php $comment = $comments[$ind];?>


		<div class="item <?php echo ($ind == $countComments - 1) ? 'first' : ''; ?>">
			<?php // his_project
				$showCommentRating = $showRating && ($comment->author_id != $model->author_id);
				$class = !$showCommentRating ? 'author his_project' : 'author';
			?>
			<p class="<?php echo $class; ?>">

				<?php if(!$comment->guest_id){ ?>

				<a href="<?php echo $comment->author->getLinkProfile();?>">
					<?php echo CHtml::image('/'.$comment->author->getPreview( Config::$preview['crop_45'] ), '', array('width' => 45, 'height' => 45));?>
					<?php echo $comment->author->name;?>
				</a>
				<?php }
				else {

				echo CHtml::image('/'.$comment->author->getPreview( Config::$preview['crop_45'] ), '', array('width' => 45, 'height' => 45));

					echo CHtml::tag('span',array('class'=>'guest'),$comment->author->name);
				}
				?>

				<br>

				<span class="date"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy <br> в HH:mm:ss', $comment->create_time);?></span>
			</p>
			<div class="body">
				<?php // Выводим звезды
				if ($showCommentRating) {
					$this->widget('application.components.widgets.WStar', array(
						'selectedStar' => Voting::getMark($comment->author->id, $model->id, get_class($model)),
						'addSpanClass' => 'rating-s'
					));
				}
				?>
				<p>
					<?php echo nl2br(CHtml::value($comment, 'message'));?>
				</p>
			</div>
			<?php
			// отображение кнопки редактирования комментария
			//if (Yii::app()->user->checkAccess(array(User::ROLE_ADMIN, User::ROLE_MODERATOR, User::ROLE_POWERADMIN, User::ROLE_SENIORMODERATOR))) {
			//        echo CHtml::openTag('span', array('class'=>'comment_edit_link'));
			//        echo CHtml::link(CHtml::image('/img/comment_edit.png', 'редактировать',array('title'=>'редактировать комментарий')), Yii::app()->createUrl('/member/admin/comment/update', array('id'=>$comment->id)));
			//        echo CHtml::closeTag('span');
			//}
			?>
		</div>

		<?php $lastAuthor = $comment->author_id; ?>
	<?php } ?>

	<?php if ($isGuest && $guestComment) { ?>
		<div class="spacer-30"></div>
		<div>
			<?php $form = $this->beginWidget('CActiveForm', array(
				'id'                   => 'comment-form',
				'htmlOptions'          => array('class' => 'shadow_block white padding-18',),
				'enableAjaxValidation' => false,
			)); ?>
			<div class="form_top"></div>

			<div class="-inset-all-hf -light-green-bg  -col-wrap -push-right -gutter-bottom">
				Уважаемый Гость, оставленный вами комментарий
				будет опубликован в течение нескольких часов.
				<a class="-login"
				   href="#">Войдите</a> или <a href="/site/registration">Зарегистрируйтесь</a>,
				чтобы моментально оставлять ответы.
			</div>
			<p>
				<label for="lc-message">Комментарий</label>
				<?php echo $form->textArea($newComment, 'message', array('class' => 'textInput field_mess')); ?>
			</p>

			
			<p class="submit">
				<input type="submit"
				       class="btn_grey"
				       value="Опубликовать"
				       onclick="_gaq.push(['_trackEvent','Comments','Опубликовать']); yaCounter11382007.reachGoal('cmpub'); return true;">
				<br><br>
				<span class="hint -gutter-top errors error-title "></span>
			</p>

			<?php $this->endWidget(); ?>
		</div>

	<?php } elseif ($isGuest) { ?>

		<form action="#">
			<p class="lc-not">Чтобы оставить комментарий, <a href="#" class="-login">авторизуйтесь</a> или <a href="/site/registration">зарегистрируйтесь</a></p>
		</form>

	<?php } elseif (Yii::app()->user->model->data->ban_comment == 1) { ?>

		<form action="#">
			<p class="lc-not">
				Вы лишены права оставлять комментарии.
			</p>
		</form>

	<?php } else { ?>


                <div class="spacer-30"></div>
                <div>
                        <?php $form=$this->beginWidget('CActiveForm', array(
                                'id'=>'comment-form',
                                'htmlOptions'=>array('class'=>'shadow_block white padding-18',),
                                'enableAjaxValidation'=>false,
                        )); ?>
                                <div class="form_top"></div>
                                <p>
                                        <label for="lc-message">Комментарий</label>
                                        <?php echo $form->textArea($newComment, 'message', array('class' => 'textInput field_mess'));?>
                                </p>
                                <?php if ( $showRating && !$owner && ! Voting::model()->findByPk(array('author_id' => Yii::app()->user->id, 'model_id' => $model->id, 'model' => get_class($model) )) ) : ?>
                                        <p>
                                                <label>Оценка</label>
                                                <span class="rating-leave"><i></i><i></i><i></i><i></i><i></i>
                                                        <?php echo $form->hiddenField($voting, 'mark');?>
                                                </span>
                                        </p>
                                <?php endif; ?>

                                <p class="submit">
                                        <input type="submit" class="btn_grey" value="Опубликовать" onclick="_gaq.push(['_trackEvent','Comments','Опубликовать']); yaCounter11382007.reachGoal('cmpub'); return true;">
                                        <br><br>
                                        <span class="hint -gutter-top errors error-title"></span>
                                </p>
                        <?php $this->endWidget(); ?>
                </div>

	<?php } ?>

</div>