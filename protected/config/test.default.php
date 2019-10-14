<?php

return CMap::mergeArray(
	require(dirname(__FILE__) . '/main.default.php'), array(
		'preload' => array('log'),
		'components' => array(
			'fixture' => array(
				'class' => 'system.test.CDbFixtureManager',
			),
			'db' => array(
				'connectionString' => 'mysql:host=localhost;dbname=myhome_test',
				'username' => 'myhome',
				'password' => '3555131',
				'charset' => 'utf8',
			),
			'mail' => array(
				'class' => 'application.components.EmailComponent',
			),
			'gearman' => array(
				'class' => 'ext.gearman.Gearman',
				'servers' => array(
					array('host' => '127.0.0.1', 'port' => 4730),
				),
			),
		),
	)
);
