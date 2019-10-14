
<form action="/<?php echo Yii::app()->request->getPathInfo();?>" class="filter_sort" method="get">
	<input type="hidden" name="pagesize" value="<?php echo $pageSize;?>">
	<input type="hidden" name="sorttype" value="<?php echo $filter['sorttype'];?>">
	<input type="hidden" name="sortdirect" value="<?php echo $filter['sortdirect'];?>">
</form>


<div class="page_settings new">
	<div class="sort_elements">
		Сортировать по
		<?php
		// CSS класс, если меняем направление стрелки
		$direct = ($filter['sortdirect'] == ForumTopic::SORT_DIRECT_UP) ? ' asc' : '';

		if ($filter['sorttype'] == ForumTopic::SORT_TYPE_TIME)
		{
			echo CHtml::tag('div', array('class' => 'sort current ' . $direct, 'data-sort-type' => ForumTopic::SORT_TYPE_TIME), '<span>новизне</span><i></i>', true);
			echo CHtml::tag('div', array('class' => 'sort', 'data-sort-type' => ForumTopic::SORT_TYPE_ANSWER), '<span>числу ответов</span>', true);
		}
		else
		{
			echo CHtml::tag('div', array('class' => 'sort', 'data-sort-type' => ForumTopic::SORT_TYPE_TIME), '<span>новизне</span>', true);
			echo CHtml::tag('div', array('class' => 'sort current ' . $direct, 'data-sort-type' => ForumTopic::SORT_TYPE_ANSWER), '<span>числу ответов</span><i></i>', true);
		}
		?>
	</div>
	<div class="pages"><?php // -- ПОСТРАНИЧКА --
		$this->widget('application.components.widgets.CustomPager2', array(
			'pages' => $pagination
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
		forum.sortSettings({
			'sorttype': {
				'time': '<?php echo ForumTopic::SORT_TYPE_TIME;?>',
				'answer': '<?php echo ForumTopic::SORT_TYPE_ANSWER;?>'
			},
			'sortdirect': {
				'up': '<?php echo ForumTopic::SORT_DIRECT_UP;?>',
				'down': '<?php echo ForumTopic::SORT_DIRECT_DOWN;?>'
			}
		});
		forum.pageSettings();
	</script>

	<div class="clear"></div>
</div>