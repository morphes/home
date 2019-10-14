<a href="#" id="feedback-handler">Обратная связь</a>

<div class="header_tools">
	<?php echo CHtml::beginForm('/search', 'get', array('name' => 'GlobalSearch', 'class' => 'search')); ?>
	<?php echo CHtml::textField('q', Yii::app()->request->getParam('q') ? Yii::app()->request->getParam('q') : 'Что ищем?', array('id' => 'global_search', 'class' => 'textInput-placeholder', 'data-placeholder'=>'Что ищем?'));?>
	<p class="submit">
		<button type="submit" class="button">Найти</button>
	</p>
	<?php echo CHtml::endForm(); ?>

	<?php if (Yii::app()->user->isGuest) : ?>
	<p class="auth">
		<?php
		echo CHtml::link('Зарегистрироваться', '/site/registration', array('class' => 'button-login button button-black', 'onclick' => "_gaq.push(['_trackEvent','Registration','Bbutton']); yaCounter11382007.reachGoal('ybb'); return true;"));
		echo CHtml::link('Войти', '/site/login', array('class' => 'button-login button button-black'));
		?>
	</p>
	<?php endif; ?>
</div>

<div id="footer">
	<div class="wrapper">
		<div class="footer_block counter">
			<?php $this->widget('application.components.widgets.WMenu.WMenu', array(
			'typeMenu'  => Menu::TYPE_FOOTER,
			'viewName'  => 'footer',
			'showLevel' => 1,
			'activeKey' => '',
			'activeLink'=> $this->menuIsActiveLink,
			'activeLinkOnlyParent'=> $this->menuIsActiveLinkOnlyParent,
		));?>

		</div>
		<div class="footer_block">
			<?php $this->widget('application.components.widgets.WMenu.WMenu', array(
			'typeMenu'  => Menu::TYPE_FOOTER_ADD,
			'viewName'  => 'footerAdd',
			'showLevel' => 1,
			'activeKey' => '',
			'activeLink'=> $this->menuIsActiveLink,
			'activeLinkOnlyParent'=> $this->menuIsActiveLinkOnlyParent,
		));?>
		</div>
		<div class="footer_block social_links">
			<p>Мы в социальных сетях</p>
			<ul>
				<li><img src="/img/vk.png"><a target="_blank" href="http://vkontakte.ru/myhomeru">ВКонтакте</a></li>
				<li><img src="/img/fb.png"><a target="_blank" href="http://facebook.com/myhome.ru">Facebook</a></li>
				<li><img src="/img/tw.png"><a target="_blank" href="http://twitter.com/MyHomeRu">Twitter</a></li>
				<li><img src="/img/yt.png"><a target="_blank" href="http://youtube.com/myhomeru">Youtube</a></li>
				<li><img src="/img/ok.png"><a target="_blank"  href="http://www.odnoklassniki.ru/myhome">Одноклассники</a></li>
				<li><img src="/img/pm.png"><a target="_blank"  href="http://pinme.ru/u/myhomeru/">Pinme</a></li>
			</ul>
		</div>
		<div class="footer_block descript">
			<p>© ООО «МайХоум», 2011&ndash;<?php echo date('Y'); ?>.<br/> Все права защищены</p>
			<p>Использование материалов www.myhome.ru без разрешения
				редакции сайта запрещено. Более подробную информацию вы можете получить в разделе <a href="<?php echo Yii::app()->homeUrl;?>/copyright">Авторские права</a>.</p>
			<?php if (empty(Yii::app()->params->stopStatistic)) : ?>
                                <span id='top100counter' style="margin-right: 5px;"></span>
                                <script type="text/javascript">
                                        var _top100q = _top100q || [];
                                        _top100q.push(["setAccount", "2861517"]);
                                        _top100q.push(["trackPageviewByLogo", document.getElementById("top100counter")]);
                                        (function(){
                                            var top100 = document.createElement("script"); top100.type = "text/javascript";
                                            top100.async = true;
                                            top100.src = ("https:" == document.location.protocol ? "https:" : "http:") + "//st.top100.ru/top100/top100.js";
                                            var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(top100, s);
                                        })();
                                </script>
				<!--LiveInternet counter--><script type="text/javascript"><!--
				document.write("<a href='http://www.liveinternet.ru/click;MyHome' "+
					"target=_blank><img src='//counter.yadro.ru/hit;MyHome?t18.2;r"+
					escape(document.referrer)+((typeof(screen)=="undefined")?"":
					";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
						screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
					";"+Math.random()+
					"' alt='' title='LiveInternet: показано число просмотров за 24"+
					" часа, посетителей за 24 часа и за сегодня' "+
					"border='0' width='88' height='31'><\/a>")
				//--></script><!--/LiveInternet-->
                                <div><noindex><a rel="nofollow" href="http://www.rambler.ru/" target="_blank" style="color: #3f3f3f !important;">Партнер «Рамблера»</a></noindex></div>
			<?php endif; ?>
		</div>
	</div>
</div>


<?php
// Clip попапа создается в //widget/_head
echo $this->clips['popup_auth'];
?>

<div class="popup popup-feedback" id="popup-feedback">
	<div class="popup-header">
		<div class="popup-header-wrapper">
			Обратная связь
			<span class="popup-close" title="Закрыть">Закрыть</span>
		</div>
	</div>
	<div class="popup-body">
		<form id="feedback-form" action="#">

			<p class='hint'>
				Ответы на многие вопросы и подробное описание сайта вы можете найти самостоятельно в разделе <a href="<?php echo $this->createUrl('/help/help/index'); ?>">Помощь по сайту</a>. Если вы не нашли ответ на свой вопрос, задайте его с помощью этой формы. Специалист поддержки ответит вам в течение 24 часов.
			</p>
			<p class="f-userdata">
				<label for="p-feedback-name">
					<strong>Ваше имя</strong>
				</label>
				<input id="p-feedback-name" class="textInput" value="<?php if (!Yii::app()->user->isGuest) echo Yii::app()->user->model->name;?>">
			</p>
			<p  class="f-userdata f-mail">
				<label for="p-feedback-email">
					<strong>Адрес электронной почты</strong>
				</label>
				<input id="p-feedback-email" class="textInput" value="<?php if (!Yii::app()->user->isGuest) echo Yii::app()->user->model->email;?>">
			</p>
			<p class="p-feedback-message">
				<label for="p-feedback-message">
					<strong>Ваш вопрос</strong>
				</label>
				<br>
				<textarea id="p-feedback-message" class="textInput"></textarea>
			</p>
			<p class="p-feedback-files">
				<label for="p-feedback-message">
					<strong>Прикрепить файл</strong>
				</label>
			<div class="file_list">
				<div><input class="" size=40 type="file"/></div>
			</div>

                        <input id="p-feedback-page_url" type="hidden" value="<?php echo Yii::app()->request->hostInfo . Yii::app()->request->url; ?>">

			</p>
			<div class="btn_conteiner yellow">
				<button type="submit" class="btn_grey">Отправить</button>
			</div>
			<a class="cancel_link" href="#">Отменить</a>
			<p class="error-title"></p>
			<p class="good-title" style="display: none;"></p>
		</form>
	</div>
</div>

<?php
// Clip попапа отправки личного сообщения.
// Сам попап находится здесь //member/message/_newMessage
// Рендерится в //member/message/inbox и //member/message/outbox
echo $this->clips['newPersonalMessage'];
?>


<?php
// Clip попапа отправки личного сообщения специалисту, в случае когда посетитель Гость.
// Сам попап находится здесь //member/profile/specialist/_sidebar
echo $this->clips['newMessageGuest'];
?>
<?php
// Clip для правил добавления отзыва на спеца
echo $this->clips['reviewRules'];
?>

<?php
// Рендерим представление для Золотой капители с попапами
if (Yii::app()->request->getRequestUri() == '/article/konkurs/rules')
	$this->renderPartial('//widget/popupCapitel');
?>

<?php // Вывод ссылки в админку для Poweradmin'ов
if (Yii::app()->user->checkAccess(array(
	User::ROLE_POWERADMIN,
	User::ROLE_JUNIORMODERATOR,
	User::ROLE_MODERATOR,
	User::ROLE_SENIORMODERATOR,
	User::ROLE_ADMIN,
	User::ROLE_SALEMANAGER,
	User::ROLE_FREELANCEEDITOR,
	User::ROLE_SEO,
	User::ROLE_JOURNALIST,
))) {
	echo CHtml::link('Центр управления', '/admin', array('class' => 'admin_link'));
}
?>

<?php // Вывод списка городов для фильтра на странице "СПециалисты"
// Формируется в представлении //member/specialist/designer
echo $this->clips['cityFilter'];
?>

<?php
// Вывод списка городов, в которых специалист предоставляет услуги
// //member/profile/specialist/services_own
echo $this->clips['cityServiceSelector'];
?>

<?php if (empty(Yii::app()->params->stopStatistic)) : ?>

<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-26286775-1']);
	_gaq.push(['_setDomainName', 'myhome.ru']);
	_gaq.push(['_addOrganic', 'images.yandex.ru',  'q', true]);
	_gaq.push(['_addOrganic', 'blogsearch.google.ru',  'q', true]);
	_gaq.push(['_addOrganic', 'blogs.yandex.ru',  'text', true]);
	_gaq.push(['_addOrganic', 'go.mail.ru',  'q']);
	_gaq.push(['_addOrganic', 'nova.rambler.ru',  'query']);
	_gaq.push(['_addOrganic', 'nigma.ru', 's']);
	_gaq.push(['_addOrganic', 'webalta.ru', 'q']);
	_gaq.push(['_addOrganic', 'aport.ru', 'r']);
	_gaq.push(['_addOrganic', 'poisk.ru', 'text']);
	_gaq.push(['_addOrganic', 'km.ru', 'sq']);
	_gaq.push(['_addOrganic', 'liveinternet.ru', 'ask']);
	_gaq.push(['_addOrganic', 'quintura.ru', 'request']);
	_gaq.push(['_addOrganic', 'search.qip.ru', 'query']);
	_gaq.push(['_addOrganic', 'gde.ru', 'keywords']);
	_gaq.push(['_addOrganic', 'gogo.ru', 'q']);
	_gaq.push(['_addOrganic', 'ru.yahoo.com', 'p']);
	_gaq.push(['_addOrganic', 'poisk.ngs.ru', 'q']);
	_gaq.push(['_addOrganic', 'meta.ua', 'q']);
	_gaq.push(['_addOrganic', 'bigmir.net', 'q']);
	_gaq.push(['_addOrganic', 'i.ua', 'q']);
	_gaq.push(['_addOrganic', 'online.ua', 'q']);
	_gaq.push(['_addOrganic', 'a.ua', 's']);
	_gaq.push(['_addOrganic', 'ukr.net', 'search_query']);
	_gaq.push(['_addOrganic', 'search.com.ua', 'q']);
	_gaq.push(['_addOrganic', 'search.ua', 'query']);
	_gaq.push(['_addOrganic', 'search.ukr.net', 'search_query']);

		<?php if (Yii::app()->user->getIsGuest()) : ?>
	_gaq.push(['_setCustomVar',1,'User','Guest',2]);
		<?php else : ?>
	_gaq.push(['_setCustomVar',1,'User','Register',2]);
		<?php endif; ?>

	_gaq.push(['_trackPageview']);

	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
	// Yandex.Metrika counter
	(function (d, w, c) {
		(w[c] = w[c] || []).push(function() {
			try {
				w.yaCounter11382007 = new Ya.Metrika({id:11382007, enableAll: true, trackHash:true, webvisor:true});
			} catch(e) {}
		});

		var n = d.getElementsByTagName("script")[0],
			s = d.createElement("script"),
			f = function () { n.parentNode.insertBefore(s, n); };
		s.type = "text/javascript";
		s.async = true;
		s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

		if (w.opera == "[object Opera]") {
			d.addEventListener("DOMContentLoaded", f);
		} else { f(); }
	})(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="//mc.yandex.ru/watch/11382007" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrikacounter -->
<?php endif; ?>