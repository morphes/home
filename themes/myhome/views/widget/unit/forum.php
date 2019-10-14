<?php
Yii::import('application.modules.social.models.ForumTopic');

$totalTopic = ForumTopic::model()->countByAttributes(array(
	'status' => ForumTopic::STATUS_PUBLIC
));

$topics = ForumTopic::model()->findAllByAttributes(
	array(
		'status' => ForumTopic::STATUS_PUBLIC
	),
	array(
		'order' => 'create_time DESC',
		'limit' => '3'
	)
);
?>


<h2 class="main_page_head"><a href="/social/forum">Форум</a></h2>
<span class="headline_counter"><?php echo $totalTopic;?></span>
<div class="alias">
	Благоустраивать дом очень интересно, но еще интереснее это обсуждать! Делитесь своим опытом, советуйтесь со специалистами, задавайте вопросы экспертам MyHome
</div>
<h3 class="main_page_head">Сегодня обсуждают</h3>
<div class="topics_list">
	<?php foreach($topics as $topic):?>
	<div class="item">
		<a class="item_head" href="<?php echo $topic->getElementLink();?>"><?php echo $topic->name;?></a>
		<div class="item_info">
			<?php
			if ($topic->author_id) {
				echo CHtml::link($topic->author->name, $topic->author->getLinkProfile(), array('class' => 'author'));
			} else {
				echo CHtml::tag('span', array(), 'Гость');
			}
			?>
			<span><?php echo CFormatterEx::formatDateToday($topic->create_time);?></span>
			<div class="block_item_counters">
				<span class="comments_quant" title="комментарии">
					<a href="<?php echo $topic->getElementLink();?>">
						<i></i>
						<?php echo CFormatterEx::formatNumeral($topic->count_answer, array('ответ', 'ответа', 'ответов'));?>
					</a>
				</span>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<?php endforeach; ?>
</div>