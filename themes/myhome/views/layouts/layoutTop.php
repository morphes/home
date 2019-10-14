<div id="header">
	<div class="wrapper">

		<?php $this->widget('application.components.widgets.WMenu.WMenu', array(
			'typeMenu'  => Menu::TYPE_MAIN,
			'viewName'  => 'main',
			'showLevel' => 1,
			'activeKey' => $this->menuActiveKey,
			'activeLink'=> $this->menuIsActiveLink,
			'activeLinkOnlyParent'=> $this->menuIsActiveLinkOnlyParent,
		));?>

		<ul id="nav-support">
			<li class="first"><a href="/help"><i title="Помощь"></i></a></li>
			<li>
				<?php // Подключаем виджет для добавления в избранное
				$this->widget('application.components.widgets.ShowFavoriteLink.ShowFavoriteLink');?>
			</li>
			<li><a class="planner" href="/planner"><i></i>Онлайн-планировщик</a></li>
		</ul>


		<?php // Рендерим верхнюю часть сайта.
		$this->renderPartial('//widget/_head');
		?>
	</div>
</div>