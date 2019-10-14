<?php $this->pageTitle = 'Профили в социальных сетях — MyHome.ru'?>
<?php Yii::app()->clientScript->registerCssFile('/css/user.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/profile.css'); ?>

<script>
	if (location.hash == '#close') {
		window.close();
	}
</script>

<div class="-grid-wrapper page-title">
	<div class="-grid">
		<div class="-col-12">
			<ul class="-menu-inline -breadcrumbs">
				<li><a href="<?php echo Yii::app()->homeUrl;?>">Главная</a></li>
				<li><a href="/specialist">Специалисты</a></li>
			</ul>
		</div>
		<div class="-col-12"><h1 class="-inline"><?php echo $user->name; ?></h1><a href="/member/profile/settings" onclick="Common.hideBlock();" class="-icon-pencil-xs -gutter-left -gray -small">Редактировать профиль</a></div>
	</div>
</div>

<div class="-grid-wrapper page-content">
	<div class="-grid">

		<div class="-col-3 profile-sidebar">
			<h3>Редактирование</h3>

			<?php $this->renderPartial('//member/profile/specialist/_settingsMenu', array('user' => $user)); ?>
		</div>


		<div class="-col-9">

			<?php
			/* -------------------------------------------------------------
			 *  Навигация
			 * -------------------------------------------------------------
			 */
			?>
			<?php $this->renderPartial('//member/profile/specialist/_menu', array('user' => $user)); ?>

			<div class="profile_info_conteiner">
				<a class="back_to_profile"
				   href="<?php echo $this->createUrl("/users/{$user->login}"); ?>">Вернуться
												   в
												   профиль</a>

				<form action="#"
				      class="form-profile form-social">
					<div class="shadow_block white profile_info">
						<h2>Аккаунты в социальных
						    сетях</h2>

						<div class="inner">
							<?php if (($tw = Oauth::checkBindTwitter($user->id))) { ?>
								<p class="fp-social">
									<a href="http://twitter.com/#!/<?php echo $tw->account_name; ?>"
									   class="fp-social-title"><img src="/img/tw.png"
													width="16"
													height="16"
													alt="">
										<?php if ($tw->social_name) : echo $tw->social_name; else : ?>twitter.com/<?php echo $tw->account_name; endif; ?>
									</a>
									<a href="/oauth/unbindtwitter"
									   class="fp-social-remove">Удалить</a>
								</p>
							<?php } else { ?>
								<p class="fp-social">
									<span class="fp-social-title"><img src="/img/tw.png"
													   width="16"
													   height="16"
													   alt="">Профиль в Twitter</span>
									<a href="#"
									   onclick="socialBind('/oauth/bindtwitter', 'Twitter'); return false;">Привязать</a>
								</p>
							<?php } ?>



							<?php if (($fb = Oauth::checkBindFacebook($user->id))) { ?>
								<p class="fp-social">
									<a href="http://www.facebook.com/profile.php?id=<?php echo $fb->account_name; ?>"
									   class="fp-social-title"><img src="/img/fb.png"
													width="16"
													height="16"
													alt="">
										<?php if ($fb->social_name) : echo $fb->social_name; else : ?>www.facebook.com/<?php echo $fb->account_name; endif; ?>
									</a>
									<a href="/oauth/unbindfacebook"
									   class="fp-social-remove">Удалить</a>
								</p>
							<?php } else { ?>
								<p class="fp-social">
									<span class="fp-social-title"><img src="/img/fb.png"
													   width="16"
													   height="16"
													   alt="">Профиль в Facebook</span>
									<a href="#"
									   onclick="socialBind('/oauth/bindfacebook', 'Facebook'); return false;">Привязать</a>
								</p>
							<?php } ?>



							<?php if (($vk = Oauth::checkBindVkontakte($user->id))) { ?>
								<p class="fp-social">
									<a href="http://vkontakte.ru/id<?php echo $vk->account_name; ?>"
									   class="fp-social-title"><img src="/img/vk.png"
													width="16"
													height="16"
													alt="">
										<?php if ($vk->social_name) : echo $vk->social_name; else : ?>vkontakte.ru/id<?php echo $vk->account_name; endif; ?>
									</a>
									<a href="/oauth/unbindvkontakte"
									   class="fp-social-remove">Удалить</a>
								</p>
							<?php } else { ?>
								<p class="fp-social">
									<span class="fp-social-title"><img src="/img/vk.png"
													   width="16"
													   height="16"
													   alt="">Профиль ВКонтакте</span>
									<a href="#"
									   onclick="socialBind('/oauth/bindvkontakte', 'ВКонтакте'); return false;">Привязать</a>
								</p>
							<?php } ?>

							<?php if (($odkl = Oauth::checkBindODKL($user->id))) { ?>
								<p class="fp-social">
									<a href="http://odnoklassniki.ru/profile/<?php echo $odkl->account_name; ?>"
									   class="fp-social-title"><img src="/img/ok.png"
													width="16"
													height="16"
													alt="">
										<?php echo $odkl->social_name; ?>
									</a>
									<a href="/oauth/unbindodkl"
									   class="fp-social-remove">Удалить</a>
								</p>
							<?php } else { ?>
								<p class="fp-social">
									<span class="fp-social-title"><img src="/img/ok.png"
													   width="16"
													   height="16"
													   alt="">Профиль в Одноклассниках</span>
									<a href="#"
									   onclick="socialBind('/oauth/bindodkl', 'Одноклассники'); return false;">Привязать</a>
								</p>
							<?php } ?>
						</div>

						<script type="text/javascript">
							$('.fp-social-remove').click(function () {
								var $a = $(this);
								doAction({
									yes: function () {
										document.location.href = $a.attr('href');

										return false;
									},
									no: function () {
										return false;
									}
								});
								return false;
							});
						</script>
					</div>
					<div class="spacer-30"></div>
					<div class="shadow_block white profile_info">
						<h2>Аккаунты на других
						    сайтах</h2>

						<div class="inner other_social">

							<?php foreach ($social as $item) : ?>
								<p class="inp_other_social">
									<label>Ссылка
									       на
									       профиль</label>
									<br>
									<?php echo CHtml::activeTextField($item, "[$item->id]url", array('class' => 'textInput social-url', 'data-id' => $item->id)); ?>
									<?php echo CHtml::link('Удалить', '#', array('class' => 'remove delete_other_social', 'data-id' => $item->id)); ?>
								</p>

							<?php endforeach; ?>

							<?php // сюда AJAX'ом вставляются Input'ы для ввода ссылок ?>

							<p>
								<a class="add add_other_social"
								   href="#">Добавить
									    ссылку
									    на
									    профиль<i></i></a>
							</p>
						</div>
					</div>
				</form>

				<?php
				Yii::app()->clientScript->registerScript('add_other', '
				$(function(){
					$("a.add_other_social").click(function(){
						var link = $(this);
						$.post(
							"/member/profile/add_other_social",
							function(response){
								if (response.success) {
									link.parent("p").before(response.html);
								}
							}, "json"
						);

						return false;
					});

					$("a.delete_other_social").live("click", function(){
						var link = $(this);
						$.post(
							"/member/profile/delete_other_social/id/"+link.attr("data-id"),
							function(response){
								if (response.success) {
									link.parents("p").slideUp(400).prev("p.hint").hide();

								}
							}, "json"
						);

						return false;
					});

					$("input.social-url").live("blur", function(){
						input = $(this);
						$.post(
							"/member/profile/social_update/id/"+input.attr("data-id"),
							{url: input.val()},
							function(response){
								var hint = input.parents("p").prev("p.hint");
								hint.html(response.message).show();
								hint.removeClass("hint-good").removeClass("hint-bad");

								if (response.success) {
									hint.addClass("hint-good").fadeOut(1500);
								} else {
									hint.addClass("hint-bad");
								}

							}, "json"
						);
					});
				})
			');
				?>


				<div class="clear"></div>
			</div>

		</div>

	</div>
</div>



<script>
        function socialBind(url, title)
        {
                var width = 560;		// ширина окна
                var height = 325;	// высота окна

                // Отступ слева
                var left = parseInt(document.documentElement.clientWidth / 2 - width / 2);
                var top = 250; // Отступ сверху


                var params = 'width='+width+', height='+height+', top='+top+', left='+left+', scrollbars=yes';
                oauthWindow = window.open(url, title, params);

		setTimeout(function () {
			if (oauthWindow.closed)
				document.location = document.location;
			else
				setTimeout(arguments.callee, 10);
		}, 10);
        }
</script>