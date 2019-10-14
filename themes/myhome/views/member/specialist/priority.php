<?php
/** @var $cs CClientScript */
$cs = Yii::app()->getClientScript();
$cs->registerCssFile('/css-new/generated/adv.css');
$cs->registerScriptFile('/js-new/adv.js');
$this->pageTitle = 'Платная приоритезация — Специалистам — MyHome.ru';
?>

<!-- Page content wrap //-->
<div class="-tinygray-bg">
	<div class="-grid-wrapper page-content">
		<div class="-grid">
			<div class="-col-12">
				<h1>Поднятие профиля в списке специалистов</h1>
			</div>
			<div class="-col-3">
				<img src="/img-new/clients-qnt.png" alt="">
			</div>
			<div class="-col-8 -inset-left">
				<p class="-huge">Поднятие профиля — это эффективный инструмент для продвижения своих услуг, благодаря которому вы окажетесь на первой странице каталога специалистов, не потратив много времени и средств. Ваши потенциальные клиенты смогут в первые же секунды поисков увидеть ваше портфолио.
				</p>
			</div>
		</div>
	</div>
</div>
<div class=" blue-box">
	<div class="-grid-wrapper  page-content">
		<div class="-grid">
			<div class="-col-12">
				<h2 class="-promo-discount">Поднимите профиль сейчас</h2>

				<?php
					$label = '';
					$id = '';
					if ( !Yii::app()->user->isGuest && in_array(Yii::app()->user->model->role, array(User::ROLE_SPEC_JUR, User::ROLE_SPEC_FIS)) )
					{
						$label = Yii::app()->user->model->name;
						$id = Yii::app()->user->id;
					}
				?>



				<form class="-form-inline">
					<label>
						<input type="text" value="<?php echo CHtml::encode($label); ?>" placeholder="Имя или название компании" class="-col-7 -gutter-left-null">
					</label>
					<div class="-col-wrap">
						<button class="-button -button-green pay" data-service-id="" data-city-id="" data-id="<?php echo $id; ?>">Поднять профиль</button>
					</div>
				</form>
				<div class="-col-wrap user-autocomplete">

				</div>
			</div>
		</div>
	</div>
</div>
<div class="-grid-wrapper  page-content">
	<div class="-grid">
		<div class="stat-table">
			<div class="-col-12">
				<h2>Какого эффекта можно ожидать? *</h2>
			</div>
			<div class="-col-8">
				<div class="-grid">
					<div class="-col-2">
						<i></i>
						<div class="-vast -strong">
							14 000
						</div>
						<span>показов профиля</span><br><br>
					</div>
					<div class="-col-2">
						<i></i>
						<div class="-vast -strong">
							8 000
						</div>
						<span>уникальных просмотров</span>
					</div>
					<div class="-col-2">
						<i></i>
						<div class="-vast -strong">
							200
						</div>
						<span>просмотров контактов</span>
					</div>
					<div class="-col-wrap -gutter-top-dbl -large">
						1 страница
					</div>
					<div class="-col-2">
						<div class="-header-h3">
							5 500
						</div>
					</div>
					<div class="-col-2">
						<div class="-header-h3">
							600
						</div>
					</div>
					<div class="-col-2">
						<div class="-header-h3">
							40
						</div>
					</div>
					<div class="-col-wrap -large">
						2 страница
					</div>
					<div class="-col-2">
						<div class="-header-h3">
							1 100
						</div>
					</div>
					<div class="-col-2">
						<div class="-header-h3">
							200
						</div>
					</div>
					<div class="-col-2">
						<div class="-header-h3">
							20
						</div>
					</div>
					<div class="-col-wrap -large">
						5 страница
					</div>
					<div class="-col-2">
						<div class="-header-h3">
							60
						</div>
					</div>
					<div class="-col-2">
						<div class="-header-h3">
							40
						</div>
					</div>
					<div class="-col-2">
						<div class="-header-h3">
							10
						</div>
					</div>
					<div class="-col-wrap -large">
						6 страница
					</div>
					<p class="-col-8 -gutter-top -small -gray">* указаны данные за месяц на примере раздела «<a href="/specialist/interior-design">дизайн интерьера</a>»</p>
				</div>
			</div>
			<div class="-col-3 -skip-1 -border-all">
				<h3>Зачем поднимать профиль?</h3>
				<p>Каждый день в&nbsp;поисках идей и&nbsp;специалистов на&nbsp;портал MyHome заходит 14&nbsp;000&nbsp;человек. </p>
				<p>Статистика показывает, что большинство посетителей находит «своего» специалиста уже на&nbsp;первой странице списка, и&nbsp;только&nbsp;38% посетителей продолжает поиск на&nbsp;второй странице.</p>
			</div>
			<div class="-col-8">
				<h2>Как воспользоваться услугой?</h2>
				<p>Чтобы воспользоваться услугой, воспользуйтесь формой в верхней части этой страницы или нажмите на кнопку «Поднять свой профиль» в вашем личном кабинете или на иконку <span class="pay-icon"></span> на своем профиле в списке специалистов.</p>
				<p>Стоимость услуги варьируется от&nbsp;10&nbsp;до&nbsp;214 рублей в&nbsp;сутки, в&nbsp;зависимости от&nbsp;выбранного региона, списка специалистов и&nbsp;периода (3, 7&nbsp;или 14&nbsp;дней).</p>
				<span class="-acronym show-conditions">Подробнее о&nbsp;подключении услуги ↓</span>
				<div class="-hidden -gutter-top">
					<p>В течение нескольких часов ваш профиль поднимется в&nbsp;выбранном списке, заняв одну из&nbsp;десяти приоритетных позиций на&nbsp;первой странице.</p>
					<p>Приоритетные позиции чередуются с&nbsp;позициями, занимаемыми лидерами рейтинга, и&nbsp;меняются при перезагрузке страницы, давая возможность побывать на&nbsp;первых местах каждому, кто приобрел услугу.</p>
					<p>По&nbsp;истечении выбранного вами периода профиль вернется на&nbsp;свое место по&nbsp;рейтингу. Обратите внимание, что действие услуги будет гораздо эффективнее, если вы&nbsp;максимально подробно заполните свой профиль и&nbsp;загрузите проекты в&nbsp;портфолио.</p>
				</div>
			</div>
		</div>
		<script>
			adv.initProAccount();
			CCommon.initPayment();
		</script>
	</div>
</div>

<div class="-col-wrap -hidden" id="pay-form"></div>

<!-- EOF Page content wrap //-->