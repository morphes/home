<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name' => 'MyHome',
	'language' => 'ru',
	'homeUrl' => 'http://www.myhome.ru',
	'id'=>'myhome',

	// application components
	'import' => array(
		'application.components.*',
		'application.components.interfaces.*',
		'application.models.*',
		'application.commands.models.EDbConnection',
		'application.components.imageHandler.imageHandler',
	),
	'components' => array(
		'cache'=>array(
            		'class'=>'system.caching.CMemCache',
        	        'servers'=>array(
                	        array('host'=>'localhost', 'port'=>11211, 'timeout'=>2),
        	        ),
	        ),
		'db' => array(
			'class' => 'EDbConnection',
			'connectionString' => 'mysql:host=localhost;dbname=myhome',
			'emulatePrepare' => true,
			'username' => 'myhome',
			'password' => 'manym71Ldb',
			'charset' => 'utf8',
			'autoConnect' => false,
			//'persistent' => true,
			'reconnectTimeOut' => 5,
		),
		'dbcatalog2' => array(
			'class' => 'EDbConnection',
			'connectionString' => 'mysql:host=localhost;dbname=myhome_catalog2',
			'emulatePrepare' => true,
			'username' => 'myhome',
			'password' => 'manym71Ldb',
			'charset' => 'utf8',
			'autoConnect' => false,
			//'persistent' => true,
			'reconnectTimeOut' => 5,
		),
		'phpQuery' => array(
            'class' => 'ext.phpQuery.Wrapper',
        ),
		'curl' =>array(
            'class' => 'application.extensions.curl.Curl',
        ),
        'redis' => array(
			'class'=>'application.components.RedisComponent',
			'host'=>'127.0.0.1',
			'port'=>6379,
			'timeout'=>100,
		),
		'gearman' => array(
            'class' => 'ext.gearman.Gearman',
            'servers' => array(array('host' => '5.9.25.215', 'port' => 4730)),
        ),
		'mail' => array(
		        'class' => 'application.components.EmailComponent',
		),
		'sphinx' => array(
			'class' => 'EDbConnection',
			'connectionString' => 'mysql:host=sphinx.myhome.ru;port=9306;',
			'tablePrefix'=>'myhome_',
			//'persistent' => true,
			'autoConnect' => false,
			'reconnectTimeOut' => 5,
		),
		'predis' => array(
			'class'=>'application.components.RedisComponent',
			'host'=>'127.0.0.1',
			'port'=>6380,
			'timeout'=>10,
			'password'=>'un7QrWoUNaDW',
		),
		'img'=>array(
			'class'=>'ext.imgLoader.ImageComponent',
			'transportClass'=>'WebDavFile',
			'transportOptions'=>array(
				'host'=>'static.myhome.ru',
				'port'=>80,
				'userpwd'=>'admin:9iwRA6XKdnQv',
			),
			'redisId'=>'predis',
			'staticDomain' => 'http://static.myhome.ru',
			'pingRedis'=>true,
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CDbLogRoute',
					'levels'=>'info, error',
					'logTableName'=>'sys_sphinx_log',
					'categories' => 'sphinx',
					'enabled'=>true,
					'autoCreateLogTable'=>true,
					'connectionID'=>'db',
				),
			),
		)
	),
	'params' => array(
		// this is used in contact page
		'serverIp' => '5.9.25.215',
		//'sphinxPrefix' => 'myhome_',
    	),
);