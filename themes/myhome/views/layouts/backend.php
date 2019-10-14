<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

	<?php /*
		<link rel="stylesheet/less" href="<?php echo Yii::app()->request->baseUrl; ?>/css/admin/less/bootstrap.less" />
		<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/less-1.1.5.min.js"></script>
		 */?>

	<?php echo Yii::app()->bootstrap->registerBootstrap(); ?>
	<link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/admin/add.css" />
	<?php
	/** @var $cs CClientScript */
	$cs = Yii::app()->getClientScript();
	$cs->registerCoreScript('jquery');
	$cs->registerScriptFile('/js/admin.js');
	$cs->registerScriptFile('/js-new/common.js');
	?>

	<?php echo Yii::app()->bootstrap->registerBootstrap(); ?>
	<link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/admin/add.css" />
	<?php Yii::app()->getClientScript()->registerCoreScript('jquery');?>


	<title><?php echo CHtml::encode($this->pageTitle); ?></title>

	<style>
		body { padding-top: 60px; }
		.container-fluid .content { position: relative; min-height: 1025px; padding-bottom: 80px; }
		.container-fluid .content footer { position: absolute; bottom: 15px; }
	</style>
</head>

<body>

<div class="topbar">
	<div class="topbar-inner">
		<div class="container-fluid">
			<a class="brand" href="/"><?php echo CHtml::encode(Yii::app()->name); ?></a>

			<?php
			$this->widget('zii.widgets.CMenu', array(
				'items' => array(
					array('label' => 'Главная', 'url' => array('/admin/')),
					array('label' => 'Регистрация пользователя', 'url' => array('/site/userregistration'), 'visible' => Yii::app()->user->isGuest),
					array('label' => 'Регистрация магазина', 'url' => array('/site/shopregistration'), 'visible' => Yii::app()->user->isGuest),
					array('label' => 'Вход', 'url' => array('/site/login'), 'visible' => Yii::app()->user->isGuest),
					array('label' => 'Панель администрирования', 'url' => array('/admin'), 'visible' => Yii::app()->user->checkAccess(User::ROLE_ADMIN)),
				),
			));
			?>

			<?php
			/* --------------------------------------------------------------
			 *  Автокомплит для поиска пунктов меню для быстрой навигации
			 * --------------------------------------------------------------
			 */
			?>
			<form class="pull-left" action="" style="position: relative;">
				<?php
				$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
					'name'=>'menu_search',
					'source'=>'js:function(request, response){
								var inText = $("#menu_search").val().toLowerCase();
								var ul = new Array();
								$(".adminMenu li a").each(function(index, element){
									var t = $(element).text().toLowerCase();

									if (t.match(inText))
										ul.push({
											label:$(element).text()+" — ("+$(element).attr("href")+")",
											value:$(element).text(),
											url:$(element).attr("href")
										});
								});
								response(ul);
								return;
							}',
					// additional javascript options for the autocomplete plugin
					'options'=>array(
						'minLength'=>'1',
						'select' => 'js:function(event, ui){
									document.location.href = ui.item["url"];
								}'
					),
					'htmlOptions'=>array(
						'style'=>'height:20px;',
						'autofocus' => 'autofocus',
					),
				));
				?>
			</form>

			<?php if ($this->module->id == 'catalog') : ?>
				<?php Yii::import('application.modules.catalog.models.GroupOperation'); ?>
				<a href="/catalog/admin/groupOperation/" class="btn info product_cart" id="product_cart">В корзине товаров: <span class="qt"><?php echo GroupOperation::model()->count();?></span> шт.</a>
			<?php endif; ?>


			<p class="pull-right">


				Вошел как <a href="<?php echo Yii::app()->user->model->getLinkProfile();?>"><?php echo Yii::app()->user->model->login;?></a>
				<a href="/site/logout">(выйти)</a>
			</p>
		</div>
	</div>
</div>

<div class="container-fluid">s

