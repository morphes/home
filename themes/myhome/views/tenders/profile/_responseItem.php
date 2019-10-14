<?php $responseUser = $response->user; 
if (!is_null($responseUser)) :
?>
<div class="tender_comment " id="<?php echo 'r_'.$response->id; ?>" data-value="<?php echo $tender->id; ?>">
	<div class="item_head">
		<?php echo CHtml::image('/'.$responseUser->getPreview( Config::$preview['crop_23'] ), '', array('width'=>23, 'height'=>23)); ?>
		<?php echo CHtml::link($responseUser->name, "/users/{$responseUser->login}/", array('class'=>'post_author_name')); ?>
		<span class="post_date"><?php echo Yii::app()->getDateFormatter()->format('d MMMM в HH:mm', $response->create_time); ?></span>
	</div>
	<div class="tender_comment_text">
		<?php
		if ($isAuthor) {
			echo CHtml::tag('span', array('class'=>'can_edit'), $response->content);
			echo CHtml::tag('textarea', array('class'=>'textInput hide'), $response->content);
		} else {
			echo CHtml::tag('span', array(), $response->content);
		}
		?>
		<?php if ($isAuthor) : ?>
		<div class="price">
			<span>Ориентировочная стоимость</span>
			<p><?php echo $response->cost; ?></p>
		</div>
		<?php endif; ?>
	</div>
	<div class="tender_comment_tel">
		<?php if ($isAuthor) { ?>
		<div class="del_comment">
			<a href="#">Отказаться от участия</a>
		</div>
		<?php } ?>
	</div>
	<div class="clear"></div>
</div>
<?php endif; ?>