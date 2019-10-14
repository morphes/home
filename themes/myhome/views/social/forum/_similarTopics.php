<?php
foreach ($topics as $topic) {
	?>
	<div class="item">
		<a class="item_head" href="<?php echo $topic->getElementLink();?>"><?php echo Amputate::getLimb($topic->name, 100);?></a>

		<div class="item_info">
			<?php
			if ($topic->author_id) {
				echo CHtml::link($topic->author->name, $topic->author->getLinkProfile(), array('class' => 'author'));
			} else {
				echo CHtml::tag('span', array(), 'Гость');
			}
			?>

			<span><?php echo CFormatterEx::formatDateToday($topic->create_time);?></span>

			<div class="block_item_counters"> <span class="comments_quant"
								title="комментарии">
		<a href="<?php echo $topic->getElementLink();?>#answer">
			<i></i><?php
			echo ($topic->count_answer > 0) ? CFormatterEx::formatNumeral($topic->count_answer, array('ответ', 'ответа', 'ответов')) : 'Без ответа';
		?></a>
	</span>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	<?php
}
?>