<div class="sidebar"><div class="well">
<?php if ($this->beginCache('adminMenuMain', array('duration' => Cache::DURATION_ADMIN_MENU, 'varyByExpression' => 'Yii::app()->user->role'))) { ?>

	<?php
	$this->widget('CMenu', array(
		'htmlOptions' => array('class' => 'adminMenu'),
		'activateParents' => true,
		'items' => array(
			array(
				'label'   => 'Главная страница',
				'url'     => array('/admin/mainpage/index'),
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_JOURNALIST,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER
				)),
				'items'   => array(
					array(
						'label'   => 'Дизайнеры и архитекторы',
						'url'     => array('/admin/unit/designer/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_JOURNALIST,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN
						)),
					),
					array(
						'label'   => 'Идеи для вашего интерьера',
						'url'     => array('/admin/unit/idea/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_JUNIORMODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN
						)),
					),
					array(
						'label'   => 'Товары',
						'url'     => array('/admin/unit/unitProduct/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_JUNIORMODERATOR,
							User::ROLE_JOURNALIST,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN
						)),
						'active'  => $this->id == 'unit/unitProduct'
					),
					array(
						'label' => 'Промоблок товаров',
						'items' => array(
							array(
								'label'   => 'Табы',
								'url'     => array('/admin/indexProductTab/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_JOURNALIST,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN,
									User::ROLE_SALEMANAGER
								)),
							),
							array(
								'label'   => 'Изображения',
								'url'     => array('/admin/indexProductPhoto/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_JOURNALIST,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN
								)),
							),
							array(
								'label'   => 'Бренды (логотипы)',
								'url'     => array('/admin/indexProductBrand/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_JOURNALIST,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN,
									User::ROLE_SALEMANAGER
								)),
							),
						)
					),
					array(
						'label' => 'Блок идей',
						'items' => array(
							array(
								'label'   => 'Ссылки',
								'url'     => array('/admin/indexIdeaLink/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_JOURNALIST,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN,
									User::ROLE_SALEMANAGER,
								)),
							),
							array(
								'label'   => 'Изображения',
								'url'     => array('/admin/indexIdeaPhoto/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_JOURNALIST,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN
								)),
							),
						)
					),
					array(
						'label' => 'Блок специалистов',
						'items' => array(
							array(
								'label'   => 'Ссылки',
								'url'     => array('/admin/indexSpecBlock/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_JOURNALIST,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN,
									User::ROLE_SALEMANAGER
								)),
							),
							array(
								'label'   => 'Изображения',
								'url'     => array('/admin/indexSpecPhoto/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_JOURNALIST,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN
								)),
							),
						)
					),
				)
			),
			array(
				'label'   => 'Пользователи',
				'url'     => array('/admin/user/userlist'),
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER
				)),
				'items'   => array(
					array(
						'label'   => 'Администраторы',
						'url'     => array('/admin/user/adminlist'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN
						)),
					),
					array(
						'label'   => 'Пользователи сайта',
						'url'     => array('/admin/user/userlist'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER
						)),
					),

					array(
						'label'   => 'Тарифы специалисты',
						'url'     => array('/member/admin/specialistRate'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER
						)),
					),

					array(
						'label'   => 'Оплата тарифов',
						'url'     => array('/member/admin/userServicePriority'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER
						)),
					),
					array(
						'label'   => 'Группы пользователей',
						'url'     => array('/member/admin/usergroup/admin'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER
						)),
					),
					array(
						'label'   => 'Отправка инвайтов (регистрация)',
						'url'     => array('/admin/user/invite'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER
						)),
					),
					array(
						'label'   => 'Жалобы на спам',
						'url'     => array('/member/admin/spam'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN,
							User::ROLE_SENIORMODERATOR,
						)),
					),
					array(
						'label'   => 'Журнал активаций',
						'url'     => array('/admin/user/activatelog'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
						)),
					),
				)
			),
			array(
				'label'   => 'Отзывы и рек-ции',
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
				)),
				'items' => array(
					array(
						'label'   => 'О специалистах',
						'url'     => array('/member/admin/review/list'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
						)),
					),
					array(
						'label'   => 'О магазинах',
						'url'     => array('/catalog/admin/storeFeedback/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
						)),
					),
					array(
						'label'   => 'О товарах',
						'url'     => array('/catalog/admin/productFeedback/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
						)),
					),
				),
			),

			array(
				'label' => 'Спецпредложения',
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_MODERATOR,
				)),
				'items' => array(
					array(
						'label' => 'Папки и миры',
						'active'  => $this->module->id == 'catalog' && $this->id == 'admin/catFolders',
						'url' => array('/catalog/admin/catFolders'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
						)),
					),
				),
			),

			array(
				'label' => 'Раздел реклама',
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_SALEMANAGER,
				)),
				'items' => array(
					array(
						'label' => 'Заявки на магазин',
						'active'  => $this->id == 'admin/storeOffer',
						'url' => array('/catalog/admin/storeOffer'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
						)),
					),
					array(
						'label' => 'Вопросы от пользователей',
						'active'  => $this->id == 'admin/advquestion',
						'url' => array('/content/admin/advquestion'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
						)),
					),
				),
			),
			array(
				'label'   => 'Каталог товаров',
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_FREELANCE_PRODUCT,
					User::ROLE_FREELANCE_STORE,
				)),
				'items'   => array(
					array(
						'label'   => 'Товары',
						'url'     => array('/catalog/admin/category/index'),
						'active'  => $this->module->id == 'catalog' && ($this->id == 'admin/category' || $this->id == 'admin/product'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
							User::ROLE_FREELANCE_PRODUCT,
						)),
					),
					array(
						'label'   => 'Производители',
						'url'     => array('/catalog/admin/vendor/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
							User::ROLE_FREELANCE_STORE,
							User::ROLE_FREELANCE_PRODUCT,
						)),
					),
					array(
						'label'   => 'Магазины',
						'url'     => array('/catalog/admin/store/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
							User::ROLE_FREELANCE_STORE,
							User::ROLE_FREELANCE_PRODUCT,

						)),
						'active'  => $this->module->id == 'catalog' && $this->id == 'admin/store',
						'items'   => array(
							array(
								'label'   => 'Новости магазинов',
								'url'     => array('/catalog/admin/storeNews/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_MODERATOR,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN,
									User::ROLE_SALEMANAGER,
									User::ROLE_FREELANCE_STORE,
									User::ROLE_FREELANCE_PRODUCT,

								)),
								'active'  => $this->module->id == 'catalog' && $this->id == 'admin/storeNews'
							),
							array(
								'label'   => 'Галереи магазинов',
								'url'     => array('/catalog/admin/storeGallery/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_MODERATOR,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN,
									User::ROLE_SALEMANAGER,
									User::ROLE_FREELANCE_STORE,
									User::ROLE_FREELANCE_PRODUCT,

								)),
								'active'  => $this->module->id == 'catalog' && $this->id == 'admin/storeGallery'
							),
						)
					),
					array(
						'label'   => 'Сети магазинов',
						'url'     => array('/catalog/admin/chain/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_FREELANCE_STORE,
							User::ROLE_FREELANCE_PRODUCT,
						)),
					),
					array(
						'label'   => 'Контрагенты',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_POWERADMIN,
							User::ROLE_ADMIN,
							User::ROLE_SALEMANAGER,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_MODERATOR,
						)),
						'active'  =>$this->module->id == 'catalog' &&  $this->id == 'admin/contractor',
						'url'     => array('/catalog/admin/contractor/index'),
					),
					array(
						'label'   => 'ТЦ',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_POWERADMIN,
							User::ROLE_ADMIN,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_MODERATOR,
						)),
						'active'  => $this->module->id == 'catalog' && $this->id == 'admin/mallBuild',
						'url'     => array('/catalog/admin/mallBuild/index'),
						'items'   => array(
							array(
								'label'   => 'Услуги в ТЦ',
								'url'     => array('/catalog/admin/mallService/index'),
								'active'  => $this->id == 'admin/mallService',
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_POWERADMIN,
									User::ROLE_ADMIN,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_MODERATOR,
								)),
							),
						)
					),
					array(
						'label'   => 'CSV',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER
						)),
						'active'  => $this->module->id == 'catalog' && $this->id == 'admin/catCsv',
						'items'   => array(
							array(
								'label'   => 'Экспорт по производителям',
								'url'     => array('/catalog/admin/catCsv/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_MODERATOR,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN,
									User::ROLE_SALEMANAGER
								)),
							),
							array(
								'label'   => 'Список заданий',
								"url"     => array('/catalog/admin/catCsv/list'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_MODERATOR,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN,
									User::ROLE_SALEMANAGER
								)),
							),
						)
					),
					array(
						'label'   => 'Цвета',
						'url'     => array('/catalog/admin/color/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN
						)),
					),
					array(
						'label'   => 'Стили',
						'url'     => array('/catalog/admin/style/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN
						)),
					),
					array(
						'label'   => 'Управление аносами',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_POWERADMIN,
							User::ROLE_ADMIN,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_MODERATOR,
							User::ROLE_SALEMANAGER,
						)),
						'items'   => array(
							array(
								'label'   => 'Помещения',
								'url'     => array('/catalog/admin/mainroom/index'),
								'active'  => $this->id == 'admin/mainroom',
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_POWERADMIN,
									User::ROLE_ADMIN,
									User::ROLE_SENIORMODERATOR,
								)),
							),
							array(
								'label'   => 'Бренды',
								'url'     => array('/catalog/admin/mainvendor/index'),
								'active'  => $this->id == 'admin/mainvendor',
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_POWERADMIN,
									User::ROLE_ADMIN,
									User::ROLE_SENIORMODERATOR,
								)),
							),
							array(
								'label'   => 'Предложения',
								'url'     => array('/catalog/admin/mainproduct/index'),
								'active'  => $this->id == 'admin/mainproduct',
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_POWERADMIN,
									User::ROLE_ADMIN,
									User::ROLE_SENIORMODERATOR,
								)),
							),
							array(
								'label'   => 'Идеи',
								'url'     => array('/catalog/admin/mainidea/index'),
								'active'  => $this->module->id == 'catalog' && $this->id == 'admin/mainidea',
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_POWERADMIN,
									User::ROLE_ADMIN,
									User::ROLE_SENIORMODERATOR,
								)),
							),
							array(
								'label'   => 'Лента логотипов',
								'url'     => array('/catalog/admin/tapestore/index'),
								'active'  => $this->module->id == 'catalog' && $this->id == 'admin/tapestore',
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_POWERADMIN,
									User::ROLE_ADMIN,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_MODERATOR,
									User::ROLE_SALEMANAGER,
									User::ROLE_SALEMANAGER,
								)),
							),
						),
					),
				)
			),
            array(
                'label'   => 'Каталог товаров 2',
                'visible' => Yii::app()->user->checkaccess(array(
                    User::ROLE_ADMIN,
                    User::ROLE_MODERATOR,
                    User::ROLE_SENIORMODERATOR,
                    User::ROLE_POWERADMIN,
                    User::ROLE_SALEMANAGER,
                    User::ROLE_FREELANCE_PRODUCT,
                    User::ROLE_FREELANCE_STORE,
                )),
                'items'   => array(
                    array(
                        'label'   => 'Товары',
                        'url'     => array('/catalog2/admin/category/index'),
                        'active'  => $this->module->id == 'catalog2' && ($this->id == 'admin/category' || $this->id == 'admin/product'),
                        'visible' => Yii::app()->user->checkaccess(array(
                            User::ROLE_ADMIN,
                            User::ROLE_MODERATOR,
                            User::ROLE_SENIORMODERATOR,
                            User::ROLE_POWERADMIN,
                            User::ROLE_SALEMANAGER,
                            User::ROLE_FREELANCE_PRODUCT,
                        )),
                    ),
                    array(
                        'label'   => 'Производители',
                        'url'     => array('/catalog2/admin/vendor/index'),
                        'visible' => Yii::app()->user->checkaccess(array(
                            User::ROLE_ADMIN,
                            User::ROLE_MODERATOR,
                            User::ROLE_SENIORMODERATOR,
                            User::ROLE_POWERADMIN,
                            User::ROLE_SALEMANAGER,
                            User::ROLE_FREELANCE_STORE,
                            User::ROLE_FREELANCE_PRODUCT,
                        )),
                    ),
                    array(
                        'label'   => 'Магазины',
                        'url'     => array('/catalog2/admin/store/index'),
                        'visible' => Yii::app()->user->checkaccess(array(
                            User::ROLE_ADMIN,
                            User::ROLE_MODERATOR,
                            User::ROLE_SENIORMODERATOR,
                            User::ROLE_POWERADMIN,
                            User::ROLE_SALEMANAGER,
                            User::ROLE_FREELANCE_STORE,
                            User::ROLE_FREELANCE_PRODUCT,

                        )),
                        'active'  => $this->module->id == 'catalog2' && $this->id == 'admin/store',
                        'items'   => array(
                            array(
                                'label'   => 'Новости магазинов',
                                'url'     => array('/catalog2/admin/storeNews/index'),
                                'visible' => Yii::app()->user->checkaccess(array(
                                    User::ROLE_ADMIN,
                                    User::ROLE_MODERATOR,
                                    User::ROLE_SENIORMODERATOR,
                                    User::ROLE_POWERADMIN,
                                    User::ROLE_SALEMANAGER,
                                    User::ROLE_FREELANCE_STORE,
                                    User::ROLE_FREELANCE_PRODUCT,

                                )),
                                'active'  => $this->module->id == 'catalog2' && $this->id == 'admin/storeNews'
                            ),
                            array(
                                'label'   => 'Галереи магазинов',
                                'url'     => array('/catalog2/admin/storeGallery/index'),
                                'visible' => Yii::app()->user->checkaccess(array(
                                    User::ROLE_ADMIN,
                                    User::ROLE_MODERATOR,
                                    User::ROLE_SENIORMODERATOR,
                                    User::ROLE_POWERADMIN,
                                    User::ROLE_SALEMANAGER,
                                    User::ROLE_FREELANCE_STORE,
                                    User::ROLE_FREELANCE_PRODUCT,

                                )),
                                'active'  => $this->module->id == 'catalog2' && $this->id == 'admin/storeGallery'
                            ),
                        )
                    ),
                    array(
                        'label'   => 'Сети магазинов',
                        'url'     => array('/catalog2/admin/chain/index'),
                        'visible' => Yii::app()->user->checkaccess(array(
                            User::ROLE_ADMIN,
                            User::ROLE_MODERATOR,
                            User::ROLE_SENIORMODERATOR,
                            User::ROLE_POWERADMIN,
                            User::ROLE_FREELANCE_STORE,
                            User::ROLE_FREELANCE_PRODUCT,
                        )),
                    ),
                    array(
                        'label'   => 'CSV',
                        'visible' => Yii::app()->user->checkaccess(array(
                            User::ROLE_ADMIN,
                            User::ROLE_MODERATOR,
                            User::ROLE_SENIORMODERATOR,
                            User::ROLE_POWERADMIN,
                            User::ROLE_SALEMANAGER
                        )),
                        'active'  => $this->module->id == 'catalog2' && $this->id == 'admin/catCsv',
                        'items'   => array(
                            array(
                                'label'   => 'Экспорт по производителям',
                                'url'     => array('/catalog2/admin/catCsv/index'),
                                'visible' => Yii::app()->user->checkaccess(array(
                                    User::ROLE_ADMIN,
                                    User::ROLE_MODERATOR,
                                    User::ROLE_SENIORMODERATOR,
                                    User::ROLE_POWERADMIN,
                                    User::ROLE_SALEMANAGER
                                )),
                            ),
                            array(
                                'label'   => 'Список заданий',
                                "url"     => array('/catalog2/admin/catCsv/list'),
                                'visible' => Yii::app()->user->checkaccess(array(
                                    User::ROLE_ADMIN,
                                    User::ROLE_MODERATOR,
                                    User::ROLE_SENIORMODERATOR,
                                    User::ROLE_POWERADMIN,
                                    User::ROLE_SALEMANAGER
                                )),
                            ),
                        )
                    ),
                    array(
                        'label'   => 'Цвета',
                        'url'     => array('/catalog2/admin/color/index'),
                        'visible' => Yii::app()->user->checkaccess(array(
                            User::ROLE_ADMIN,
                            User::ROLE_MODERATOR,
                            User::ROLE_SENIORMODERATOR,
                            User::ROLE_POWERADMIN
                        )),
                    ),
                    array(
                        'label'   => 'Стили',
                        'url'     => array('/catalog2/admin/style/index'),
                        'visible' => Yii::app()->user->checkaccess(array(
                            User::ROLE_ADMIN,
                            User::ROLE_MODERATOR,
                            User::ROLE_SENIORMODERATOR,
                            User::ROLE_POWERADMIN
                        )),
                    ),
                )
            ),
			array(
				'label' => 'Баннеры',
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_SALEMANAGER,
					User::ROLE_MODERATOR,
				)),
				'items' => array(
					array(
						'label' => 'Список баннеров',
						'url' => array('/admin/banner/'),
					),
					array(
						'label' => 'Новый баннер',
						'url' => array('/admin/banner/create'),
					),
				),
			),
			array(
				'label'   => 'Услуги',
				'url'     => array('/member/admin/service/index'),
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
				)),
				'items'   => array(
					array(
						'label'   => 'Добавление услуги',
						'url'     => array('/member/admin/service/create'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN
						)),
					),
				)
			),
			array(
				'label'   => 'Идеи',
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_JOURNALIST,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_FREELANCE_IDEA,
				)),
				'items'   => array(
					array(
						'label'   => 'Интерьеры',
						'url'     => array('/idea/admin/interior/list'),
						'active'  => $this->id == 'admin/interior' || $this->id == 'admin/create',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_FREELANCE_IDEA,
							User::ROLE_JOURNALIST,
						)),
					),
					array(
						'label'   => 'Интерьеры (обществ.)',
						'url'     => array('/idea/admin/interiorpublic/list'),
						'active'  => $this->id == 'admin/interiorpublic',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_JOURNALIST,
							User::ROLE_FREELANCE_IDEA,
						)),
					),
					array(
						'label'   => 'Архитектура',
						'url'     => array('/idea/admin/architecture/list'),
						'active'  => $this->id == 'admin/architecture',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_JOURNALIST,
							User::ROLE_POWERADMIN,
						)),
					),
					array(
						'label'   => 'Портфолио',
						'url'     => array('/idea/admin/portfolio/index'),
						'active'  => $this->id == 'admin/portfolio',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
						)),
					),
					array(
						'label'   => 'Свойства идей',
						'url'     => array('/idea/admin/property/index'),
						'active'  => $this->id == 'admin/property',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN
						)),
					),
				)
			),
			array(
				'label'   => 'Помощь',
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_JOURNALIST,
				)),
				'items'   => array(
					array(
						'label'   => 'Владельцам квартир',
						'url'     => array('/help/admin/help/list/base/1'),
						'visible' => Yii::app()->user->checkAccess(array(
							User::ROLE_POWERADMIN,
							User::ROLE_ADMIN,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_JOURNALIST,
						)),
					),
					array(
						'label'   => 'Специалистам',
						'url'     => array('/help/admin/help/list/base/2'),
						'visible' => Yii::app()->user->checkAccess(array(
							User::ROLE_POWERADMIN,
							User::ROLE_ADMIN,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_JOURNALIST,
						)),
					),
					array(
						'label'   => 'Магазинам',
						'url'     => array('/help/admin/help/list/base/3'),
						'visible' => Yii::app()->user->checkAccess(array(
							User::ROLE_POWERADMIN,
							User::ROLE_ADMIN,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_JOURNALIST,
						)),
					),
				),
			),
			array(
				'label'   => 'Заказы',
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_POWERADMIN,
					User::ROLE_ADMIN,
					User::ROLE_SALEMANAGER,
					User::ROLE_MODERATOR,
				)),
				'items'   => array(
					array(
						'label'   => 'Список заказов',
						'url'     => array('/tenders/admin/tender/list/'),
						'visible' => Yii::app()->user->checkAccess(array(
							User::ROLE_POWERADMIN,
							User::ROLE_ADMIN,
							User::ROLE_SALEMANAGER,
							User::ROLE_MODERATOR,
						)),
					),
					array(
						'label'   => 'Список откликов',
						'url'     => array('/tenders/admin/response/list/'),
						'visible' => Yii::app()->user->checkAccess(array(
							User::ROLE_POWERADMIN,
							User::ROLE_ADMIN,
							User::ROLE_SALEMANAGER,
							User::ROLE_MODERATOR,
						)),
					),
				),
			),
			array(
				'label'   => 'Управление почтой',
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
				)),
				'items'   => array(
					array(
						'label'   => 'Новый шаблон сообщений',
						'url'     => array('/admin/mailtemplate/create'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
						)),
					),
					array(
						'label'   => 'Шаблоны сообщений',
						'url'     => array('/admin/mailtemplate/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
						)),
					),
					array(
						'label'   => 'Рассылки',
						'url'     => array('/admin/mailer/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
						)), // FIXEME: rule
					),
				)
			),
			array(
				'label'   => 'Отчеты',
				//'url'	  => array('/admin/report/index'),
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_SALEMANAGER,
				)),
				'items'   => array(
					array(
						'label'   => 'Сводный по городам',
						'url'     => array('/admin/report/show/type/1'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
						)),
					),
					array(
						'label'   => 'Т. по городам',
						'url'     => array('/admin/report/show/type/2'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
						)),
					),
					array(
						'label'   => 'Т. по магазинам',
						'url'     => array('/admin/report/show/type/3'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
						)),
					),
					array(
						'label'   => 'Т. по производителям',
						'url'     => array('/admin/report/show/type/4'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
						)),
					),
					array(
						'label'   => 'Т. по контрагентам',
						'url'     => array('/admin/report/show/type/5'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
						)),
					),
					array(
						'label'   => 'По специалистам',
						'url'     => array('/admin/report/show/type/6'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
						)),
					),
					array(
						'label'   => 'По просмотрам магазинов',
						'url'     => array('/admin/report/show/type/7'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_SALEMANAGER,
						)),
					),
				),


			),
			array(
				'label'   => 'Вакансии',
				'url'     => array('/admin/vacancies/index'),
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN
				)),
				'items'   => array(
					array(
						'label'   => 'Список вакансий',
						'url'     => array('/admin/vacancies/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN
						)),
					),
					array(
						'label'   => 'Добавить вакансию',
						'url'     => array('/admin/vacancies/create'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN
						)),
					),
				)
			),
			array(
				'label'   => 'Медиа материалы',
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_SENIORMODERATOR,
					User::ROLE_POWERADMIN,
					User::ROLE_JOURNALIST
				)),
				'items'   => array(
					array(
						'label'   => 'Тематики',
						'url'     => array('/media/admin/mediaTheme/index'),
						'active'  => $this->id == 'admin/mediaTheme',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN
						)),
					),
					array(
						'label'   => 'Типы событий',
						'url'     => array('/media/admin/mediaEventType/index'),
						'active'  => $this->id == 'admin/mediaEventType',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_JOURNALIST
						)),
					),
					array(
						'label'   => 'Знания',
						'url'     => array('/media/admin/mediaKnowledge/index'),
						'active'  => $this->id == 'admin/mediaKnowledge',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_JOURNALIST
						)),
					),
					array(
						'label'   => 'Новости',
						'url'     => array('/media/admin/mediaNew/index'),
						'active'  => $this->id == 'admin/mediaNew',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_JOURNALIST
						)),
					),
					array(
						'label'   => 'События',
						'url'     => array('/media/admin/mediaEvent/index'),
						'active'  => $this->id == 'admin/mediaEvent',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_JOURNALIST
						)),
					),
					array(
						'label'   => 'Промоблок',
						'url'     => array('/media/admin/mediaPromo/index'),
						'active'  => $this->id == 'admin/mediaPromo',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_JOURNALIST
						)),
					),
					array(
						'label'   => 'Люди говорят',
						'url'     => array('/media/admin/mediaPeople/index'),
						'active'  => $this->id == 'admin/mediaPeople',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN,
							User::ROLE_JOURNALIST
						)),
					),
				),
			),
			array(
				'label'   => 'Форум',
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN
				)),
				'items'   => array(
					array(
						'label'   => 'Разделы форума',
						'url'     => array('/social/admin/forumSection/index'),
						'active'  => $this->id == 'admin/forumSection',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN
						)),
					),
					array(
						'label'   => 'Темы форума',
						'url'     => array('/social/admin/forumTopic/index'),
						'active'  => $this->id == 'admin/forumTopic',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN
						)),
					),
					array(
						'label'   => 'Ответы',
						'url'     => array('/social/admin/forumAnswer/index'),
						'active'  => $this->id == 'admin/forumAnswer',
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN
						)),
					),
				),
			),
			array(
				'label'   => 'Контент',
				'url'     => array('/content/admin/content/index'),
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_MODERATOR,
					User::ROLE_SENIORMODERATOR,
				)),
				'items'   => array(
					array(
						'label'   => 'Категории статических страниц',
						'url'     => array('/content/admin/contentcategory/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN
						)),
						'items'   => array(
							array(
								'label'   => 'Просмотр',
								'url'     => array('/content/admin/contentcategory/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_POWERADMIN
								)),
							),
							array(
								'label'   => 'Создание',
								'url'     => array('/content/admin/contentcategory/create/'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_POWERADMIN
								)),
							),
						)
					),
					array(
						'label'   => 'Статические страницы',
						'url'     => array('/content/admin/content/admin'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN
						)),
						'items'   => array(
							array(
								'label'   => 'Список страниц',
								'url'     => array('/content/admin/content/admin'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_POWERADMIN
								)),
							),
							array(
								'label'   => 'Новая страница',
								'url'     => array('/content/admin/content/create/'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_POWERADMIN
								)),
							),
						)
					),
					array(
						'label'   => 'PDF-свидетельства',
						'url'     => array('/idea/admin/copyrightfile/index'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN
						)),
						'items'   => array()
					),
					array(
						'label'   => 'Новости',
						'url'     => array('/content/admin/news/admin'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN
						)),
						'items'   => array(
							array(
								'label'   => 'Список новостей',
								'url'     => array('/content/admin/news/admin/'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_POWERADMIN
								)),
							),
							array(
								'label'   => 'Добавить новость',
								'url'     => array('/content/admin/news/create/'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_POWERADMIN
								)),
							),
						)
					),
					array(
						'label'   => 'Источники',
						'url'     => array('/content/admin/source/admin'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_MODERATOR,
							User::ROLE_SENIORMODERATOR,
							User::ROLE_POWERADMIN
						)),
						'items'   => array(
							array(
								'label'   => 'Просмотр',
								'url'     => array('/content/admin/source/admin'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_MODERATOR,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN
								)),
							),
							array(
								'label'   => 'Создание',
								'url'     => array('/content/admin/source/create'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_MODERATOR,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN
								)),
							),
						)
					),
					array(
						'label'   => 'Общий поиск ("возможно вы искали")',
						'url'     => array('/content/admin/search/meansAdmin'),
						'visible' => Yii::app()->user->checkaccess(array(
							User::ROLE_ADMIN,
							User::ROLE_POWERADMIN
						)),
					),
				)
			),
			array(
				'label'   => 'Управление меню',
				'url'     => array('/admin/menu/index'),
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN
				))
			),
			array(
				'label'   => 'Комментарии',
				'url'     => array('/member/admin/comment/index'),
//				'visible' => Yii::app()->user->checkaccess(array(
//					User::ROLE_ADMIN,
//					User::ROLE_POWERADMIN)),
				'active'  => $this->id == 'seoRewrite',
				'visible' => Yii::app()->user->checkaccess(array(
					User::ROLE_ADMIN,
					User::ROLE_POWERADMIN,
					User::ROLE_SEO,
				)),
			),
		),
	));
	?>
	<?php $this->endCache();
} ?>

</div></div>

<?php // Если правая панель выключена, получаем новый класс
$cls = is_null($this->rightbar) ? 'disable-rightbar' : '';?>

<div class="content <?php echo $cls;?>">

	<?php if (isset($this->breadcrumbs)): ?>
		<?php
		$this->widget('ext.bootstrap.widgets.BootCrumb', array(
			'links' => $this->breadcrumbs,
			'separator' => '/',
			'homeLink' => array('label' => 'Главная', 'url' => '/admin'),
		));
		?>
	<?php endif ?>


	<?php echo $content; ?>

</div>

<?php if ( ! empty($this->rightbar)): ?>
	<div class="rightbar">
		<div class="well">
			<?php echo $this->rightbar; ?>
		</div>
	</div>
<?php endif; ?>

</div>

<?php $this->widget('application.components.widgets.AdminRole.AdminWidget'); ?>
<!-- Yandex.Metrika counter --> <script type="text/javascript" > (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)}; m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)}) (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym"); ym(42935859, "init", { id:42935859, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true, trackHash:true }); </script> <noscript><div><img src="https://mc.yandex.ru/watch/42935859" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter -->
</body>
</html>
