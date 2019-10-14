<?php $this->pageTitle = 'Создание темы — Форум — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>


<div class="forum_topic_side guest">
	<div class="guest_hint">
		Ваше сообщение находится на модерации и будет опубликовано в течение нескольких часов.<br>
		<a href="#" class="-login">Войдите</a> или <a href="/site/registration">Зарегистрируйтесь</a>, чтобы моментально
		публиковать свои сообщения.
	</div>
	<div class="forum_head">
		<h1><?php echo $model->name;?></h1>
	</div>
	<div class="forum_theme">
		<div class="author">
			<img src="/<?php echo User::model()->getPreview(Config::$preview['crop_45']);?>" width="45" height="45" alt="гость">

			<div class="author_info">
				<span class="name">Гость</span>
				<span class="topic_date"><?php echo date('d.m.Y', $model->create_time);?></span>
			</div>
			<div class="del"><i></i><a href="#" data-id="<?php echo $model->id;?>" data-section="<?php echo $model->section_id;?>">Удалить</a></div>

			<div class="topic_text">
				<p><?php echo nl2br($model->description);?></p>

				<?php
				$files = $model->files;
				if ($files) {
					echo CHtml::openTag('ul', array('class' => 'item_files'));
					foreach($files as $file) {
						echo CHtml::openTag('li');
						echo CHtml::tag('i', array('class' => 'fileicon '.ForumTopic::getClassIcon($file['ext'])), '', true);
						echo CHtml::link(($file['original_name']) ? $file['original_name'] : $file['name'], $file['full_path']);
						echo CHtml::encode(CFormatterEx::formatFileSize($file['size']));
						echo CHtml::closeTag('li');
					}
					echo CHtml::closeTag('ul');
				}
				?>
			</div>
			<i></i>
		</div>

	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		forum.delTopicGuest();
	})
</script>