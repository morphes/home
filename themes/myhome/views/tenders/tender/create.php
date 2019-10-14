<?php $this->pageTitle = 'Заказы — MyHome.ru'?>

<div class="pathBar">
	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
		'links'=>array(
			'Заказы' => array('/tenders/list'),
		),
	));?>
	<h1>Создать заказ на выполнение работ</h1>
	<div class="spacer"></div>
</div>
<div id="left_side">
	<div class="shadow_block padding-18 about_service">
		<p>Просим вас не использовать заказы для размещения коммерческих объявлений, сообщений рекламного характера и иных материалов, не имеющих отношения к тематике MyHome.</p>
		<p>Пожалуйста, не используйте в описании заказа рекламные ссылки на сторонние ресурсы.</p>
		<p>Просим не размещать файлы рекламного характера и вредоносные программы.</p>
	</div>
</div>
<div id="right_side">
	<div class="content_block ">
		<?php if (!Yii::app()->getUser()->getIsGuest()) : ?>
		<div class="guest_hint">
			Все заказы проходят обязательную проверку, которая может занять от 5 до 30 минут.
		</div>
		<?php else : ?>
		<div class="guest_hint">
			Чтобы иметь больше возможностей по работе с заказами — <a href="#" class="-login">авторизуйтесь</a> или <a href="/site/registration">зарегистрируйтесь</a> на сайте.
		</div>
		<?php endif; ?>

		<?php $this->widget('tenders.components.TenderForm.TenderForm', array(
			'tender' => $tender,
			'user' => $user,
		)); ?>
	</div>

</div>
<div class="clear"></div>
<div class="spacer-30"></div>