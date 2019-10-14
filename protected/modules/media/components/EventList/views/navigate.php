<div class="page_settings new <?php if ($isBottom) echo 'bottom'; ?>">
	<?php /*<div class="page_template view_type_list">
		<a data-value=0 class="list<?php if ($viewType==0) echo ' current'; ?>" href="#"><img src='/img/list.png'/></a>
		<a data-value=1 class="elemets<?php if ($viewType==1) echo ' current'; ?>" href="#"><img src='/img/elements.png'/></a>
	</div>
 	*/ ?>

	<div class="sort_elements">
		Сортировать по
		<div class="sort_date <?php if ($sortType==MediaEvent::SORT_DATE) { echo ($sortDirect==MediaEvent::SORT_DIRECT_ASC) ? 'current asc' : 'current'; } ?>"><span data-value=<?php echo MediaEvent::SORT_DATE; ?>>дате</span><i></i></div>
		<div class="<?php if ($sortType==MediaEvent::SORT_POPULAR) { echo ($sortDirect==MediaEvent::SORT_DIRECT_ASC) ? 'current asc' : 'current'; } ?>"><span data-value=<?php echo MediaEvent::SORT_POPULAR; ?>>популярности</span><i></i></div>
	</div>

	<div class="pages"><?php
		$this->widget('application.components.widgets.CustomPager2', array(
			'pages' => $pagination,
			'maxButtonCount' => 7,
		));
	?></div>
	<div class="elements_on_page drop_down">
		Показать<span class="exp_current"><?php echo $pageSize; ?><i></i></span>
		<ul>
			<?php foreach (Config::$mediaPageSizes as $key=> $value) {
				echo CHtml::tag('li', array('data-value'=>$key), $value);
			}
			?>
		</ul>
	</div>

	<div class="clear"></div>
</div>