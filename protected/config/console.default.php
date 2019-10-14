<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return CMap::mergeArray(
    array(
        'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
        'name' => 'My Console Application',
        'language' => 'ru',
        'homeUrl' => 'http://myhome.local',
        'id' => 'myhome',
        'import' => array(
            'application.components.*',
            'application.models.*',
            'application.components.interfaces.*',
            'application.commands.models.EDbConnection',
            'application.components.imageHandler.imageHandler',
        ),
        'components' => array(
            'cache' => array(
                'class' => 'system.caching.CMemCache',
                'servers' => array(
                    array('host' => 'localhost', 'port' => 11211),
                ),
            ),
            'db' => array(
                'class' => 'EDbConnection',
                'connectionString' => 'mysql:host=localhost;dbname=myhome',
                'emulatePrepare' => true,
                'username' => 'myhome',
                'password' => '3555131',
                'charset' => 'utf8',
                'persistent' => true,
                'autoConnect' => false,
                'reconnectTimeOut' => 5,
                'initSQLs' => array('SET SESSION group_concat_max_len = 4096'),
            ),
            'dbcatalog2' => array(
                'connectionString' => 'mysql:host=localhost;dbname=myhome_catalog2',
                'username' => 'myhome',
                'password' => '3555131',
                'charset' => 'utf8',
                'enableProfiling' => true,
                'enableParamLogging' => true,
                'class' => 'CDbConnection'
            ),
            'mail' => array(
                'class' => 'application.components.EmailComponent',
            ),
            'phpQuery' => array(
                'class' => 'ext.phpQuery.Wrapper',
            ),
            'curl' => array(
                'class' => 'application.extensions.curl.Curl',
            ),
            'redis' => array(
                'class' => 'application.components.RedisComponent',
                'host' => 'localhost',
                'port' => 6379,
                'timeout' => 20,
            ),
            'gearman' => array(
                'class' => 'ext.gearman.Gearman',
                'servers' => array(array('host' => '127.0.0.1', 'port' => 4730)),
            ),
            'sphinx' => array(
                'class' => 'EDbConnection',
                'connectionString' => 'mysql:host=127.0.0.1;port=9306;',
                'emulatePrepare' => true,
                'tablePrefix' => 'myhome_',
                'persistent' => true,
                'autoConnect' => false,
                'charset' => 'utf8',
                'reconnectTimeOut' => 5,
            ),
            'predis' => array(
                'class' => 'application.components.RedisComponent',
                'host' => 'localhost',
                'port' => 6380,
                'timeout' => 1,
                'password' => 'xrenovpass',
            ),
            'img' => array(
                'class' => 'ext.imgLoader.ImageComponent',
                'transportClass' => 'WebDavFile',
                'transportOptions' => array(
                    'host' => 'img.myhome.local',
                    'port' => 8080,
                    'userpwd' => 'admin:12345',
                ),
                'redisId' => 'predis',
                'pingRedis' => true,
            ),
        ),
        'params' => array(),
    ), require(dirname(__FILE__) . '/console.php')
);