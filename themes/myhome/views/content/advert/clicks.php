<?php
$this->pageTitle = 'Услуги и цены — MyHome.ru';
Yii::app()->clientScript->registerScriptFile('/js-new/adv.js');
Yii::app()->clientScript->registerScriptFile('/js-new/scroll.js');
Yii::app()->clientScript->registerCssFile('/css-new/generated/adv.css');

	/**
	*	Custom vars
	*/
	$menu_1 = false;
	$menu_2 = false;
	$menu_3 = false;
	$menu_4 = true;
?>
<!-- PAGE CONTENT -->

<!-- Page title widget //-->
<div class="-grid-wrapper page-title">
	<div class="-grid">
		<div class="-col-12">
			<ul class="-menu-inline -breadcrumbs">
				<li><a>Главная</a></li>
				<li><a href="index.php">Рекламодателям</a></li>
				<li><a>Услуги и цены</a></li>
			</ul>
		</div>
		<!-- 			<div class="-col-8"><h1>Готовая площадка для продаж</h1></div>
					<div class="-col-4 -inset-top -text-align-right"></div>
					<hr class="-col-12"> -->
	</div>
</div>
<!-- EOF Page title widget //-->

<!-- Page content wrap //-->
<div class="-grid-wrapper page-content">
	<div class="-grid">
		<?php
		include('common/_top.php');
		include('click/_content.php');
		include('common/_bottom.php');
		?>
	</div>
</div>
<!-- EOF Page content wrap //-->