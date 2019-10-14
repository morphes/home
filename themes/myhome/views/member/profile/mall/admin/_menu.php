<?php $this->widget('application.components.widgets.CMenuEx',array(
	'encodeLabel'    => false,
	'activeCssClass' => 'current',
	'htmlOptions'    => array('class' => '-menu-inline -tab-menu profile-menu'),
	'items'          => array(
		array(
			'label'  => 'Проифль',
			'active' => $this->id == 'profile' && $this->action->id == 'index',
			'url'    => $user->getLinkProfile()
		),
		array(
			'label'   => 'Мои подборки',
			'url'     => '/catalog/profile/folderList',
			'active'  => $this->module->id == 'catalog' && $this->id == 'profile' && in_array($this->action->id, array('folderList')),
			'visible' => Yii::app()->user->id == $user->id,
		),
		array(
			'label'   => 'Баннеры',
			'url'     => '/catalog/mall/index',
			'active'  => $this->module->id == 'catalog' && $this->id == 'mall' && in_array($this->action->id, array('index')),
			'visible' => Yii::app()->getUser()->getId() == $user->id,
		),
	)
));?>
