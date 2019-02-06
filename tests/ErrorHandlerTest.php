<?php

namespace lucidtaz\yii2whoops\tests;

use Exception;
use lucidtaz\yii2whoops\ErrorHandler;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\ExitException;
use yii\base\UnknownPropertyException;
use yii\web\Application;
use yii\web\Response;

class ErrorHandlerTest extends TestCase
{
    public function setUp() {
        new Application(require(__DIR__ . '/app/config/web.php')); // Registers itself with Yii::$app
    }

    public function tearDown() {
        if (isset(Yii::$app)) {
            try {
                Yii::$app->end();
            } catch (ExitException $e) {
                // Ignore, normal behavior
            }
        }
    }

    public function testDependencyInjection()
    {
        $handler = Yii::createObject([
            'class' => ErrorHandler::class,
        ]);
        $this->assertInstanceOf(ErrorHandler::class, $handler);
    }

    public function testDependencyInjectionWithExtraneousAttributes()
    {
        // Verify that the readme pointer about removing the "errorAction" attribute is useful
        $this->expectException(UnknownPropertyException::class);
        $this->expectExceptionMessage('Setting unknown property: lucidtaz\yii2whoops\ErrorHandler::errorAction');

        // The config as in default yii2-app-basic
        Yii::createObject([
            'class' => ErrorHandler::class,
            'errorAction' => 'site/error',
        ]);
    }

    public function testHandleException()
    {
        $errorHandler = new ErrorHandler();
        $errorHandler->discardExistingOutput = false; // Defaults to true but it messes up PHPUnit's ob_level counting.
        $exception = new Exception('Intentional unit test exception');

        $errorHandler->handleException($exception); // TODO: Make sure it uses a response object instead of direct output

        /* @var $response Response */
        $response = Yii::$app->getResponse();
        $this->assertNotEmpty($response->data);
        $this->assertContains('Who'.'ops', $response->content, 'Broken up so we can be sure that it does not come from this stack trace');
        $this->assertContains('Shaddlepop', $response->content, 'Unique string should appear in output, since *this* very line appears somewhere in the rendered strack trace');

        $this->assertEquals(500, $response->statusCode);
    }
}
