<?php $this->pageTitle = 'Мои ответы — Форум — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>


<?php $this->renderPartial('_pageSettings', array(
	'filter'     => $filter,
	'pagination' => $topicProvider->getPagination(),
	'pageSize'   => $pageSize
)); ?>

<div class="topics_list">
	<?php
	$data = $topicProvider->getData();
	foreach($data as $topic) {
		?>
		<div class="item">
			<div class="item_body">
				<a class="item_head" href="<?php echo $topic->getElementLink();?>"><?php echo $topic->name;?></a>

				<div class="item_info">
					<?php if ($topic->author_id) : ?>
						<a class="author" href="<?php echo $topic->author->getLinkProfile();?>"><?php echo $topic->author->name;?></a>
						<span><?php echo CFormatterEx::formatDateToday($topic->create_time);?></span>
					<?php else: ?>
						<span>Гость</span>
						<span><?php echo CFormatterEx::formatDateToday($topic->create_time);?></span>
					<?php endif; ?>



					<div class="block_item_counters">
						<span class="comments_quant"
						      title="комментарии">
							<a href="<?php echo $topic->getElementLink();?>">
								<i></i><?php
								echo ($topic->count_answer > 0) ? CFormatterEx::formatNumeral($topic->count_answer, array('ответ', 'ответа', 'ответов')) : 'Без ответа';
								?></a>
						</span>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="item_last_comment">
				<?php
				// --- Получаем данные о последнем комменте в теме
				$lastAnswer = ForumAnswer::model()->findByAttributes(
					array('topic_id' => $topic->id),
					array(
						'order' => 'update_time DESC',
						'limit' => 1,
						'condition' => 'author_id = :aid',
						'params' => array(':aid' => Yii::app()->user->id)
					)
				);
				if ($lastAnswer) {
					echo CHtml::tag('span', array(), 'Добавлено: '.CFormatterEx::formatDateToday($lastAnswer->update_time), true);
				}
				?>
			</div>
			<div class="clear"></div>
		</div>

		<?php
	}
	?>
</div>

<div class="page_settings new bottom">

	<div class="pages"><?php // -- ПОСТРАНИЧКА --
		$this->widget('application.components.widgets.CustomPager2', array(
			'pages' => $topicProvider->getPagination()
		));
	?></div>

	<div class="elements_on_page drop_down">
		<span class="exp_current"><?php echo Config::$forumPageSizes[$pageSize]; ?><i></i></span>
		<ul>
			<?php
			foreach (Config::$forumPageSizes as $key => $value) {
				echo CHtml::tag('li', array('data-value' => $key), $value);
			}
			?>
		</ul>
	</div>

	<div class="clear"></div>
</div>