<?php
return array(
    'components' => array(
       'cache' => array(
    		'class' => 'system.caching.CMemCache',
			'useMemcached' => True,
    		'servers' => array(
    			array('host' => '10.132.31.205', 'port' => 11211, 'weight' => 50)
    		),
    	),
        'db' => array(
            'class' => 'system.db.CDbConnection',
            'connectionString' => 'mysql:host=qumaiyain.mysql.rds.aliyuncs.com;dbname=theone;port=3303;',
            'schemaCachingDuration' => 432000, //60*60*12
            'emulatePrepare' => True,
            'enableProfiling' => False,
            'username' => 'theone',
            'password' => 'QumaiyA520',
            'charset' => 'utf8',
            'tablePrefix' => 'v1_',
        ),
    )
);