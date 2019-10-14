<!DOCTYPE html>
<html class="<?php
if (!empty($this->htmlClass)) {
	if (!is_array($this->htmlClass)) {
		$this->htmlClass = array($this->htmlClass);
	}
	echo implode(' ', $this->htmlClass);

} ?>">
<head>
	<meta charset="utf-8">
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
	<?php
	/** @var $cs CustomClientScript */
	$cs = Yii::app()->getClientScript();
	// Служебные метатеги
	$cs->registerMetaTag(CHtml::encode($this->description), 'description');
	$cs->registerMetaTag(CHtml::encode($this->keywords), 'keywords');
	//$cs->registerMetaTag(null, null, null, array('charset' => 'utf-8'));
	$cs->registerMetaTag('904ad3cb7f072c576881f8dfaead4a80', 'cmsmagazine');

	// Базовые стили
	$cs->registerCssFile('/css-new/generated/styles.css');
	$cs->registerCssFile('/css-new/generated/ext.css');
	$cs->registerCssFile('/css-new/generated/mini-site.css');
	$cs->registerCssFile('/css/jquery-ui-1.8.18.custom.css');

	// Базовые скирпты
	$cs->registerCoreScript('jquery');
	$cs->registerCoreScript('jquery.ui');
	$cs->registerScriptFile('/js-new/common.js');
	$cs->registerScriptFile('/js-new/jquery.simplemodal.1.4.4.min.js');
	$cs->registerScriptFile('/js-new/minisite.js');

	// Старые стили и скрипты
	$cs->registerScriptFile('/js/f.js');
	$cs->registerScriptFile('/js/functions.js');


	// Переносим параметры.
	$store = $this->layoutParams['store'];
	?>
</head>
<body class="<?php
if (!empty($this->bodyClass)) {
	if (!is_array($this->bodyClass)) {
		$this->bodyClass = array($this->bodyClass);
	}	
	echo implode(' ', $this->bodyClass);
	
} ?>">

<div class="-layout-page">

	<div id="myhomeHeader">
		<div class="-grid-container -layout-header">
			<div class="-grid-wrapper">
				<div class="-grid">
					<div class="-col-2 -layout-header-logo"><?php
						if ($this->getRoute() != 'site/index')
							echo '<a class="-block" href="' . Yii::app()->homeUrl . '"><span class="-text-block">MyHome — интернет-помощник по ремонту и благоустройству Вашего дома</span></a>';
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
					<!--noindex-->
					<div class="-col-3 -layout-header-search-form">
						<form method="get" action="/search">
							<input type="text" name="q" value="">
						</form>
					</div>
					<div class="-col-2 -layout-header-user">

						<?php $this->renderPartial('//site/gridPopupAuth'); ?>

					</div>
					<!--/noindex-->
				</div>
			</div>
		</div>
	</div>

	<?php // --- Шапка с редактированием --- ?>

	<div class="-grid-container -header">
		<div class="-grid-wrapper title-image">
			<?php
			if ($store->headImage) {
				echo CHtml::image(
					'/' . $store->headImage->getPreviewName(Store::$preview['crop_1000_230']),
					$store->name,
					array('class' => 'header-bg')
				);
			} else {
				echo '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="'.$store->name.'">';
			}
			?>

			<?php
			/* -----------------------------------------------------
			 *  Редактирование шапки. Для владельца.
			 * -----------------------------------------------------
			 */
			if ($store->isOwner(Yii::app()->user->id)) { ?>

				<div class="-col-4 -inset-all -hidden header-edit">
					<p class="-small">Переместите изображение в нужное положение</p>
					<span class="-button -button-skyblue">Сохранить</span>
					<a class="-gray -gutter-left">Отменить</a>
				</div>
				<div class="site-owner">

					<div class="-inline -opaque-box">
						<span class="-icon-header-xs -gray -small -underline">Обновить шапку</span>
				<span class="-icon-upload-xs -gray -small -underline -hidden" id="uploadHeaderBg">
					Загрузить
					<input type="file" class="header-input" />
				</span>
						<span class="-icon-cross-circle-xs -gray -small -underline -hidden" id="removeHeaderBg">Удалить</span>
					</div>
					<div class="-inline -opaque-box">
						<span class="-icon-bg-xs -gray -small -underline" id="changeBg">Обновить фон</span>
					</div>
				</div>

			<?php } ?>

		</div>
		<div class="-grid-wrapper">
			<div class="-grid">
				<div class="-absolute">
					<?php
					if ($store->uploadedFile) {
						echo CHtml::image(
							'/' . $store->uploadedFile->getPreviewName(Config::$preview['crop_130']),
							$store->name
						);
					}
					?>

					<?php
					/* -------------------------------------
					 *  Редактирование лого. Для владельца.
					 * -------------------------------------
					 */
					if ($store->isOwner(Yii::app()->user->id)) { ?>

						<div class="site-owner">
						<span class="-icon-pencil-xs -pseudolink -gray -small">
							<i>Редактировать лого</i>
							<input type="file"
							       class="logo-input"
							       data-url="<?php echo $this->createUrl('/catalog/profile/storeUpdate', array('id' => $store->id));?>" />
						</span>
						</div>

					<?php } ?>

				</div>
				<div class="-col-9 -skip-3">
					<ul class="-menu-inline -justified-menu">
						<?php $cls = ($this->action->id == 'moneyIndex') ? 'current' : ''; ?>
						<li class="<?php echo $cls;?>">
							<a href="<?php echo Store::getLink($store->id, 'moneyAbout');?>">Описание</a>
						</li>

						<?php $cls = ($this->action->id == 'moneyProducts') ? 'current' : ''; ?>
						<li class="<?php echo $cls;?>">
							<a href="<?php echo Store::getLink($store->id, 'moneyProducts');?>">Товары</a>
							<span><?php echo $store->productQt;?></span>
						</li>

						<?php $cls = ($this->action->id == 'moneyFeedback') ? 'current' : ''; ?>
						<li class="<?php echo $cls;?>">
							<a href="<?php echo Store::getLink($store->id, 'moneyFeedback');?>">Отзывы</a>
						</li>

						<?php $cls = ($this->action->id == 'moneyNews' || $this->action->id == 'moneyNewsDetail') ? 'current' : ''; ?>
						<li class="<?php echo $cls;?>">
							<a href="<?php echo StoreNews::getLink($store, 'list');?>">Новости и акции</a>
						</li>

						<?php $cls = ($this->action->id == 'moneyGallery') ? 'current' : ''; ?>
						<li class="<?php echo $cls;?>">
							<a href="<?php echo Store::getLink($store->id, 'moneyFotos');?>">Фотогалерея</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<script>
		minisite.layoutActions()
	</script>

	<div class="-grid-container -layout-content">

		<?php // --- Здесь КОНТЕНТ --- ?>

		<?php echo $content; ?>

	</div>
	<div class="-layout-footer-fix"></div>
