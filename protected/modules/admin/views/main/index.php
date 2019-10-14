<?php
$this->widget('zii.widgets.CMenu', array(
	'encodeLabel'=>false,
	'items'=>array(
		array(
			'label'=>'Интерьеры<img src="/img/admin/icon-interior.png" alt="" width="128" height="128">',
			'url'=>array('/idea/admin/interior/list'),
			'visible' => Yii::app()->user->checkaccess(array(
				User::ROLE_ADMIN,
				User::ROLE_MODERATOR,
				User::ROLE_POWERADMIN,
				User::ROLE_SENIORMODERATOR,
				User::ROLE_FREELANCE_IDEA,
			)),
		),
		array(
			'label'=>'Архитекутра<img src="/img/admin/icon-architecture.png" alt="" width="128" height="128">',
			'url'=>array('/idea/admin/architecture/list'),
			'visible' => Yii::app()->user->checkaccess(array(
				User::ROLE_ADMIN,
				User::ROLE_MODERATOR,
				User::ROLE_POWERADMIN,
				User::ROLE_SENIORMODERATOR,
			)),
		),
		array(
			'label'=>'Пользователи<img src="/img/admin/icon-user.png" alt="" width="128" height="128">',
			'url'=>array('/admin/user/userlist'),
			'visible' => Yii::app()->user->checkaccess(array(
				User::ROLE_ADMIN,
				User::ROLE_POWERADMIN,
				User::ROLE_SALEMANAGER,
				User::ROLE_SENIORMODERATOR,
			)),
		),
		array(
			'label'=>'Комментарии<img src="/img/admin/icon-comment.png" alt="" width="128" height="128">',
			'url'=>array('/member/admin/comment/index'),
			'visible' => Yii::app()->user->checkaccess(array(
				User::ROLE_ADMIN,
				User::ROLE_POWERADMIN,
			)),
		),
		array(
			'label'=>'Форум<img src="/img/admin/icon-forum.png" alt="" width="128" height="128">',
			'url'=>array('/social/admin/forumSection/index'),
			'visible' => Yii::app()->user->checkaccess(array(
				User::ROLE_ADMIN,
				User::ROLE_POWERADMIN,
			)),
		),
		array(
			'label'=>'Каталог товаров<img src="/img/admin/icon-product.png" alt="" width="128" height="128">',
			'url'=>array('/catalog/admin/category/index'),
			'visible' => Yii::app()->user->checkaccess(array(
				User::ROLE_ADMIN,
				User::ROLE_MODERATOR,
				User::ROLE_POWERADMIN,
				User::ROLE_SALEMANAGER,
				User::ROLE_SENIORMODERATOR,
				User::ROLE_FREELANCE_STORE,
				User::ROLE_FREELANCE_PRODUCT,
			)),
		),
	),
	'htmlOptions'=>array('class'=>'media-grid'),
));
?>