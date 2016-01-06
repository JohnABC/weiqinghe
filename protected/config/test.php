<?php
return array(
    'components' => array(
       'cache' => array(
    		'class' => 'system.caching.CMemCache',
			'useMemcached' => True,
    		'servers' => array(
    			array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 50)
    		),
    	),
        'db' => array(
            'class' => 'system.db.CDbConnection', // 'class' => 'system.db.CDbConnection',
            'connectionString' => 'mysql:host=127.0.0.1;dbname=newtheone',
            'schemaCachingDuration' => 432000, //60*60*12
            'emulatePrepare' => True,
            'enableProfiling' => False,
            'username' => 'root',
            'password' => 'Qumaiya520',
            'charset' => 'utf8',
            'tablePrefix' => 'v2_',
        )
    )
);