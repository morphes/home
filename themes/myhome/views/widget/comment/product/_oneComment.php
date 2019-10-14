<?php
if ( ! isset($comment) || ! $comment instanceof Comment)
	return;
?>

<div class="item">
        <p class="author">
                <a href="<?php echo $comment->author->getLinkProfile();?>">
                        <?php echo CHtml::image('/'.$comment->author->getPreview( Config::$preview['crop_45'] ), '', array('width' => 45, 'height' => 45));?>
                        <?php echo $comment->author->name;?>
                </a>
                <br>
                <span class="date"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy <br> в HH:mm:ss', $comment->create_time);?></span>
        </p>
        <div class="body">
                <p><?php echo nl2br(CHtml::value($comment, 'message'));?></p>
        </div>
</div>