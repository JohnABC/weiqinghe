<?php
return array(
    'components' => array(
        'cache'=> array(
            'class' => 'CFileCache',
            'directoryLevel' => 2
        ),
        'db' => array(
            'class' => 'system.db.CDbConnection',
            'connectionString' => 'mysql:host=localhost;dbname=theone',
            'schemaCachingDuration' => 432000,
            'emulatePrepare' => True,
            'enableProfiling' => True,
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'tablePrefix' => 'v2_',
        ),
        /*
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CWebLogRoute',
                    'levels'=>'trace',
                    'categories' => 'system.db.*',
                )
            ),
        ),
        */
    )
);