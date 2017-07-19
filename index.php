<?php
$yii    = dirname(__FILE__).'/protected/framework/yii.php';
$config = dirname(__FILE__).'/protected/config/main.php';

require_once dirname(__FILE__).'/protected/config/debug.template.php';
require_once $yii;

Yii::createWebApplication($config)->run();
