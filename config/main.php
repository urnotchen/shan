<?php
$params = array_merge(
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php'),
    require(__DIR__ . '/main-local.php')
);

return [
    'id' => 'app-backend',
    'name' => '齐齐哈尔慈善总会',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'app\controllers',
    'bootstrap' => ['log'],
    'language' => 'zh-CN',

    'components' => [
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'fileMap' => [
                        'app' => 'yii.php',
                        'app/error' => 'error.php',
                    ],

                ],
            ],
        ],
        'sidebarItems' => [
            'class' => 'app\common\helpers\SidebarItems',
        ],
        'db' => require(__DIR__ . '/db.php'),
        'request' => [
            'csrfParam' => '_csrf',
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json' => 'yii\web\JsonParser',
            ]
        ],
        'timeFormatter' => [
            'class' => 'app\components\TimeFormatter',
        ],
        'user' => [
            'class'           => 'app\components\User',
            'identityClass'   => 'app\models\BaseUser',
            'enableAutoLogin' => true,
            'loginUrl'        => ['site/login'],
            'identityCookie'  => [
                'name'     => '_identity',
                'httpOnly' => true,
            ],
        ],
        'session' => [
            'class' => 'yii\web\Session',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
//        'i18n' => [
//            'translations' => [
//                'yii' => [
//                    'class' => 'yii\i18n\PhpMessageSource',
//                    'basePath' => '@app/messages',
//                ],
//            ],
//        ],
        'assetManager' => [
            'appendTimestamp' => true,
            'forceCopy' => false,
            'bundles' => [
                'dmstr\web\AdminLteAsset' => [
                    'css' => [
                        '/css/AdminLTE.min.css',
                    ],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl'     => true,
            'enableStrictParsing' => true,
            'showScriptName'      => false,
            'rules' => [
                ''       => 'site/index',
                'logout' => 'site/logout',
                '<controller:[a-z-]+>' => '<controller>/index',
                '<controller:[a-z-]+>/' => '<controller>/index',
                '<controller:[a-z-]+>/<action:[a-z-]+>' => '<controller>/<action>',
                '<controller:[a-z-]+>/<action:[a-z-]+>/<id:[0-9]+>' => '<controller>/<action>',
                '<module:[a-z-]+>/<controller:[a-z-]+>/<action:[a-z-]+>' => '<module>/<controller>/<action>',
            ],
        ],
        'urlManagerLogin' => [
            'class'               => 'yii\web\urlManager',
            'enablePrettyUrl'     => true,
            'enableStrictParsing' => true,
            'showScriptName'      => false,
            'rules' => [
                'login'   => 'site/login',
                'logout'  => 'site/logout',
            ],
        ],

        'user' => [
            'class'           => 'app\components\User',
            'identityClass'   => 'app\models\BaseUser',
            'enableAutoLogin' => true,
            'loginUrl'        => APP_DOMAIN_SCHEMA. APP_BASE_DOMAIN . '/site/login',
            'identityCookie'  => [
                'name'     => '_identity',
                'httpOnly' => true,
            ],
        ],
    ],
    'params' => $params,
];
