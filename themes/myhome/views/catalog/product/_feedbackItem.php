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
                    'labels'=>array(1=>'Ужасная модель', 2=>'Плохая модель', 3=>'Обычная модель', 4=>'Хорошая модель', 5=>'Отличная модель'),
            ));?>

        <?php if(!empty($data->merits)) : ?>
                <div class="comment_part">
                    <h3>Достоинства</h3>
                    <p><?php echo $data->merits; ?></p>
                    <span>Еще достоинства</span>
                </div>
        <?php endif; ?>

        <?php if(!empty($data->limitations)) : ?>
                <div class="comment_part">
                        <h3>Недостатки</h3>
                        <p><?php echo $data->limitations; ?></p>
                        <span>Еще недостатки</span>
                </div>
        <?php endif; ?>

        <?php if(!empty($data->message)) : ?>
                <div class="comment_part">
                        <h3>В общем</h3>
                        <p><?php echo $data->message; ?></p>
                        <span>Еще</span>
                </div>
        <?php endif; ?>

	<?php
	    /**
	     * @var $answers array Feedback список ответов на отзыв
	     */
	    $answers = Feedback::model()->findAll('parent_id=:pid', array(':pid'=>$data->id));

	    /**
	     * @var $alreadyAnswered boolean При выводе ответов в этот флаг ставится true,
	     * если текущий пользователь уже ответил на отзыв
 	     */
	    $alreadyAnswered = false;
	?>

	<?php // Вывод ответов магазинов ?>
	<?php foreach($answers as $answer) : ?>

	    <?php if ( $answer->user_id === Yii::app()->user->id && $alreadyAnswered === false ) $alreadyAnswered = true; ?>

	    <?php // Дополнительный класс для первого элемента ?>
	    <?php $answer === reset($answers) ? $class = 'first' : $class = ''; ?>

	    <div class="answer_part <?php echo $class; ?> editable" data-commentid="<?php echo $data->id; ?>" data-answerid="<?php echo $answer->id; ?>">
		    <?php echo $this->_generateAnswerLabel($answer->author->name, $data->product_id, $answer->message, $answer->user_id); ?>
		    <?php if ( $answer->user_id == Yii::app()->user->id ) : ?>
		    	<div class="admin_controls"><span class="edit"><i></i></span><span class="del"><i></i></span></div>
		    <?php endif; ?>
	    </div>
	<?php endforeach; ?>

	<?php // Вывод блока для создания ответа ?>
	<?php if ( $data->product->isSeller && !$alreadyAnswered ) : ?>

	    <?php // Дополнительный класс, если блок создания ответа на отзыв идет сразу после отзыва (нет ответов) ?>
	    <?php empty($answers) ? $class = 'first' : $class = ''; ?>

	    <div class="answer_part <?php echo $class?>" data-commentId="<?php echo $data->id; ?>">
		    <a href="#">Ответить на отзыв</a> <span>как представитель магазина</span>
	    </div>
	<?php endif; ?>


    </div>
</div>