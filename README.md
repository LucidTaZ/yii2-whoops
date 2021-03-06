Yii2 bindings for Whoops
==========================

This library provides easy integration of
[filp/whoops](https://github.com/filp/whoops) into
[Yii2](https://github.com/yiisoft/yii2). Whoops is a pretty exception and error
formatter. This library enables you to use it by configuring an ErrorHandler in
your application config.

USAGE
-----

Configure `web.php` to disable Yii's built-in error handler and use the new one:

```php
<?php
$config = [
    // ...
    'components' => [
        'errorHandler' => [
            'class' => 'lucidtaz\yii2whoops\ErrorHandler',

            // NOTE: yii2-app-basic comes with this errorAction line by default,
            // be sure to comment/remove it, or you will get an
            // UnknownAttributeException when running your code:
            // 'errorAction' => 'site/error'
        ],
        // ...
    ],
    // ...
];
```

Or to only use it in Dev+Debug mode, to prevent your source code displaying in
production:

```php
<?php
$config = [
    // ...
    'components' => [
        'errorHandler' => YII_ENV_DEV && YII_DEBUG ? [
            'class' => 'lucidtaz\yii2whoops\ErrorHandler',
        ] : [
            'class' => 'yii\web\ErrorHandler',
            'errorAction' => 'site/error'
        ],
        // ...
    ],
    // ...
];
```
