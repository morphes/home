<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/competitions.css'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js-new/competitions.js'); ?>

<!-- PAGE CONTENT -->
<!-- Page content wrap //-->
<div class="-grid-wrapper page-content -gutter-top">
	<div class="-grid">
		<!-- Main block //-->
		<div class="-col-12 competition-page">
			<div class="-grid">
				<div class="-col-10">
					<div class="description-block">
						<div class="-grid">
							<img class="-gutter-left-hf" src="/img-new/competiotion-ipad-top.png">
							<div class="-col-9 -border-radius-all error -gutter-bottom-dbl -inset-all -success -red -giant">
								Акция завершена. <a href="/ipad_za_spasibo">Победители определены.</a>
							</div>
							<div class="-col-5">
								<div class="-giant -gutter-bottom-dbl -inset-bottom-dbl">
									Вы уже нашли себе профессионала
									на MyHome или другом ресурсе?<br>
									С 6 июля по 6 сентября оставьте<br>
									о нём отзыв и получите приз!
								</div>
								<div class="-huge -inset-top">
									У нас есть отличное предложение в обмен<br>
									на несколько ваших предложений.<br>
									Напишите отзыв о работе, проделанной<br>
									для вас специалистом, найденным на портале.
								</div>
							</div>
							<div class="-col-3 -skip-1">
								<div class="-gutter-bottom-dbl -inset-bottom-dbl -gutter-top-dbl -inset-top">
									Приложите к своему отзыву фотографию, и в случае победы<br>
									в дополнение мы подарим вам фотоаппарат Nikon1 J2!
								</div>
								<div class="-inset-top">
									Больше отзывов — больше вероятность того, что после<br>
									10 сентября 2013 года вы будете заходить на MyHome с новенького iPad 4!
								</div>
							</div>
						</div>

					</div>
					<div class="-grid -tinygray-bg button-block " id="inviteFormBlock">
						<span class="-icon-cross -icon-gray -icon-only -hidden" id="closeBtn"></span>
						<a class="-col-5 -button -button-green -huge -semibold -gutter-right-null" href="<?php echo Yii::app()->homeUrl.'/specialist' ?>" target="_blank">Найти специалиста и оставить отзыв</a>
						<div class="-col-3 -skip-1 -gutter-right-null -gutter-bottom-null -inset-left-hf">Не нашли своего специалиста?<br><span class="-acronym -red"  id="btnToggleForm">Пригласите</span> его на MyHome</div>

						<?php if(Yii::app()->user->getIsGuest()){ ?>
						<!-- Это показываем гостю //-->
						<div class="-col-7 -inset-top -hidden">Для того чтобы пригласить пользователя <a href="#" class="-red -login" >войдите</a> или <a href="/site/registration" class="-red">зарегистрируйтесь</a></div>
						<?php } else{ ?>

						<!-- Выводим, если юзер авторизован //-->
						<div class="-col-7 -gutter-bottom-null -hidden"><form><input type="text" placeholder="Электронная почта специалиста" value="" class="-col-5 -large emailInvite"><button class="-button -button-green -huge -semibold" id="submitInviteForm">Пригласить</button></form></div>
						<?php } ?>





					</div>
					<div class="-grid conditions">
						<div class="-col-7 -gutter-bottom-dbl">
							<h2>Кому призы?</h2>
							<p>Мы определим двух победителей — одного автора отзыва и одного специалиста. Каждый из победителей получит планшетный компьютер
							   iPad 4 (16 Гб, Wi-Fi). Лучший автор отзывов определится случайным выбором,
							   а лучшим специалистом станет тот, на чьей странице оставят больше всего соответствующих условиям отзывов.
							</p>
							<p>Если победитель среди авторов отзывов дополнит свой отзыв фотографией, то в дополнение к основному призу
							   получит фотоаппарат Nikon1 J2! Помните, что перед публикацией на сайте, все фотографии утверждаются
							   администраторами MyHome.
							</p>
						</div>
						<div class="-col-7">
							<span class="-acronym show-conditions">Подробные условия конкурса</span>
							<ol class="-hidden">
								<li>Отзывы  принимаются с 6 июля по 6 сентября 2013 года от зарегистрированных пользователей MyHome, являющихся гражданами РФ и достигших 18 лет. Зарегистрироваться на сайте, чтобы оставить свой отзыв, можно на любом этапе конкурса.</li>
								<li>Отзыв должен быть правдивым, включать исчерпывающую информацию о поставленной вами задаче, её решении и полученном результате, не должен содержать ненормативную лексику. Все  отзывы и/или картинки на сайте MyHome утверждаются  администраторами. Организатор оставляет за собой право не объяснять причины отказа в публикации отзыва для участия в конкурсе.</li>
								<li>10 сентября 2013 года выбранным победителям будет отправлено письмо, на которое необходимо будет ответить в течение 48 часов, чтобы подтвердить свой выигрыш. Если в течении двух суток подтверждение не будет получено, то будет выбран новый победитель. Имя победителя будет опубликовано на странице MyHome не позднее 12 сентября 2013 года.</li>
								<li>Вручение призов победителям конкурса осуществляется в течение 5 (пяти) дней с момента его определения. Согласием на получение приза в Интернете считается предоставление необходимых данных: фамилия, имя, отчество, полный адрес проживания, контактный телефон. Эти данные должны быть предоставлены не позднее, чем через 2 (две) недели после уведомления участника о победе.</li>
								<li>В конкурсе запрещается участвовать работникам и представителям Организатора конкурса, аффилированным лицам, членам семей таких работников и представителей, а также работникам и представителям любых других лиц, имеющих непосредственное отношение к организации или проведению настоящего конкурса.</li>
							</ol>
							<p class="questions-block">По всем вопросам обращайтесь<br>
										   к организатору конкурса — порталу «MyHome».<br>
										   <br>
								<a href="mailto:info@myhome.ru">info@myhome.ru</a>
							</p>
							<div class="-gutter-bottom-dbl -inset-bottom">
								<?php $this->widget('application.components.widgets.likes.Likes'); ?>
							</div>
							<a class="-gray" href="<?php echo Yii::app()->homeUrl.'/competition' ?>">Архив конкурсов MyHome</a>
						</div>
					</div>
				</div>
			</div>
		</div>
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