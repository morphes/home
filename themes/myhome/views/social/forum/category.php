<?php $this->pageTitle = $sectionName.' — Форум — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>

<div class="f_middle">

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
					<span class="comments_quant"
					      title="комментарии">
						<a href="<?php echo $topic->getElementLink();?>"><i></i><?php
							echo ($topic->count_answer > 0) ? CFormatterEx::formatNumeral($topic->count_answer, array('ответ', 'ответа', 'ответов')) : 'Без ответа';
						?></a>
					</span>
					</div>
					<div class="clear"></div>
				</div>
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
		<script type="text/javascript">forum.pageSettings();</script>

		<div class="clear"></div>
	</div>
</div>

<div class="f_right">
	<?php /* // Экспертов временно коментим
	<div class="user_pics">
		<h2 class="block_head">Эксперты</h2>

		<span class="all_elements_link">
			<a href="#">125</a>
		</span>

		<ul class="user_photos expert">
			<li><a href="#"><img width=41 height="41" src="/img/tmp/user/user1.jpg"></a>
			</li>
			<li><a href="#"><img width=41 height="41" src="/img/tmp/user/user1.jpg"></a>
			</li>
			<li><a href="#"><img width=41 height="41" src="/img/tmp/user/user1.jpg"></a>
			</li>
			<li><a href="#"><img width=41 height="41" src="/img/tmp/user/user1.jpg"></a>
			</li>
			<li><a href="#"><img width=41 height="41" src="/img/tmp/user/user1.jpg"></a>
			</li>
			<li><a href="#"><img width=41 height="41" src="/img/tmp/user/user1.jpg"></a>
			</li>
			<li><a href="#"><img width=41 height="41" src="/img/tmp/user/user1.jpg"></a>
			</li>
			<li><a href="#"><img width=41 height="41" src="/img/tmp/user/user1.jpg"></a>
			</li>
			<li><a href="#"><img width=41 height="41" src="/img/tmp/user/user1.jpg"></a>
			</li>
		</ul>
		<div class="clear"></div>
	</div>
 	*/ ?>
	<?php
	if ($knowledges) {
		?>
		<div class="articles_block">
			<h2 class="block_head">Статьи</h2>

		<span class="all_elements_link">
			<a href="<?php echo MediaKnowledge::getSectionLink(); ?>">Все</a><span>&rarr;</span>
		</span>

			<div class="user_photos">
				<?php
				foreach ($knowledges as $know) {
					?>
					<div class="block_item">
						<a href="<?php echo $know->getElementLink();?>"><?php echo $know->title;?></a>

						<div class="block_item_info">
							<span class="pub_date"><?php echo CFormatterEx::formatDateToday($know->public_time);?></span>
						</div>
						<div class="clear"></div>
					</div>
					<?php
				}
				?>
			</div>

		</div>
		<?php
	}
	?>
</div>

