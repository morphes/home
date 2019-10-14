<?php $isExpert = !empty($data->author_id) && ($data->author->expert_type!=User::EXPERT_NONE); ?>
<div class="item <?php if ($i == 0) echo 'first'; if ($isExpert) echo ' expert';?>" id="<?php echo $data->id;?>">
	<div class="item_head">
		<?php
		if ($data->author_id) {
			echo CHtml::image('/'.$data->author->getPreview(Config::$preview['crop_23']), '', array('width' => 23));
			echo CHtml::link($data->author->name, $data->author->getLinkProfile(), array('class' => 'author_name'));
			if ($isExpert) {
				echo CHtml::tag('span', array('class'=>'expert_sign'), ', эксперт');
			}
		} else {
			echo CHtml::image('/'.User::model()->getPreview(Config::$preview['crop_23']), '', array('width' => 23));
			echo CHtml::tag('span', array('class' => 'name'), 'Гость', true);
		}


		echo CHtml::tag('span', array('class' => 'post_date'), Yii::app()->getDateFormatter()->format('d MMMM yyyy в HH:mm', $data->create_time), true);
		?>

		<a class="item_link" href="#<?php echo $data->id;?>">#</a>

		<span class="item_tools">
			<i class="quote"></i>
			<?php if ($data->isAuthor()) : ?>
			<i class="edit"></i><i class="delete"></i>
			<?php endif; ?>
		</span>

		<div class="likes">
			<?php if ( ! $data->isAuthor()) : ?>
				<i class="like <?php if ( ! ForumAnswerLike::canVote($data->id)) echo 'voted';?>"></i>
			<?php endif; ?>

			<?php if ($data->count_like > 0) : ?>
				<span class="good">+<?php echo $data->count_like;?></span>
			<?php else: ?>
				<span>0</span>
			<?php endif;?>
		</div>
	</div>

	<div class="item_body">
		<div class="item_text"><?php echo nl2br($data->answer);?></div>

		<?php
		$files = $data->files;
		if ($files) {
			echo CHtml::openTag('ul', array('class' => 'item_files'));
			foreach($files as $file) {
				echo CHtml::openTag('li', array('data-file' => $file['file_id']));
				echo CHtml::tag('i', array('class' => 'fileicon '.ForumTopic::getClassIcon($file['ext'])), '', true);
				echo CHtml::link(($file['original_name']) ? $file['original_name'] : $file['name'], $file['full_path'], array('target'=>'_blank'));
				echo CHtml::encode(CFormatterEx::formatFileSize($file['size']));
				echo CHtml::closeTag('li');
			}
			echo CHtml::closeTag('ul');
		}
		?>


	</div>
</div>