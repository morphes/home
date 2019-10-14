<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
	<?php Yii::app()->less->register(); ?>
	<?php
	/** @var $cs CustomClientScript */
	$cs = Yii::app()->getClientScript();
	// Служебные метатеги
	$cs->registerMetaTag(CHtml::encode($this->description), 'description');
	$cs->registerMetaTag(CHtml::encode($this->keywords), 'keywords');
	//$cs->registerMetaTag(null, null, null, array('charset' => 'utf-8'));
	// Базовые стили
	$cs->registerCssFile('/css-new/generated/styles.css');
	$cs->registerCssFile('/css-new/generated/ext.css');
	$cs->registerCssFile('/css-new/generated/admin.css');
	$cs->registerCssFile('/css/jquery-ui-1.8.18.custom.css');


	// Базовые скирпты
	$cs->registerCoreScript('jquery');
	$cs->registerCoreScript('jquery.ui');
	//$cs->registerScriptFile('/js-new/less-1.3.3.min.js');
	$cs->registerScriptFile('/js-new/lib.js');
	$cs->registerScriptFile('/js-new/mod/Common.js');
	$cs->registerScriptFile('/js-new/common.js');
	$cs->registerScriptFile('/js-new/jquery.simplemodal.1.4.4.min.js');
	$cs->registerScriptFile('/js-new/swfobject.js');
	$cs->registerScriptFile('/js-new/admin.js');
	$cs->registerScriptFile('/js/scroll.js');

	// Старые стили и скрипты
	if ($this->getRoute() != 'site/index') {
		$cs->registerCssFile('/css/style.css');
		$cs->registerScriptFile('/js/f.js');
		$cs->registerScriptFile('/js/functions.js');
	}

	?>
</head>
<body <?php echo ($this->bodyClass) ? "class=\"{$this->bodyClass}\"" : '';?>>
	

<?php if (empty(Yii::app()->params->stopStatistic)) : ?>

<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-T3NSM"
		  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-T3NSM');</script>
<!-- End Google Tag Manager -->

<?php endif; ?>


<!-- top banner -->
<?php
// Виджет отвечает за вывод баннера в текущем разделе сайта
$this->widget('application.components.widgets.banner.BannerWidget', array(
    'controller'=>$this,
    'type'=>1
));
?>
<!-- eof top banner -->
<div class="-layout-page -with-banner with-banner">
	<!-- HEADER -->
	<div class="-grid-container -layout-header">
		<div class="-grid-wrapper">
			<div class="-grid">
				<div class="-col-2 -layout-header-logo"><?php
					if ($this->getRoute() != 'site/index')
						echo '<a class="-block" href="'.Yii::app()->homeUrl.'"><span class="-text-block">MyHome — интернет-помощник по ремонту и благоустройству Вашего дома</span></a>';
					else
						echo '<a class="-block"></a>';
				?></div>
				<div class="-col-5 -gutter-null">
					<?php $this->widget('application.components.widgets.WMenu.WMenu', array(
						'typeMenu'  => Menu::TYPE_MAIN,
						'viewName'  => 'gridMain',
						'showLevel' => 1,
						'activeKey' => $this->menuActiveKey,
						'activeLink'=> $this->menuIsActiveLink,
						'activeLinkOnlyParent'=> $this->menuIsActiveLinkOnlyParent,
					));?>
				</div>

				<div class="-col-3 -layout-header-search-form">
					<noindex>
					<form method="get" action="/search">
						<input type="text" name="q" value="">
					</form>
					</noindex>
				</div>
				<div class="-col-2 -layout-header-user">
					<?php $this->renderPartial('//site/gridPopupAuth'); ?>
				</div>

			</div>
		</div>
	</div>
	<!-- EOF HEADER -->





	<?php // Рендерим дополнительный контент
	echo $this->additionalContent;
	?>


	<div class="-grid-container page-content -layout-content -gutter-top-dbl">
		<div class="-grid-wrapper">
			<div class="-grid">


			<?php // === К О Н Т Е Н Т === ?>
			<?php
				if ($this->getRoute() != 'site/index')
					echo '<div class="-col-12">'.$content.'</div>';
				else
					echo $content;
			?>

			</div>
		</div>
	</div>

	<div class="-layout-footer-fix"></div>
</div>


<!--<div class="-feedback -button -button-orange">
	Обратная связь
