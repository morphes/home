<?php $isExpert = !empty($data->author_id) && ($data->author->expert_type!=User::EXPERT_NONE); ?>
<div class="item <?php if ($i == 0) echo 'first'; if ($isExpert) echo ' expert';?>" id="<?php echo $data->id;?>">
	<div class="item_head">
		<?php
		if ($data->author_id) {
			?>
			<img src="/<?php echo $data->author->getPreview(Config::$preview['crop_23']);?>" width="23" height="23"/>
			<a href="<?php echo $data->author->getLinkProfile();?>"><?php echo $data->author->name;?></a>
			<?php
			if ($isExpert) {
				echo CHtml::tag('span', array('class'=>'expert_sign'), ', эксперт');
			}
		} else {
			?>
			<img src="/<?php echo User::model()->getPreview(Config::$preview['crop_23']);?>" width="23" height="23"/>
			<span>Гость</span>
			<?php
		}
		?>

		<span><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy в HH:mm', $data->create_time);?></span>
		<a class="item_link" href="#<?php echo $data->id;?>">#</a>

		<div class="likes">
			<?php if ($data->count_like > 0) : ?>
				<span class="good">+?><?php echo $data->count_like;?></span>
			<?php else: ?>
				<span>0</span>
			<?php endif;?>
		</div>
	</div>
	<div class="item_body">
		<div class="item_text">
			<div class="deleted_message">Сообщение было удалено.</div>
		</div>
	</div>
</div>