<?php

return [
    'id' => 'yii2-whoops-unit',
    'basePath' => dirname(__DIR__),
    'components' => [
        'response' => [
            'class' => lucidtaz\yii2whoops\tests\app\ResponseStub::class,
        ],
    ],
];
