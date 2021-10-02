<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Handlers\SendChatActionHandler;
use App\Http\Handlers\SendMessageHandler;
use App\Http\Middlewares\ApiErrorHandlerMiddleware;
use App\Http\Middlewares\BaseValidateMiddleware;
use App\Http\Middlewares\JsonBodyParserMiddleware;
use Dotenv\Dotenv;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Slim\App as SlimApp;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Yiisoft\Validator\Validator;
use Yiisoft\Validator\ValidatorInterface;

class App
{
    private SlimApp $slim;
    public function __construct()
    {
        $container = $this->getContainer();
        AppFactory::setContainer($container);
        AppFactory::setResponseFactory($container->get(ResponseFactoryInterface::class));
        $this->slim = AppFactory::create();
        $this->registerRoutes();
    }

    private function getContainer() :ContainerInterface
    {
        $dotenv = Dotenv::createImmutable(BASE_DIR);
        $dotenv->load();
        $settings = [
            'redis_url' => sprintf("@%s:%s", $_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']),
            'test_token' => '798987043:AAEFbSVifXq8POi5Sg4FlayAkrh7buJwcSs',
            'test_chat_id' => -1001176886276
        ];
        $definitions = [
            ValidatorInterface::class => function() {
                return new Validator();
            },
            TaskManager::class => function(ContainerInterface $c) {
                return new TaskManager($c->get('redis_url'));
            },
            TelegramApi::class => function(){
                return new TelegramApi();
            },
        ];
        $implementations = [
            RequestFactoryInterface::class => Psr17Factory::class,
            ResponseFactoryInterface::class => Psr17Factory::class,
            ServerRequestFactoryInterface::class => Psr17Factory::class,
            StreamFactoryInterface::class => Psr17Factory::class,
            UploadedFileFactoryInterface::class => Psr17Factory::class,
            UriFactoryInterface::class => Psr17Factory::class,
        ];
        return new Container($settings, $implementations, $definitions);
    }

    private function registerRoutes()
    {
        $this->slim->group('', function (RouteCollectorProxyInterface $group) {
            $group->post('/sendMessage', SendMessageHandler::class);
            $group->post('/sendChatAction', SendChatActionHandler::class);
        })
            ->add(BaseValidateMiddleware::class)
            ->add(JsonBodyParserMiddleware::class)
            ->add(ApiErrorHandlerMiddleware::class)
        ;

        $this->slim->post('/stopTask/{bot_id}', function (Request $request, Response $response, $args) {
            $redis = new TaskManager('@localhost:6379');
            $redis->remove(intval($args['bot_id']));
            return $response;
        });
    }

    public function run()
    {
        $this->slim->run();
    }
}