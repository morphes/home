<div class="page_settings">
	<div class="sort_elements drop_down tender_list">
		Сортировать по
		<?php
		if (!isset(Tender::$sortNames[$sortType])) {
			$sortType = @reset(array_keys($sortList));
		}
		?>
		<span class="exp_current"><?php echo Tender::$sortNames[$sortType]; ?><i></i></span>
		<ul>
			<?php

			foreach (Tender::$sortNames as $key => $value) {
				if ($key == $sortType)
					echo CHtml::tag('li', array('data-value' => $key, 'class' => 'active'), $value);
				else
					echo CHtml::tag('li', array('data-value' => $key), $value);
			}
			?>
		</ul>
	</div>
	<div class="elements_on_page drop_down">
		На странице <span class="exp_current"><?php echo $availablePageSizes[$pageSize]; ?><i></i></span>
		<ul>
		<?php 
		foreach ($availablePageSizes as $key => $value) {
			echo CHtml::tag('li', array('data-value' => $key), $value);
		}
		?>
		</ul>
	</div>


    <?php if (isset($bottom) && $bottom == true) : ?>
        <div class="pagination" style="float:right; display: block; padding-top: 5px;">
            <?php $this->widget('application.components.widgets.CustomListPager', array(
                'pages'          => $pagination,
                'htmlOptions'    => array('class' => '-menu-inline -pager'),
                'maxButtonCount' => 5,
            )); ?>
        </div>
    <?php else : ?>
        <div class="pages">
            <?php
            $this->widget('application.components.widgets.CustomPager2', array(
                'pages' => $pagination,
                'maxButtonCount' => 5,
            ));
            ?>
        </div>
    <?php endif; ?>

	<div class="clear"></div>
	
</div>