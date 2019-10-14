<?php $this->pageTitle = 'Поиск — Форум — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>


<script type="text/javascript">
	$(function(){
		$('.forum_search input[name=text]').focus();
	});
</script>

<form action="" class="filter_sort" method="get">
	<input type="hidden" name="pagesize" value="<?php echo $pageSize;?>">
	<input type="hidden" name="text" value="<?php echo CHtml::encode($text);?>">
</form>

<div class="page_settings new">
	<span class="search_result">
		<?php
		echo CFormatterEx::formatNumeral($dataProvider->getTotalItemCount(), array('Найдена', 'Найдено', 'Найдено'), true);
		echo '&nbsp;';
		echo CFormatterEx::formatNumeral($dataProvider->getTotalItemCount(), array('тема', 'темы', 'тем'));?>  по запросу «<?php echo $text;?>»
	</span>

	<div class="pages"><?php // -- ПОСТРАНИЧКА --
		$this->widget('application.components.widgets.CustomPager2', array(
			'pages' => $dataProvider->getPagination()
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

	<script type="text/javascript">
		forum.pageSettings();
	</script>

	<div class="clear"></div>
</div>
<div class="topics_list">

	<?php
	$topics = $dataProvider->getData();
	foreach($topics as $topic)
	{
		$name = preg_replace("#($sphinxQuery)#ius", '<span class="search_word">\1</span>', $topic->name);
		$description = preg_replace("#($sphinxQuery)#ius", '<span class="search_word">\1</span>', $topic->description);
		?>
		<div class="item">
			<a class="item_head" href="<?php echo $topic->getElementLink();?>"><?php echo $name;?></a>
			<p><?php echo $description;?></p>
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
					<a href="<?php echo $topic->getElementLink();?>">
						<i></i><?php
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
			'pages' => $dataProvider->getPagination()
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