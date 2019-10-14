<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/competitions.css'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js-new/competitions.js'); ?>

<div class="-grid-wrapper page-content -gutter-top">
	<div class="-grid">
		<!-- Main block //-->
		<div class="-col-12 competition-page">
			<div class="-grid">
				<div class="-col-10">
					<div class="description-block">
						<div class="-grid">
							<div class="-col-5">
								<img class="" src="/img-new/competiotion-ipad-result-top.png">
							</div>
							<div class="-col-4 -huge">
								Для участия в акции пользователям нужно было в период с 06.07.13 по 06.09.13 оставить подробный отзыв
								о работе специалиста, а специалистам — привлечь как можно больше своих клиентов к написанию отзывов.
							</div>
						</div>
					</div>
					<div class="-grid winners">
						<div class="-col-7 -skip-1">
							<h1>Победители акции</h1>
							<div class="-col-wrap -large">
								<a class="-huge -nodecor -semibold" href="http://www.myhome.ru/users/Sergeev_Peter">
									<img class="-gutter-bottom" src="/img-new/tmp/ipad4-winner-1.png" alt="">
									Петр Сергеев
								</a>,
								<span class="-block -gutter-bottom-hf">Дизайнер интерьеров Санкт-Петербург</span>
								<span>Приз: iPad 4</span>
							</div>
							<div class="-col-wrap"></div>
							<div class="-col-wrap  -large">
								<a class="-huge -nodecor -semibold" href="http://www.myhome.ru/users/Timur/activity?view=review">
									<img class="-gutter-bottom" src="/img-new/tmp/ipad4-winner-2.png" alt="">
									Тимур Гизитдинов
								</a>,
								<span class="-block -gutter-bottom-hf">Пользователь MyHome.ru Ростов-на-Дону</span>
								<span>Приз: iPad 4</span>
							</div>
							<p>Победитель среди пользователей определен случайным выбором, а среди специалистов — большинством положительных отзывов.</p>
							<a href="/ipad_za_spasibo_condition">Подробные условия акции</a>
						</div>


					</div>
					<script>
						competition.initInviteForm();

						$(function(){
							$('.show-conditions').click(function(){
								$(this).next().slideToggle('fast');
							});
						})
					</script>
				</div>
			</div>
		</div>

		<!-- EOF Main block //-->
	</div>
</div>
