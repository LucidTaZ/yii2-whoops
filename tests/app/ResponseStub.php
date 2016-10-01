<?php

namespace lucidtaz\yii2whoops\tests\app;

class ResponseStub extends \yii\web\Response
{
    protected function sendHeaders()
    {
        // Prevent sending to mess up test
    }

    protected function sendContent()
    {
        // Prevent sending to mess up test
        // Inspect actual Response content with Yii::$app->getResponse()->content
    }
}
