<?php
$this->pageTitle = 'Услуги и цены — MyHome.ru';
Yii::app()->clientScript->registerScriptFile('/js-new/adv.js');
Yii::app()->clientScript->registerScriptFile('/js-new/scroll.js');
Yii::app()->clientScript->registerCssFile('/css-new/generated/adv.css');

	/**
	*	Custom vars
	*/
	$menu_1 = true;
	$menu_2 = false;
	$menu_3 = false;
	$menu_4 = false;
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
<!--<div class="promo-banner-2013">Только в январе 2014!<strong>Скидка 50%</strong> на ВСЕ УСЛУГИ!</div>-->
<!-- Page content wrap //-->

	<div class="-grid-wrapper page-content">
		<div class="-grid">
			<?php
			include('common/_top.php');
			include('rates/_content.php');
			include('common/_bottom.php');
			?>
		</div>
	</div>

<?php
/* Автоматическое открывание попапов сделано для того, чтобы можно было
   их увидеть перейдя по прямой ссылке. */

if (isset($_GET['feedback']) && $_GET['feedback'] == 'true') : ?>
	<script>
		$(function(){
			$('.-feedback').click();
		});
	</script>

<?php elseif (isset($_GET['request']) && $_GET['request'] == 'true') : ?>
	<script>
		$(function(){
			adv.showForm();
		})
	</script>
<?php endif; ?>