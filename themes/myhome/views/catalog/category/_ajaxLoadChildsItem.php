<div class="-breadcrumbs-submenu -col-6">
	<div class="-col-3 submenu_section">
		<ul class="-menu-block -small">
			<?php
			$curQnt = 0;
			$cols = 2;
			$totalQnt = count($categories);
			foreach ($categories as $category) {
				if ($curQnt > intval($totalQnt / $cols) && $cols != 1) {
					echo '</ul></div>';
					echo '<div class="-col-3 submenu_section">'
						.'<ul class="-menu-block -small">';
					$cols--;
					$curQnt = 0;
				}

				$class = ($category->id == $currentId)
					? 'current'
					: '';

				echo CHtml::openTag('li', array('class' => $class));
				echo CHtml::link($category->name, Category::getLink($category->id));
				echo CHtml::closeTag('li');

				$curQnt++;
			}
			?>
		</ul>
	</div>
</div>