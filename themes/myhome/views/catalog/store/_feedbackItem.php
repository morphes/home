<div class="item" id="comment-<?php echo $data->id; ?>">
    <div class="descript">
        <p class="author">
            <a href="<?php echo $data->author->linkProfile;?>">
                    <?php echo CHtml::image('/'.$data->author->getPreview( Config::$preview['crop_45'] ), '', array('width' => 45, 'height' => 45));?>
                    <?php echo $data->author->name; ?>
            </a>
            <br>
            <span class="date"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy <br> в HH:mm:ss', $data->create_time);?></span>
        </p>
    </div>
    <div class="body">
            <?php $this->widget('application.components.widgets.WStar', array(
                    'selectedStar' => $data->mark,
                    'labels'=>array(1=>'Ужасный магазин', 2=>'Плохой магазин', 3=>'Обычный магазин', 4=>'Хороший магазин', 5=>'Отличный магазин'),
            ));?>

        <div class="comment_part">
            <p><?php echo $data->message; ?></p>
            <span>Развернуть</span>
        </div>

        <?php $answer = StoreFeedback::model()->find('parent_id=:pid', array(':pid'=>$data->id)); ?>

        <?php if ($answer) : ?>
                <div class="answer_part editable" data-commentid="<?php echo $data->id; ?>" data-answerid="<?php echo $answer->id; ?>">
                    <span>Ответ магазина:</span> <span class="text"><?php echo $answer->message; ?></span><div class="admin_controls"><span class="edit"><i></i></span><span class="del"><i></i></span></div>
                </div>
        <?php elseif ($model->isOwner(Yii::app()->user->id)) : ?>
                <div class="answer_part" data-commentid="<?php echo $data->id; ?>">
                    <a href="#">Ответить на отзыв</a>
                </div>
        <?php endif; ?>
    </div>
</div>