<?php

namespace lucidtaz\yii2whoops;

use Whoops\Handler\HandlerInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\RunInterface;
use Yii;
use yii\base\ErrorHandler as BaseErrorHandler;
use yii\base\Request as BaseRequest;
use yii\console\Request as ConsoleRequest;
use yii\web\HttpException;
use yii\web\Request as WebRequest;
use yii\web\Response;

/**
 * Yii2 Error Handler using Whoops
 */
class ErrorHandler extends BaseErrorHandler
{
    /**
     * @var HandlerInterface Whoops handler to use. If not set, it will be
     * inferred from the type of request.
     */
    public $handler;

    /**
     * If this isn't here, Yii gets cranky
     */
    public $errorAction;

    public function init()
    {
        parent::init();
        if (!Yii::$container->hasSingleton(RunInterface::class)) {
            Yii::$container->setSingleton(RunInterface::class, Run::class);
            // NOTE: Override functions in SystemFacade to play nicer with Yii?
            // (Such as setting headers in the Response instead of directly)
        }
    }

    protected function renderException($exception)
    {
        /* @var $whoops RunInterface */
        $whoops = Yii::createObject(RunInterface::class);
        if ($this->handler === null) {
            $this->handler = $this->makeHandler();
        }
        $whoops->pushHandler($this->handler);

        $whoops->allowQuit(false);
        $whoops->writeToOutput(false); // We will take the output and put it into the Response
        $whoops->sendHttpCode(false); // We take care of this ourselves

        $response = $this->prepareResponse();
        $response->data = $whoops->handleException($exception);

        if ($exception instanceof HttpException) {
            $response->setStatusCode($exception->statusCode);
        } else {
            $response->setStatusCode(500);
        }

        $response->send();
    }

    private function prepareResponse()
    {
        if (Yii::$app->has('response')) {
            $response = Yii::$app->getResponse();
            // reset parameters of response to avoid interference with partially created response data
            // in case the error occurred while sending the response.
            $response->isSent = false;
            $response->stream = null;
            $response->data = null;
            $response->content = null;
        } else {
            $response = new Response();
            Yii::$app->set('response', $response);
        }
        return $response;
    }

    /**
     * @return HandlerInterface
     */
    private function makeHandler()
    {
        /* @var $request BaseRequest */
        $request = Yii::$app->getRequest();
        if ($request instanceof ConsoleRequest) {
            return new PlainTextHandler();
        } else if ($request instanceof WebRequest) {
            if ($request->getIsAjax()) {
                return new JsonResponseHandler();
            } else {
                $handler = new PrettyPageHandler();
                $handler->handleUnconditionally(true); // Prevent it from recognizing CLI mode (which is a test necessity) and therefore quitting
                return $handler;
            }
        }

        Yii::warning('Falling back to default Whoops handler.', __METHOD__);
        return new PrettyPageHandler();
    }
}
