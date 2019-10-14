<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// 
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'MyHome',
        'language' => 'ru',
        'homeUrl' => 'http://www.myhome.ru',
        'theme'=>'myhome',
	'id'=>'myhome',
    
	// preloading 'log' component
	'preload'=>array('log', 'cookieStorage', 'userservice'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.components.urlRules.*',
		'application.components.urlRules.Classes.*',
		'ext.yiiext.behaviors.model.trees.ENestedSetBehavior',
		'application.modules.member.models.*',
		'ext.bootstrap.components.*',
		'application.helpers.*',
		'application.components.interfaces.*',
		'application.components.imageHandler.imageHandler',
	),
	
	'modules'=>array(
		// uncomment the following to enable the Gii tool
		'admin' => array('layout'=>'admin'),
            
                'content', 'market', 'member', 'idea', 'log', 'tenders', 'catalog', 'media', 'help', 'social', 'catalog2',
	),

	// application components
	'components'=>array(
		'predis' => array(
			'class'=>'application.components.RedisComponent',
			'host'=>'127.0.0.1',
			'port'=>6380,
			'timeout'=>2,
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
			'staticDomain' => 'http://static.myhome.ru',
			'redisId'=>'predis',
		),
		'workTime'=> array(
			'class' => 'application.components.WorkTime.WorkTime'
		),
	        'clientScript' => array(
			'class' => 'application.components.CustomClientScript',
		),
        'less'=>array(
			'class'=>'ext.yii-less.components.Less',
			'mode'=>'server',
			'files'=>array(
				'css-new/styles.less'=>'css-new/generated/styles.css',
				'css-new/dev/custom/ext.less'=>'css-new/generated/ext.css',
				'css-new/dev/custom/index.less'=>'css-new/generated/index.css',
				'css-new/dev/custom/bm.less'=>'css-new/generated/bm.css',
				'css-new/dev/custom/goods.less'=>'css-new/generated/goods.css',
				'css-new/dev/custom/profile.less' => 'css-new/generated/profile.css',
				'css-new/dev/custom/admin.less' => 'css-new/generated/admin.css',
				'css-new/dev/custom/adv.less' => 'css-new/generated/adv.css',
				'css-new/dev/custom/media.less' => 'css-new/generated/media.css',
				'css-new/dev/custom/competitions.less' => 'css-new/generated/competitions.css',
				'css-new/dev/custom/mini-site.less'    => 'css-new/generated/mini-site.css',
				'css-new/dev/custom/favorites.less' => 'css-new/generated/favorites.css',
				'css-new/dev/custom/static.less'   => 'css-new/generated/static.css',
				'css-new/dev/custom/spec.less'      => 'css-new/generated/spec.css',
				'css-new/dev/custom/auth.less'         => 'css-new/generated/auth.css',
			),
			'options'=>array(
				//'compression'=>'yui',
				'compilerPath'=>'lessc',
				'nodePath'=>'node',
				'forceCompile'=>false,
			),
		),
		'widgetFactory'=>array(
			'widgets'=>array(
				'EBreadcrumbs'=>array(
					'homeLink'=>'<a href=http://www.myhome.ru/><span class="text_block">Федеральный интернет-портал по ремонту и благоустройству MyHome:</span>Главная</a>',
				),
			),
		),
		'ePdf' => array(
		        'class' => 'ext.yii-pdf.EYiiPdf',
		        'params' => array(
		                'mPDF' => array(
		                        'librarySourcePath' => 'application.vendor.mpdf.*',
		                        'constants' => array(
		                                '_MPDF_TEMP_PATH' => Yii::getPathOfAlias('application.runtime'),
		                        ),
		                        'class' => 'mpdf',
		                ),
		        ),
		),
		'file'=>array(
                        'class'=>'application.extensions.file.CFile',
                ),
		'bootstrap'=>array(
                    'class'=>'ext.bootstrap.components.Bootstrap'
                 ),
		'image' => array(
			    'class' => 'application.extensions.image.CImageComponent',
			    'driver' => 'ImageMagick',
		),
		'session' => array(
			'class' => 'CDbHttpSession',
			'connectionID' => 'db',
			'sessionTableName' => 'session',
			'autoCreateSessionTable' => false,
			'timeout' => 86400,
			'cookieMode' => 'allow',
                        'cookieParams' => array(
                                'path' => '/',
                                'domain' => '.myhome.ru',
                                'httpOnly' => true,
                        ),
		),
        'cache'=>array(
                'class'=>'system.caching.CMemCache',
                'servers'=>array(
                        array('host'=>'localhost', 'port'=>11211, 'timeout'=>2),
                ),
        ),
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
            'loginUrl'=>array('site/login'),
            'class' => 'WebUser',
		),
		'cookieStorage' => array(
			'class' => 'CCookieStorage',
			'cookieVar' => 'storage',
		),
        'userservice'=>array(
            'class'=>'application.components.UserService',
        ),
                
		'search' => array(
		    'class' => 'ext.DGSphinxSearch.DGSphinxSearch',
		    'indexPrefix' => 'myhome_',
		    'server' => 'sphinx.myhome.ru',
		    'port' => 9312,
		    'maxQueryTime' => 3000,
		    'enableProfiling'=>0,
		    'enableResultTrace'=>0,
		    'fieldWeights' => array(
			'name' => 10000,
			'keywords' => 100,
		    ),
		),
		'sphinx' => array(
			'class' => 'CDbConnection',
			'connectionString' => 'mysql:host=sphinx.myhome.ru;port=9306;',
			'tablePrefix'=>'myhome_',
		),
		'redis' => array(
				'class'=>'application.components.RedisComponent',
				'host'=>'127.0.0.1',
				'port'=>6379,
				'timeout'=>3,
		),
        'gearman' => array(
                'class' => 'ext.gearman.Gearman',
                'servers' => array(array('host' => '5.9.25.215', 'port' => 4730)),
        ),
        'mail' => array(
                'class' => 'application.components.EmailComponent',
        ),

		'urlManager'=>array(
			'urlFormat'=>'path',
            'showScriptName' => false,
			'appendParams' => true,
			'rules'=>array(
				'ipad_za_spasibo' => 'content/static/iforthankstotal',
				'ipad_za_spasibo_condition' => 'content/static/iforthanks',
				array(
					'class' => 'application.components.urlRules.SeoUrlRule',
				),	
				array(
					'class' => 'application.components.urlRules.NewUrlRules',
				),
				'http://bm.myhome.<zone:ru|local>/search'  => 'search/index',
				'http://bm.myhome.<zone:ru|local>/about'  => 'site/bmAbout',
				'http://bm.myhome.<zone:ru|local>/email'  => 'site/email',

				'http://<sub:\+w>.myhome.ru/about'         => 'catalog/store/moneyIndex',
				'http://<sub:\+w>.myhome.ru/fotos'         => 'catalog/store/moneyGallery',
				//'http://<sub:\+w>.myhome.ru/products' => 'catalog/store/moneyProducts',
				'http://<sub:\+w>.myhome.ru/news'          => 'catalog/store/moneyNews',
				'http://<sub:\+w>.myhome.ru/news/<id:\d+>' => 'catalog/store/moneyNewsDetail/id/<id>',
				'http://<sub:\+w>.myhome.ru/feedback'      => 'catalog/store/moneyFeedback',

				'pros/up'		    => '/member/specialist/priority',
				'favorite/shared/<hash:\w+>'=> '/member/favorite/shared/hash/<hash>',
				'journal'                    => 'media',
				'journal/news'               => 'media/new/index',
				'journal/news/<id:\d+>'      => 'media/new/detail/id/<id>',
				'journal/knowledge'          => 'media/knowledge/index',
			        'journal/knowledge/<id:\d+>' => 'media/knowledge/detail/id/<id>',
			        'journal/knowledge/cat/<section_url:\w+>/<id:\d+>'  => 'media/knowledge/detail/id/<id>',
			        'journal/knowledge/cat/<section_url:\w+>'  => 'media/knowledge/index',
				'journal/events'             => 'media/event/index',
				'journal/events/<id:\d+>'    => 'media/event/view/id/<id>',
				'about'     => 'content/default/index/category/info/article/about',
				'copyright' => 'content/default/index/category/info/article/copyright',
				'agreement' => 'content/default/index/category/info/article/agreement',
				'advertising'                => 'content/advertising',
				'advertising/<action:\w+>'   => 'content/advertising/<action>',
				'competition'                         => 'content/static/competition',
				'competition/superintendent' 	       => 'content/default/index/category/konkurs/article/competition_superintendent',
				'competition/architector'             => 'content/default/index/category/konkurs/article/competition_architector',
				'competition/bestdesigner/2012/march' => 'content/default/index/category/konkurs/article/bestdesigner_march',
				'competition/bestdesigner/2012/march/result' => 'content/default/index/category/konkurs/article/bestdesigner_march_result',
				'competition/foreman/2012/june'       => 'content/default/index/category/konkurs/article/foreman_june',
				'competition/foreman/2012/june/june'  => 'content/default/index/category/konkurs/article/foreman_june',
				'competition/superintendent/2012/july/result' => 'content/default/index/category/konkurs/article/2012_july_result_int',
				'competition/architector/2012/july/result' => 'content/default/index/category/konkurs/article/2012_july_result_arch',
				'pros/rating'                             =>  'content/static/specrules',
				'content/deposition' => 'content/default/index/category/info/article/deposition',
				'stream/zkapitel'    => 'content/static/zkapitel',
				'partners'           => 'content/default/index/category/info/article/partners',
				'password/remember'  => 'site/forgot',
				'planner'            => 'site/planner',
				'forum/topic/id/<id:\d+>' => '/social/forum/topic/id/<id>',
				'experts' => '/social/expert',
				'article/<category>/<article>' => 'content/default/index',
				'tenders/list'     => 'tenders/tender/list',
				'tenders/<id:\d+>' => 'tenders/tender/view/id/<id>',
				'tenders/create'   => 'tenders/tender/create',
				'tenders/success'  => 'tenders/tender/success',
				'tenders/create/<id:\d+>' => 'tenders/tender/create/id/<id>',
				'<controller:\w+>/<id:\d+>'              => '<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
				'<controller:\w+>/<action:\w+>'          => '<controller>/<action>',
				'/competition/specialists/2013/july'       =>  'content/static/iforthanks',
				'cupons'    => 'content/static/cupons',
			),
		),
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=myhome',
			'emulatePrepare' => true,
			'schemaCachingDuration' => 3600,
			'username' => 'myhome',
			'password' => 'manym71Ldb',
			'charset' => 'utf8',
			'enableProfiling'=>true,
        		'enableParamLogging' => true,
		),
		'dbcatalog2' => array(
			'class' => 'CDbConnection',
			'connectionString' => 'mysql:host=localhost;dbname=myhome_catalog2',
			'emulatePrepare' => true,
			'schemaCachingDuration' => 3600,
			'username' => 'myhome',
			'password' => 'manym71Ldb',
			'charset' => 'utf8',
			'enableProfiling'=>true,
        		'enableParamLogging' => true,
		),
		
		'errorHandler'=>array(
            'errorAction'=>'site/error',
        ),
        'log'=>array(
                'class'=>'CLogRouter',
                'routes'=>array(
					array(
			                        'class' => 'ext.yii-debug-toolbar.YiiDebugToolbarRoute',
						'ipFilters' => array('127.0.0.1', '127.0.1.1', '194.186.7.46', '192.168.1.215', '37.192.128.242', '92.125.138.55'),
					),
					array(
					    'class'=>'CDbLogRoute',
					    'levels'=>'error, warning',
					    'logTableName'=>'sys_log',
					    'connectionID'=>'db',
					    'autoCreateLogTable'=>false,
					),
					array(
						'class' => 'ext.SlowLogRoute.EDbSlowLogRoute',
						'connectionID' => 'db',
						'autoCreateLogTable' => false,
						'logTableName' => 'slow_log',
						'maxMemory' => 30,
						'maxExecTime' => 0.3,
					),
                ),
        ),

		'openGraph'=>array(
			'class' => 'application.components.OpenGraph',
		),
		
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
    	'params' => array(
		//'sphinxPrefix' => 'myhome_',
		// this is used in contact page
		'adminEmail' => 'webmaster@myhome.ru',
		'bmHomeUrl' => 'http://bm.myhome.ru',
        	'promoCode' => 'хомяк',
        	'salesEmail' => 'sales@myhome.ru',
        	'feedbackEmail' => 'feedback@myhome.ru',
	),
);


