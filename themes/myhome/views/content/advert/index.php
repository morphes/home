<?php
$this->pageTitle = 'Рекламодателям — MyHome.ru';
Yii::app()->clientScript->registerScriptFile('/js-new/adv.js');
Yii::app()->clientScript->registerCssFile('/css-new/generated/adv.css');
?>
<!-- PAGE CONTENT -->
<!-- Page title widget //-->
	<div class="-grid-wrapper page-title">
		<div class="-grid">
			<div class="-col-12">
				<ul class="-menu-inline -breadcrumbs">
					<li><a href="<?php echo Yii::app()->homeUrl?>">Главная</a></li>
					<li><a href="/advertising">Рекламодателям</a></li>
				</ul>
			</div>
        </div>
    </div>
<!--<div class="promo-banner-2013">Только в январе 2014!<strong>Скидка 50%</strong> на ВСЕ УСЛУГИ!</div>-->


<div class="-grid-wrapper page-content">
    <div class="-grid">
            <div class="-col-12">
				<ul class="-menu-inline -justified-menu top-menu">
					<li class="current"><a href="#" class="-large"">Рекламодателям</a></li>
					<li><a href="/advertising/advantages" class="-large">Наши преимущества</a></li>
                    <li></li>
                    <li></li>
				</ul>
			</div>
		</div>
	</div>

<!-- EOF Page title widget //-->


<!-- Page content wrap //-->
	<div class="-grid-bracing">
		<div class="-grid-wrapper page-content">
			<div class="-grid">
				<?php
					include('index/_middle.php');
				?>
			</div>
		</div>
	</div>
	<div class="-grid-wrapper page-content">
		<div class="-grid -inset-top-dbl">
			<?php
				include('common/_bottom.php');
			?>
		</div>
	</div>
<!-- EOF Page content wrap //-->

<!-- EOF PAGE CONTENT -->
