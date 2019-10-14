<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 27.05.13
 * Time: 16:26
 * To change this template use File | Settings | File Templates.
 */

?>

<div class="clearfix">
	<label>Автор </label>
	<div class="input">
		<?php echo CHtml::textArea('msg', User::model()->findByPk($model->author_id)->login, array('disabled'=>true, 'class'=>'span7', 'style'=>'height:20px;')); ?>
	</div>
</div>

<div class="clearfix">
	<label>Сообщение</label>
	<div class="input">
		<?php echo CHtml::textArea('msg', $model->message, array('disabled'=>true, 'class'=>'span7', 'style'=>'height:100px;')); ?>
	</div>
</div>

<div class="clearfix">
	<label>Дата</label>
	<div class="input">
		<?php echo CHtml::textArea('msg', date("H:i d.m.Y",$model->create_time), array('disabled'=>true, 'class'=>'span7', 'style'=>'height:100px;')); ?>
	</div>
</div>