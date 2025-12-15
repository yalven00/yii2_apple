<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],

        'db' => [ 
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=apple_db',
            'username' => 'dbuser',
            'password' => 'dbpwd',
            'charset' => 'utf8',
        ],
    ],
];