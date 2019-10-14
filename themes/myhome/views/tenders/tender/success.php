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
		<h2>Ваш заказ отправлен на модерацию</h2>
		<p>Через <span class="redirect_time">6</span> секунд откроется <a href="/tenders/list">список заказов</a>.</p>
		<script>
			$(function(){
				var $time = $('.redirect_time');

				var t = setInterval(function(){
					$time.text( parseInt($time.text()) - 1);

					if (parseInt($time.text()) <= 0) {
						clearInterval(t);
						window.location.href = '/tenders/list';
					}
				}, 1000);
			});
		</script>
	</div>

</div>
<div class="clear"></div>
<div class="spacer-30"></div>