<?php
Yii::app()->clientScript->registerScript('comments',"
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
				'".$this->createUrl('/member/comment/create/', array('id' => $model->id, 'type' => get_class($model)))."',
				$(this).serialize(),
				function(response){
					if (response.success) {
				        	$('#comments-container').append(response.html_comment);
						$('#comments-qt').html(CCommon.formatNumeral(response.count_comment, ['комментарий', 'комментария', 'комментариев']));
						$('.field_mess').val('');
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
");
?>


<div class="buyers_opinion_header">
        <span id="comments-qt"><?php echo CFormatterEx::formatNumeral($cntTotal)?></span>

        <span class="add_comment"><i></i><a href="#product_comment_form">Написать свой комментарий</a></span>
</div>

<div id="discuss_page">
        <div class="comments" id="comments-container">

                <?php $countComments = count($comments); ?>
                <?php $lastAuthor = 0; ?>
                <?php for ($ind = ($countComments - 1); $ind >= 0; $ind--) : ?>
                        <?php $comment = $comments[$ind];?>
                        <div class="item <?php echo ($ind == $countComments - 1) ? 'first' : ''; ?>">
                                <p class="author">
                                        <a href="<?php echo $comment->author->getLinkProfile();?>">
                                                <?php echo CHtml::image('/'.$comment->author->getPreview( Config::$preview['crop_45'] ), '', array('width' => 45, 'height' => 45));?>
                                                <?php echo $comment->author->name;?>
                                        </a>
                                        <br>
                                        <span class="date"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy <br> в HH:mm:ss', $comment->create_time);?></span>
                                </p>
                                <div class="body">
                                        <p>
                                                <?php echo nl2br(CHtml::value($comment, 'message'));?>
                                        </p>
                                </div>
                        </div>
                        <?php $lastAuthor = $comment->author_id; ?>
                <?php endfor; ?>

        </div>
</div>
<div class="spacer-30"></div>


<div class="comments" id="product_comment_form" >
        <?php if (Yii::app()->user->isGuest) : ?>

                <form action="#">
                        <p class="lc-not">Чтобы оставить комментарий, <a href="#" class="-login">авторизуйтесь</a> или <a href="/site/registration">зарегистрируйтесь</a></p>
                </form>

        <?php elseif (Yii::app()->user->model->data->ban_comment == 1) : ?>

                <form action="#">
                        <p class="lc-not">
                                Вы лишены права оставлять комментарии.
                        </p>
                </form>

        <?php else : ?>

                <?php $form=$this->beginWidget('CActiveForm', array(
                        'id'=>'comment-form',
                        'htmlOptions'=>array('class'=>'shadow_block white padding-18',),
                        'enableAjaxValidation'=>false,
                )); ?>

                        <div class="form_top"></div>
                        <p>
                                <label for="lc-message">Комментарий</label>
                                <?php echo $form->textArea($newComment, 'message', array('class' => 'textInput field_mess', 'id'=>'lc-message'));?>
                        </p>
                        <p class="submit">
                                <input type="submit" class="btn_grey" value="Опубликовать" onclick="_gaq.push(['_trackEvent','Comments','Опубликовать']); yaCounter11382007.reachGoal('cmpub'); return true;">
                                <span class="hint errors error-title"></span>
                        </p>
                <?php $this->endWidget(); ?>
        <?php endif; ?>
</div>