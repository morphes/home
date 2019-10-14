<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/auth.css'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/scroll.js'); ?>

<div class="-grid-bracing">
	<div class="-grid-wrapper">
		<div class="-grid">
			<div class="-col-12">
				<h3 class="-giant -gutter-top-null -gutter-bottom">Добро пожаловать на MyHome!</h3>
				<p class="-huge -normal -gutter-null">Предлагаем вам кратко ознакомиться со всеми возможностями MyHome или <a href="#" onclick="Auth.redirectToRegReturnUrl(); return false;" class="-skyblue">вернуться на страницу, с которой вы пришли</a></p>
			</div>
		</div>
	</div>
</div>
<div class="-grid-wrapper page-content">
	<div class="-grid">


		<div class="-col-12 -inset-top-dbl">
			<div class="-grid -inset-top-dbl -force-middle -inset-bottom-dbl">
				<div class="-col-5 -gutter-bottom-dbl"><img src="/img-new/reg-promo-1.jpg"></div>
				<div class="-col-7">
					<h1 class="-gutter-bottom-dbl">Создавайте уют в доме</h1>
					<p class="-huge -normal">Выбирайте, покупайте и коллекционируйте товары для дома из более чем 100 000 наименований</p>
					<a href="/catalog" class="-button -button-skyblue -huge -semibold">Выбрать товары</a>
				</div>
				<div class="-col-7">
					<h1 class="-gutter-bottom-dbl">Творите и вдохновляйтесь</h1>
					<p class="-huge -normal">Каждый день открывайте новые яркие идеи интерьеров и архитектуры, создавайте собственные коллекции для вдохновения!</p>
				</div>
				<div class="-col-5 -gutter-bottom-dbl -relative ideas">
					<img src="/img-new/reg-promo-2.jpg">
					<a href="/idea/interior" class="-button -button-skyblue -huge -semibold -absolute">Смотреть фото</a>
				</div>
				<div class="-col-5 -gutter-bottom-dbl"><img src="/img-new/reg-promo-3.jpg"></div>
				<div class="-col-7">
					<h1 class="-gutter-bottom-dbl">Привлекайте специалистов</h1>
					<p class="-huge -normal">Заказывайте услуги профессиональных специалистов или организаций в вашем городе, оценивайте их работу по достоинству. На MyHome уже более 12 000 специалистов!</p>
					<a href="/specialist" class="-button -button-skyblue -huge -semibold">Найти специалиста</a>
				</div>
			</div>
			<h1 class="-em-top -gutter-bottom-null">А также...</h1>
			<div class="-grid -inset-bottom-dbl">
				<div class="-col-4">
					<h3 class="-giant -gutter-bottom">Будьте в курсе</h3>
					<p class="-large -gray -gutter-bottom-dbl">Следите за <a href="/journal" class="-skyblue">самым интересным</a><br>в мире дизайна и архитектуры</p>
					<img src="/img-new/reg-promo-4.jpg" class="-gutter-all">
				</div>
				<div class="-col-4">
					<h3 class="-giant -gutter-bottom">Обсуждайте</h3>
					<p class="-large -gray -gutter-bottom-dbl"><a href="/forum" class="-skyblue">Общайтесь на форуме MyHome</a>,<br>задавайте вопросы экспертам</p>
					<img src="/img-new/reg-promo-5.jpg" class="-gutter-top">
				</div>
				<div class="-col-4">
					<h3 class="-giant -gutter-bottom">Размещайте заказы</h3>
					<p class="-large -gray -gutter-bottom-dbl"><a href="/tenders/list" class="-skyblue">Размещайте свои заказы</a> на MyHome<br>и специалисты сами найдут вас</p>
					<img src="/img-new/reg-promo-6.jpg" class="-gutter-all">
				</div>
			</div>
		</div>

	</div>
</div>

<?php if (Yii::app()->user->role == User::ROLE_USER) { ?>

	<div class="-grid-bracing">
		<div class="-grid-wrapper">
			<div class="-grid">
				<div class="-col-12 -gutter-bottom-null -text-align-center">
					<h3 class="-gutter-null">Хотите попасть в каталог специалистов — присоединяйтесь</h3>
					<a href="#" class="-button -semibold -button-skyblue -gutter-top -registration">Присоединиться как специалист</a>
				</div>
			</div>
		</div>
	</div>

<?php } else { ?>

	<div class="-grid-bracing -invite">
		<div class="-grid-wrapper">
			<div class="-grid">
				<div class="-col-12 -gutter-bottom-null -text-align-center">
					<h3 class="-gutter-null">Расскажите о себе — <a href="<?php echo Yii::app()->user->model->getLinkProfile();?>" class="-skyblue">заполните свой профиль на MyHome</a></h3>
				</div>
			</div>
		</div>
	</div>

<?php } ?>

<div class="-text-align-center -huge -normal -inset-top-dbl">
	<a href="#" onclick="Auth.redirectToRegReturnUrl(); return false;" class="-pointer-right -skyblue -nodecor">Вернуться на страницу, с которой вы пришли</a>
</div>