<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();

ini_set('upload_max_filesize', \Yii::$app->params['maxFileSize']);
ini_set('post_max_size', \Yii::$app->params['maxFileSize']);