</div>-->
<?php // Вывод ссылки в админку
$role = Yii::app()->getUser()->getRole();
if ( in_array($role, array_keys(Config::$rolesAdmin)) || (Yii::app()->session->get('REAL_ROLE', null)==User::ROLE_POWERADMIN ) ) : ?>
<div class="-inset-all -light-gray-bg admin-widget -relative hidden">
	<div class="-grid">
		<div class="-col-2 -gutter-bottom-dbl -inset-bottom-dbl">
			<p class="-gutter-bottom-hf -medium -semibold"><i class="-icon-user"></i>Центр управления</p>
			<a href="/admin">Войти</a>
		</div>
		<?php echo Cache::getInstance()->wSeoMetaTag; ?>
		<?php $this->widget('application.components.widgets.SeoRewrite.WSeoRewrite'); ?>

		<?php $this->widget('application.components.widgets.AdminRole.AdminWidget', array('isFront'=>true)); ?>
	</div>
	<span class="-pointer-right -huge -absolute"></span>
</div>
<?php endif; ?>


<?php  $this->renderPartial('//site/gridPopupFeedback'); ?>


<!-- FOOTER -->
<?php //<script src="http://ma-static.ru/sticker/26433.js" type="text/javascript"></script> ?>
<div class="-grid-container -layout-footer">
	<div class="-grid-wrapper">
		<div class="-grid">
			<div class="-col-3">
				<?php $this->widget('application.components.widgets.WMenu.WMenu', array(
					'typeMenu'  => Menu::TYPE_FOOTER,
					'viewName'  => 'gridFooter',
					'showLevel' => 1,
					'activeKey' => '',
					'activeLink'=> $this->menuIsActiveLink,
					'activeLinkOnlyParent'=> $this->menuIsActiveLinkOnlyParent,
				));?>
			</div>
			<div class="-col-3">
				<noindex>
				<?php $this->widget('application.components.widgets.WMenu.WMenu', array(
					'typeMenu'  => Menu::TYPE_FOOTER_CAT,
					'viewName'  => 'gridFooterAdd',
					'showLevel' => 1,
					'activeKey' => '',
					'activeLink'=> $this->menuIsActiveLink,
					'activeLinkOnlyParent'=> $this->menuIsActiveLinkOnlyParent,
				));?>
				</noindex>
			</div>
			<div class="-col-3">
				<noindex>
				<?php $this->widget('application.components.widgets.WMenu.WMenu', array(
					'typeMenu'  => Menu::TYPE_FOOTER_ADD,
					'viewName'  => 'gridFooterAdd',
					'showLevel' => 1,
					'activeKey' => '',
					'activeLink'=> $this->menuIsActiveLink,
					'activeLinkOnlyParent'=> $this->menuIsActiveLinkOnlyParent,
				));?>
				</noindex>
			</div>
			<div class="-col-3">
				<noindex>
					<p class="-small -gray -gutter-bottom-hf">ООО &laquo;Май Хоум&raquo;, 2011&#150;<?php echo date('Y'); ?><br>Все права
						защищены, 12+</p>

					<p class="-small -gray -gutter-bottom-dbl">Использование материалов myhome.ru без разрешения редакции сайта
						запрещено. <a href="<?php echo Yii::app()->homeUrl;?>/copyright">Авторские права</a>.</p>

					<p class="-gutter-bottom-hf -small -gray">Мы в соцсетях:</p>
					<p class="-gray -gutter-bottom-dbl">
						<a target="_blank" class="-icon-facebook" 	href="http://facebook.com/myhome.ru"></a>
						<a target="_blank" class="-icon-vkontakte" 	href="http://vkontakte.ru/myhomeru"></a>
						<a target="_blank" class="-icon-twitter" 	href="http://twitter.com/MyHomeRu"></a>
						<a target="_blank" class="-icon-google-plus" 	href="https://plus.google.com/111864486971130445417/posts"></a>
						<a target="_blank" class="-icon-odnoklassniki" 	href="http://www.odnoklassniki.ru/myhome"></a>
						<a target="_blank" class="-icon-pinme" 		href="http://pinme.ru/u/myhomeru/"></a>
					</p>

					<div class="-footer-counters">
						<?php if (empty(Yii::app()->params->stopStatistic)) : ?>
							<span id='top100counter' style="margin-right: 3px;"></span>
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
							<!-- HotLog -->
							<span style="margin-right: 3px;">
							<script type="text/javascript">
								hotlog_r=""+Math.random()+"&s=2305962&im=68&r="+
									escape(document.referrer)+"&pg="+escape(window.location.href);
								hotlog_r+="&j="+(navigator.javaEnabled()?"Y":"N");
								hotlog_r+="&wh="+screen.width+"x"+screen.height+"&px="+
									(((navigator.appName.substring(0,3)=="Mic"))?screen.colorDepth:screen.pixelDepth);
								hotlog_r+="&js=1.3";
								document.write('<a href="http://click.hotlog.ru/?2305962" target="_blank"><img '+
									'src="http://hit3.hotlog.ru/cgi-bin/hotlog/count?'+
									hotlog_r+'" border="0" width="88" height="31" title="" alt="HotLog"><\/a>');
							</script>
							<noscript>
								<a href="http://click.hotlog.ru/?2305962" target="_blank"><img
									src="http://hit3.hotlog.ru/cgi-bin/hotlog/count?s=2305962&im=68" border="0"
									width="88" height="31" title="" alt="HotLog"></a>
							</noscript>
							</span>
							<!-- /HotLog -->
							<!--LiveInternet counter--><script type="text/javascript"><!--
							document.write("<a href='http://www.liveinternet.ru/click;MyHome' "+
								"target=_blank><img src='//counter.yadro.ru/hit;MyHome?t44.6;r"+
								escape(document.referrer)+((typeof(screen)=="undefined")?"":
								";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
									screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
								";"+Math.random()+
								"' alt='' title='LiveInternet' "+
								"border='0' width='31' height='31'><\/a>")
							//--></script><!--/LiveInternet-->

							<div class="-small"><noindex><a rel="nofollow" href="http://www.rambler.ru/" target="_blank" style="color: #3f3f3f !important;">Партнер «Рамблера»</a></noindex></div>
						<?php endif; ?>
					</div>
				</noindex>
			</div>
		</div>
	</div>
</div>
<!-- EOF FOOTER -->

<?php if (empty(Yii::app()->params->stopStatistic)) : ?>

	<script type="text/javascript">
		var _gaq = _gaq || [];
			<?php if (Yii::app()->user->getIsGuest()) : ?>
		_gaq.push(['_setCustomVar',1,'User','Guest',2]);
			<?php else : ?>
		_gaq.push(['_setCustomVar',1,'User','Register',2]);
			<?php endif; ?>

		_gaq.push(['_trackPageview']);

	</script>

	<!--<noscript><div><img src="//mc.yandex.ru/watch/11382007" style="position:absolute; left:-9999px;" alt="" /></div></noscript>-->
<?php endif; ?>


<?php // Вывод блока для добавления в избранное @see AddToFavorite
echo $this->clips['addFavorite'];
?>

<?php // Вывод блока для добавления в папку @see AddToFolder
echo $this->clips['addFolder'];
?>


<?php
/* -----------------------------------------------------------------------------
 *  Скрытый <div> для хранения попапчиков.
 * -----------------------------------------------------------------------------
 */
?>
<noindex>
<div class="-hidden">
	<?php
	// Выводим попап для авторизации пользователя.
	// Формируется в //site/gridPopupAuth
	echo $this->clips['popupAuth'];
	?>

	<?php
	// Выводим попап для регистрации пользователя.
	// Формируется в //site/gridPopupAuth
	echo $this->clips['popupRegistration'];
	?>
</div>
</noindex>

<!--reformal.ru-->
<script type="text/javascript">
	var reformalOptions = {
		project_id: 115406,
		project_host: "www.myhome.ru",
		tab_orientation: "right",
		tab_indent: "50%",
		tab_bg_color: "#e33926",
		tab_border_color: "#FFFFFF",
		tab_image_url: "http://tab.reformal.ru/T9GC0LfRi9Cy0Ysg0Lgg0L%252FRgNC10LTQu9C%252B0LbQtdC90LjRjw==/FFFFFF/88128dfd6ca0743b5ccc2f8afed9f3b1/right/0/tab.png",
		tab_border_width: 0
	};

	(function() {
		var script = document.createElement('script');
		script.type = 'text/javascript'; script.async = true;
		script.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'media.reformal.ru/widgets/v3/reformal.js';
		document.getElementsByTagName('head')[0].appendChild(script);
	})();
</script><noscript><a href="http://reformal.ru"><img src="http://media.reformal.ru/reformal.png" /></a><a href="http://www.myhome.ru">Oтзывы и предложения для MyHome.ru – Все об интерьере дома: от идеи до воплощения!</a></noscript>
<!-- / reformal.ru-->

</body>
</html>