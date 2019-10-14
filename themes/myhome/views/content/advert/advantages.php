<?php
$this->pageTitle = 'Наши преимущества — MyHome.ru';
Yii::app()->clientScript->registerScriptFile('/js-new/adv.js');
Yii::app()->clientScript->registerCssFile('/css-new/generated/adv.css');
?>


<!-- PAGE CONTENT -->

<!-- Page title widget //-->
	<div class="-grid-wrapper page-title">
		<div class="-grid">
			<div class="-col-12">
				<ul class="-menu-inline -breadcrumbs">
					<li><a href = "<?php echo Yii::app()->homeUrl?>">Главная</a></li>
					<li><a href = "/advertising">Рекламодателям</a></li>
					<li><a href = "/advertising/advantages">Наши преимущества</a></li>
				</ul>
			</div>
<!-- 			<div class="-col-8"><h1>Готовая площадка для продаж</h1></div>
			<div class="-col-4 -inset-top -text-align-right"></div>
			<hr class="-col-12"> -->
		</div>
	</div>
<!--<div class="promo-banner-2013">Только в январе 2014!<strong>Скидка 50%</strong> на ВСЕ УСЛУГИ!</div>-->

<!-- EOF Page title widget //-->

<!-- Page content wrap //-->
	<div class="-grid-wrapper page-content">
		<div class="-grid">
			<?php
				include('advantages/_top.php');
			?>
		</div>
	</div>
	<div class="-grid-bracing -gutter-top-dbl -gutter-bottom-dbl">
		<div class="-grid-wrapper page-content">
			<div class="-grid">
				<?php
					include('advantages/_content.php');
				?>
			</div>
		</div>
	</div>
	<div class="-grid-wrapper page-content">
		<div class="-grid">
			<?php
				include('advantages/_sub.php');
			?>
		</div>
	</div>
	<div class="-grid-crossing -gutter-top-dbl -gutter-bottom-dbl">
		<div class="-grid-wrapper page-content">
			<div class="-grid">
				<?php
					include('advantages/_button.php');
				?>
			</div>
		</div>
	</div>
	<div class="-grid-wrapper page-content">
		<div class="-grid">
			<?php
				include('advantages/_bottom.php');
			?>
		</div>
	</div>

<script>
	adv.advantages();
</script>
<!-- EOF Page content wrap //-->

<!-- EOF PAGE CONTENT -->
