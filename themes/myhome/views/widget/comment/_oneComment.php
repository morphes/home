<?php
if ( ! isset($comment) || ! $comment instanceof Comment)
	return;
?>

<div class="item">
        <p class="author his_project">

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
		$class = '';
                if ($showRating && $comment->author_id != $model->author_id) {
                        $this->widget('application.components.widgets.WStar', array(
                                'selectedStar' => Voting::getMark($comment->author->id, $modelId, $comment->model),
                                'addSpanClass' => 'rating-s'
                        ));
                } else {
			$class = 'no_rating';
		}
                ?>
                <p class=" <?php echo $class; ?>">
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