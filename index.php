<?php
define('W_TIME', $_SERVER['REQUEST_TIME']);
define('W_DATE', date('Y-m-d', W_TIME));
define('W_HOST', $_SERVER['HTTP_HOST']);
define('W_ROOT_PATH', dirname(__FILE__));

define('YII_DEBUG', True);
define('YII_TRACE_LEVEL', 3);

$yiiFile = W_ROOT_PATH . '/framework/yii.php';
$wFile = W_ROOT_PATH . '/protected/components/W.php';

require($wFile);
require($yiiFile);

Yii::createWebApplication(W::getConfig())->run();
