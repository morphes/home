<?php $this->pageTitle = 'Мои темы — Форум — MyHome.ru'?>
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
					<a class="author" href="<?php echo $topic->author->getLinkProfile();?>"><?php echo $topic->author->name;?></a>
					<span><?php echo CFormatterEx::formatDateToday($topic->create_time);?></span>

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
				$lastAnswer = ForumAnswer::model()->findByAttributes(array('topic_id' => $topic->id), array('order' => 'update_time DESC', 'limit' => 1));
				if ($lastAnswer) {
					echo CHtml::tag('span', array(), 'Обновлено: '.CFormatterEx::formatDateToday($lastAnswer->update_time), true);
					if ($lastAnswer->author_id) {
						echo CHtml::link($lastAnswer->author->name, $lastAnswer->author->getLinkProfile());
					} else {
						echo CHtml::tag('span', array('class' => 'name'), 'Гость', true);
					}
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