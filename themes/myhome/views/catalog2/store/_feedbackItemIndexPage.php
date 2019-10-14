<div class="item">
    <div class="item_head">
            <?php echo CHtml::image('/'.$data->author->getPreview( Config::$preview['crop_23'] ), '', array('width' => 23, 'height' => 23));?>
            <?php echo CHtml::link($data->author->name, $data->author->linkProfile); ?>
            <span><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy в HH:mm:ss', $data->create_time);?></span>
    </div>
    <?php $this->widget('application.components.widgets.WStar', array(
        'selectedStar' => $data->mark,
        'labels'=>array(1=>'Ужасный магазин', 2=>'Плохой магазин', 3=>'Обычный магазин', 4=>'Хороший магазин', 5=>'Отличный магазин'),
    ));?>
    <div class="review_body">
        <div class="item_text">
            <div class="comment_part">

                <p><?php echo $data->message; ?></p>
                <span>Развернуть</span>
            </div>

            <?php $answer = StoreFeedback::model()->find('parent_id=:pid', array(':pid'=>$data->id)); ?>

            <?php if ($answer) : ?>
                    <div class="answer_part">
                        <span>Ответ магазина:</span> <?php echo $answer->message; ?>
                    </div>
            <?php elseif ($model->isOwner(Yii::app()->user->id)) : ?>
                    <div class="answer_part">
                        <?php echo CHtml::link('Ответить на отзыв', $this->createUrl('/catalog2/store/feedback/', array('id'=>$model->id)) . '#comment-' . $data->id); ?>
                    </div>
            <?php endif; ?>
        </div>
    </div>
</div>
