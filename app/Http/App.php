<?php

namespace App\Http;

use App\Http\Handlers\SendMessageHandler;
use App\Http\Middlewares\ApiErrorHandlerMiddleware;
use App\Memory\Redis;
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App as SlimApp;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Valitron\Validator;

class App
{
    private SlimApp $slim;
    public function __construct()
    {
        AppFactory::setContainer($this->getContainer());
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
        $settings = new KeyValue([
            'redis_url' => '@localhost:6379'
        ]);
        return new Container($settings);
    }

    private function registerRoutes()
    {
        $this->slim->group('', function (RouteCollectorProxyInterface $group) {
            $group->post('/sendMessage', SendMessageHandler::class);
        })->add(new ApiErrorHandlerMiddleware($this->slim->getResponseFactory()));

        $this->slim->post('/stopTask/{bot_id}', function (Request $request, Response $response, $args) {
            $redis = new Redis('@localhost:6379');
            $redis->delData($args['bot_id']);
            return $response;
        });
    }

    public function run()
    {
        $this->slim->run();
    }
}