<?php

namespace Intersect\Providers;

use Intersect\Commands\GenerateApplicationKeyCommand;
use Intersect\Commands\GenerateRouteCacheCommand;
use Intersect\Utils\ComposerUtils;
use Intersect\Http\ExceptionHandler;
use Intersect\Core\Http\Router\Route;
use Intersect\Http\Response\JsonResponse;
use Intersect\Http\DefaultExceptionHandler;
use Intersect\Providers\AppServiceProvider;

class DefaultProvider extends AppServiceProvider {

    public function init()
    {
        $this->app->bind(ExceptionHandler::class, DefaultExceptionHandler::class);
        $this->app->route(Route::get('/_version', 'Intersect\Controllers\StatusController#version'));
        $this->app->route(Route::get('/_health-check', 'Intersect\Controllers\StatusController#healthCheck'));
    }

    public function initCommands()
    {
        $this->app->command('app:generate-key', function() { return new GenerateApplicationKeyCommand(); });
        $this->app->command('app:generate-route-cache', function() { return new GenerateRouteCacheCommand(); });
    }

}