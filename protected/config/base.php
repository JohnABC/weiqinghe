<?php
return array(
    'basePath' => W_ROOT_PATH . '/protected',
    'name' => '【微清河】 - 城市生活社区门户,微清河weiqinghe.com',
    'preload' => array('log'),
    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.dicts.*',
    ),
    'modules' => array(
        'weixin',
        'admin',
        'gii' => array(
            'class' => 'system.gii.GiiModule',
            'password' => '123456',
            'ipFilters' => array('127.0.0.1', '::1', '10.10.11.100'),
        ),
    ),
    'components' => array(
        'request' => array(
            'csrfTokenName' => 'weiqingheToken',
        ),
        'urlManager' => array(
            'urlFormat' => 'path',
            'showScriptName' => False
        ),
        'errorHandler' => array(
            // use 'site/error' action to display errors
            //'errorAction' => 'flight/site/error',
        ),
		'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
				array(
					'class' => 'CFileLogRoute',
					'levels' => 'error, warning',
				),
			    array(
			        'class' => 'CFileLogRoute',
			        'levels' => 'error, warning',
			        'categories' => 'dberror.*',
			        'logFile' => 'dberror.log',
			    ),
			),
		),
    ),

    'params' => array(
        'adminEmail' => 'weiqinghe@yilexun.com',
        'keyword' => '微清河,微清河网,清河,清河县,清河吧,清河租房,清河社区',
        'des' => '微清河（www.weiqinghe.com），清河县“自行开版、自行管理、自行发展”的开放式社区平台，致力于为各地用户提供便捷的生活交流空间与本地生活服务平台。',
    ),
);