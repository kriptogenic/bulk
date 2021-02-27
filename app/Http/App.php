<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Handlers\SendChatActionHandler;
use App\Http\Handlers\SendMessageHandler;
use App\Http\Middlewares\ApiErrorHandlerMiddleware;
use App\Http\Middlewares\BaseValidateMiddleware;
use App\Http\Middlewares\JsonBodyParserMiddleware;
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
use Valitron\Validator;
use Yiisoft\Validator\ValidatorInterface;

class App
{
    private SlimApp $slim;
    public function __construct()
    {
        $container = $this->getContainer();
        AppFactory::setContainer($container);
        AppFactory::setResponseFactory($container->get(ResponseFactoryInterface::class));
        $this->registerValidatorRules();
        $this->slim = AppFactory::create();
        $this->registerRoutes();
    }

    private function registerValidatorRules()
    {
        Validator::addRule('exclude_if_entities',
            static function ($field, $value, array $params, array $fields) {
                return !array_key_exists('entities', $fields);
            }, 'You must choose either parse_mode or entities. You cannot choose both');
        Validator::addRule('parse_mode_values',
            static function ($field, $value, array $params, array $fields) {
                return in_array(mb_strtolower($value), ['html', 'markdown', 'markdownv2']);
            }, '{field} can be only one of these values: html, markdown, markdownv2');
        Validator::addRule('my_array',
            static function($field, $value, array $params, array $fields) {
                return is_array($value);
            }, '{field} must be an array');
        Validator::addRule('string',
            static function($field, $value, array $params, array $fields){
                return is_string($value);
            }, '{field} must be a string');
    }

    private function getContainer() :ContainerInterface
    {
        $settings = [
            'redis_url' => '@localhost:6379',
            'test_token' => '798987043:AAEFbSVifXq8POi5Sg4FlayAkrh7buJwcSs',
            'test_chat_id' => -1001176886276
        ];
        $definitions = [
            ValidatorInterface::class => function() {
                return new \Yiisoft\Validator\Validator();
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