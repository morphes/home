<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <meta name="language" content="en" />

                <!-- blueprint CSS framework -->
                <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
                <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
                <!--[if lt IE 8]>
                <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
                <![endif]-->

                <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
                <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />

                <title><?php echo CHtml::encode($this->pageTitle); ?></title>
        </head>

        <body>

                <div class="container" id="page">

                        <div id="header">
                                <div id="logo"><?php echo CHtml::encode(Yii::app()->name); ?></div>
                        </div><!-- header -->

                        <div id="mainmenu">
                                <?php 
                                $this->widget('zii.widgets.CMenu', array(
                                    'items' => array(
                                        array('label' => 'Главная', 'url' => array('/site/index')),
                                        array('label' => 'Регистрация пользователя', 'url' => array('/site/userregistration'), 'visible' => Yii::app()->user->isGuest),
                                        array('label' => 'Регистрация магазина', 'url' => array('/site/shopregistration'), 'visible' => Yii::app()->user->isGuest),
                                        array('label' => 'Вход', 'url' => array('/site/login'), 'visible' => Yii::app()->user->isGuest),
                                        array('label' => 'Выход (' . Yii::app()->user->name . ')', 'url' => array('/site/logout'), 'visible' => !Yii::app()->user->isGuest),
                                        array('label' => 'Панель администрирования', 'url' => array('/admin'), 'visible' => Yii::app()->user->checkAccess(User::ROLE_ADMIN)),
                                    ),
                                ));
                                ?>
                        </div><!-- mainmenu -->
                        <?php if (isset($this->breadcrumbs)): ?>
                                <?php
                                $this->widget('zii.widgets.CBreadcrumbs', array(
                                    'links' => $this->breadcrumbs,
                                ));
                                ?><!-- breadcrumbs -->
                        <?php endif ?>

                        <div class="container">
                                <div id="content">
					<?php
					Yii::app()->clientScript->registerCoreScript('jquery');
					?>
					
					<div id="menuButton">
						<div>М Е Н Ю</div>
					</div>
					
					<?php 
					Yii::app()->clientScript->registerScript('menuButton','
						$("#menuButton").find("div").hover(function(){
							$("ul.adminMenu").show();
						});
						$("ul.adminMenu").mouseleave(function(){
							$(this).hide();
						});
					');
					?>
					
                                        <?php if ($this->beginCache('adminMenuMain', array('duration' => Cache::DURATION_ADMIN_MENU, 'varyByExpression' => 'Yii::app()->user->role'))) { ?>

                                                <?php
                                                $this->widget('CMenu', array(
						    'htmlOptions' => array('class' => 'adminMenu'),
						    'activateParents' => true,
                                                    'items' => array(
                                                        array(
								'label'	  => 'Главная страница',
								'url'	  => array('/admin/mainpage/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_POWERADMIN
								)),
								'items'   => array(
									array(
										'label'   => 'Идеи',
										'url'	  => array('/admin/unit/idea/index'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_POWERADMIN
										)),
									),
									array(
										'label'   => 'Дизайнеры и архитекторы',
										'url'	  => array('/admin/unit/designer/index'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_POWERADMIN
										)),
									),
									array(
										'label'   => 'Прорабы и мастера',
										'url'	  => array('/admin/unit/contractor/index'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_POWERADMIN
										)),
									),
									array(
										'label'   => 'Промоблок',
										'url'	  => array('/admin/unit/promoblock/index'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_POWERADMIN
										)),
									),
								)
							),
                                                        array(
								'label'   => 'Управление пользователями',
								'url'	  => array('/admin/user/userlist'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_JUNIORMODERATOR,
									User::ROLE_MODERATOR,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN,
									User::ROLE_SALEMANAGER
								)),
								'items'   => array(
									array(
										'label'	  => 'Администраторы',
										'url'	  => array('/admin/user/adminlist'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_POWERADMIN
										)),
									),
									array(
										'label'   => 'Пользователи',
										'url'     => array('/admin/user/userlist'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_JUNIORMODERATOR,
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
											User::ROLE_POWERADMIN,
											User::ROLE_SALEMANAGER
										)),
									),
								)
							),
                                                        array(
								'label'   => 'Портфолио лайт',
								'url'	  => array('/member/admin/portfolio/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_MODERATOR,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN
								)),
								'items'   => array()
							),
                                                        array(
								'label'   => 'Идеи',
								'url'	  => array('/idea/admin/interior/list'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_MODERATOR,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN,
									User::ROLE_FREELANCEEDITOR
								)),
								'items'   => array(
									array(
										'label'   => 'Добавить новую идею',
										'url'     => array('/idea/admin/create/'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_MODERATOR,
											User::ROLE_SENIORMODERATOR,
											User::ROLE_POWERADMIN
										)),
									),
									array(
										'label'   => 'Интерьеры',
										'url'     => array('/idea/admin/interior/list'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_MODERATOR,
											User::ROLE_SENIORMODERATOR,
											User::ROLE_POWERADMIN,
											User::ROLE_FREELANCEEDITOR
										)),
									),
									array(
										'label'   => 'Типы идей',
										'url'	  => array('/idea/admin/ideatype/index'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_POWERADMIN
										)),
										'items'   => array(
											array(
												'label'	  => 'Новый тип идей',
												'url'	  => array('/idea/admin/ideatype/create'),
												'visible' => Yii::app()->user->checkaccess(array(
													User::ROLE_POWERADMIN
												)),
											),
											array(
												'label'   => 'Список типов идей',
												'url'     => array('/idea/admin/ideatype/admin'),
												'visible' => Yii::app()->user->checkaccess(array(
													User::ROLE_POWERADMIN
												)),
											),
										)
									),
									array(
										'label' => 'Свойства идей',
										'url'   => array('/idea/admin/property/index'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_POWERADMIN
										)),
									),
								)
							),
                                                        array(
								'label'	  => 'Управление почтой',
								'url'	  => array('/admin/mailtemplate/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN
								)),
								'items'   => array(
									array(
										'label'   => 'Новый шаблон сообщений',
										'url'     => array('/admin/mailtemplate/create'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_SENIORMODERATOR,
											User::ROLE_POWERADMIN
										)),
									),
									array(
										'label'   => 'Шаблоны сообщений',
										'url'     => array('/admin/mailtemplate/index'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_SENIORMODERATOR,
											User::ROLE_POWERADMIN
										)),
									),
									array(
									    'label' => 'Рассылки',
									    'url' => array('/admin/mailer/index'),
									    'visible' => Yii::app()->user->checkaccess(array(
										    User::ROLE_ADMIN,
										    User::ROLE_POWERADMIN
									    )), // FIXEME: rule
									),
								)
							),
                                                        array(
								'label'   => 'Контент',
								'url'     => array('/content/admin/content/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_MODERATOR,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN
								)),
								'items'   => array(
									array(
										'label'   => 'Категории статей',
										'url'     => array('/content/admin/contentcategory/index'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_MODERATOR,
											User::ROLE_SENIORMODERATOR,
											User::ROLE_POWERADMIN
										)),
										'items'   => array(
											array(
												'label'   => 'Просмотр',
												'url'     => array('/content/admin/contentcategory/index'),
												'visible' => Yii::app()->user->checkaccess(array(
													User::ROLE_ADMIN,
													User::ROLE_MODERATOR,
													User::ROLE_SENIORMODERATOR,
													User::ROLE_POWERADMIN
												)),
											),
											array(
												'label'   => 'Создание',
												'url'     => array('/content/admin/contentcategory/create/'),
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
										'label'   => 'Статьи',
										'url'     => array('/content/admin/content/admin'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_MODERATOR,
											User::ROLE_SENIORMODERATOR,
											User::ROLE_POWERADMIN
										)),
										'items'   => array(
											array(
												'label'   => 'Просмотр',
												'url'     => array('/content/admin/content/admin'),
												'visible' => Yii::app()->user->checkaccess(array(
													User::ROLE_ADMIN,
													User::ROLE_MODERATOR,
													User::ROLE_SENIORMODERATOR,
													User::ROLE_POWERADMIN
												)),
											),
											array(
												'label'   => 'Создание',
												'url'     => array('/content/admin/content/create/'),
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
										'label'   => 'Источники',
										'url'     => array('/admin/source/index'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_MODERATOR,
											User::ROLE_SENIORMODERATOR,
											User::ROLE_POWERADMIN
										)),
										'items'   => array(
											array(
												'label'   => 'Просмотр',
												'url'     => array('/admin/source/admin'),
												'visible' => Yii::app()->user->checkaccess(array(
													User::ROLE_ADMIN,
													User::ROLE_MODERATOR,
													User::ROLE_SENIORMODERATOR,
													User::ROLE_POWERADMIN
												)),
											),
											array(
												'label'   => 'Создание',
												'url'     => array('/admin/source/create'),
												'visible' => Yii::app()->user->checkaccess(array(
													User::ROLE_ADMIN,
													User::ROLE_MODERATOR,
													User::ROLE_SENIORMODERATOR,
													User::ROLE_POWERADMIN
												)),
											),
										)
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
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_MODERATOR,
									User::ROLE_SENIORMODERATOR,
									User::ROLE_POWERADMIN
								))
							),
							array(
								'label'   => 'Логирование',
								'url'     => array('/log/moderator/operationlist'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_ADMIN,
									User::ROLE_POWERADMIN
								))
							),
							array(
								'label'   => 'Промокоды',
								'url'     => array('/member/admin/promocode/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_POWERADMIN
								)),
								'items'	  => array(
									array(
										'label'   => 'Просмотр',
										'url'     => array('/member/admin/promocode/admin'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_POWERADMIN
										)),
									),
									array(
										'label'   => 'Создание',
										'url'     => array('/member/admin/promocode/create'),
										'visible' => Yii::app()->user->checkaccess(array(
											User::ROLE_ADMIN,
											User::ROLE_POWERADMIN
										)),
									),
								)
							),
							array(
								'label' => 'Бан по IP',
								'url' => array('/admin/ban/index'),
								'visible' => Yii::app()->user->checkaccess(array(
									User::ROLE_POWERADMIN
								)),
							),
                                                    ),
                                                ));
                                                ?>
                                                <?php $this->endCache();
                                        } ?>                        
                                        <hr />
                                        <?php echo $content; ?>
                                </div><!-- content -->
                        </div>

                        <div id="footer">
                                Copyright &copy; <?php echo date('Y'); ?> by MyHome.<br/>
                                All Rights Reserved.<br/>
                        </div><!-- footer -->

                </div><!-- page -->

        </body>
</html>