</div>

<!-- FOOTER -->
<div class="-grid-container -layout-footer">
	<div class="-grid-wrapper">
		<div class="-grid">
			<div class="-col-4">
				<ul class="-menu-block -gray">
					<li>
						&copy; <?php echo $store->name;?>
						<?php if (!empty($store->activity)) echo '— ' . $store->activity;?>
					</li>
					<li>
						<?php
						$city = $store->getCityOfflineStore();
						if ($city) {
						echo $city->country->name . ', г. ' . $city->name . '<br>' . $store->address;
						}
						?>
						<br>
						<?php echo $store->phone;?>
					</li>
					<li>
						<?php
						if ($store->email) {
							echo CHtml::link($store->email, 'mailto:' . $store->email);
						}
						?>
						<br>
						<?php
						if ($store->site) {
							echo CHtml::link(
								Amputate::absoluteUrl($store->site),
								$this->createUrl(
									'viewSite',
									array(
										'store_id' => $store->id
									)
								),
								array('target' => '_blank', 'class' => '-red')
							);
						}
						?>
					</li>
				</ul>
			</div>
			<div class="-col-5">
				<ul class="-menu-block -gray">
					<li><a href="<?php echo Store::getLink($this->layoutParams['store']->id, 'moneyAbout');?>">Описание</a></li>
					<li><a href="<?php echo Store::getLink($this->layoutParams['store']->id, 'moneyProducts');?>">Товары</a></li>
					<li><a href="<?php echo Store::getLink($this->layoutParams['store']->id, 'moneyFeedback');?>">Отзывы</a></li>
					<li><a href="<?php echo Store::getLink($this->layoutParams['store']->id, 'moneyNews');?>">Новости и акции</a></li>
					<li><a href="<?php echo Store::getLink($this->layoutParams['store']->id, 'moneyFotos');?>">Фотогалерея</a></li>
				</ul>
			</div>
			<div class="-col-3">
				<img src="/img-new/mini-site/bottom-logo.gif">
				<p class="-small -gray">Сайт создан на платформе <a href="#">MyHome</a></p>
				<div class="-inline -gutter-top-hf -opacity-50">
					<?php if (empty(Yii::app()->params->stopStatistic)) : ?>
						<!--LiveInternet counter-->
						<script type="text/javascript">
							document.write("<a href='http://www.liveinternet.ru/click;MyHome' "+
								"target=_blank><img src='//counter.yadro.ru/hit;MyHome?t18.2;r"+
								escape(document.referrer)+((typeof(screen)=="undefined")?"":
								";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
									screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
								";"+Math.random()+
								"' alt='' title='LiveInternet: показано число просмотров за 24"+
								" часа, посетителей за 24 часа и за сегодня' "+
								"border='0' width='88' height='31'><\/a>")
						</script>
						<!--/LiveInternet-->
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- EOF FOOTER -->


<?php // Вывод блока для добавления в избранное @see AddToFavorite
echo $this->clips['addFavorite'];
?>


<noindex>
	<div class="-hidden">
	<?php
	// Выводим попап для авторизации пользователя.
	// Формируется в //site/gridPopupAuth
	echo $this->clips['popupAuth'];
	?>
	</div>
</noindex>

<div class="-col-9 bg-palette -hidden">
	<h2 class="-giant -inset-bottom-hf">Выберите фон</h2>
	<div class="-grid">
		<?php // --- Набор фоновых изображений для магазина --- ?>
		<?php if (isset(Store::$bgClasses) && !empty(Store::$bgClasses)) { ?>
			<?php foreach (Store::$bgClasses as $item) { ?>
				<?php $cls = ($item == $store->bg_class) ? 'current' : ''; ?>
				<div class="-col-2"><span data-bgClass="<?php echo $item;?>" class="<?php echo $item;?> <?php echo $cls;?>"></span></div>
			<?php } ?>
		<?php } ?>
		<div class="-col-6 -skip-2 -gutter-top">
			<button class="-button -button-skyblue -huge -semibold -gutter-right">Сохранить</button><a href="#" class="-gray -large -gutter-left">Отмена</a>
		</div>
	</div>
</div>



</body>
</html>