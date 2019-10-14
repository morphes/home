<?php $this->widget('application.components.widgets.CMenuEx', array(
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
			'label'   => 'Мои заказы',
			'url'     => array('/users', 'login' => $user->login, 'action' => 'tenders'),
			'active'  => $this->module->id == 'tenders',
			'visible' => Yii::app()->user->id == $user->id,
		),
		array(
			'label'   => 'Избранное',
			'url'     => array('/users', 'login' => $user->login, 'action' => 'favorite'),
			'active'  => $this->id == 'favorite',
			'visible' => Yii::app()->user->id == $user->id,
		),
		array(
			'label'  => 'Активность',
			'url'    => array('/users', 'login' => $user->login, 'action' => 'activity'),
			'active' => $this->action->id == 'activity',
		),

	)
));
?>