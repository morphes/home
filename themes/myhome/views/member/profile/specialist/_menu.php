<?php $this->widget('application.components.widgets.CMenuEx',array(
	'encodeLabel'    => false,
	'htmlOptions'    => array('class' => '-menu-inline -tab-menu profile-menu'),
	'activeCssClass' => 'current',
	'items'          => array(
		array(
			'label'  => 'Профиль',
			'url'    => $user->getLinkProfile(),
			'active' => $this->id == 'profile' && $this->action->id == 'index',
		),
		array(
			'label'  => 'Портфолио',
			'url'    => array('/users', 'login' => $user->login, 'action' => 'portfolio'),
			'active' => $this->action->id == 'portfolio' || $this->action->id == 'project' || $this->action->id == 'draft',
		),
		array(
			'label'  => 'Услуги',
			'url'    => array('/users', 'login' => $user->login, 'action' => 'services'),
			'active' => $this->action->id == 'services',
		),
		array(
			'label'   => 'Мои заказы',
			'url'     => array('/users', 'login' => $user->login, 'action' => 'tenders'),
			'active'  => $this->module->id == 'tenders',
			'visible' => Yii::app()->user->id == $user->id,
		),

		array(
			'label'  => 'Отзывы',
			'url'    => array('/member/review/list', 'login' => $user->login),
			'active' => $this->id == 'review',
		),
		array(
			'label'  => 'Контакты',
			'url'    => array('/users', 'login' => $user->login, 'action' => 'contacts'),
			'active' => $this->action->id == 'contacts',
		),
		array(
			'label'   => 'Статистика',
			'url'     => array('/users', 'login' => $user->login, 'action' => 'statistic'),
			'active'  => $this->action->id == 'statistic',
			'visible' => Yii::app()->user->id == $user->id,
		),
)));
?>