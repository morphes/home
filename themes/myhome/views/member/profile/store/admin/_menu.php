<?php $this->widget('application.components.widgets.CMenuEx',array(
	'encodeLabel'    => false,
	'activeCssClass' => 'current',
	'htmlOptions'    => array('class' => '-menu-inline -tab-menu profile-menu'),
	'items'          => array(
		array(
			'label'  => 'Профиль',
			'url'    => $user->getLinkProfile(),
			'active' => $this->id == 'profile' && $this->action->id == 'index',
		),
		array(
			'label'   => 'Магазины и товары',
			'url'     => '/catalog/profile/storeList',
			'active'  => $this->module->id == 'catalog' && $this->id == 'profile' && in_array($this->action->id, array('storeList', 'storeProductList', 'storeCreate', 'storeUpdate', 'storeShowcase')),
			'visible' => Yii::app()->user->id == $user->id,
		),
		array(
			'label'   => 'Добавление товаров',
			'url'     => '/catalog/profile/list',
			'active'  => $this->module->id == 'catalog' && $this->id == 'profile' && in_array($this->action->id, array('productCreate', 'productUpdate', 'productSelectCategory', 'list')),
			'visible' => Yii::app()->user->id == $user->id,
		),
	)
)); ?>
