<?php

namespace Intersect\Providers;

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

        $this->app->route(Route::get('/_version', function() {
            return new JsonResponse(ComposerUtils::getVersions());
        }));

        $this->app->route(Route::get('/_health-check', function() {
            return new JsonResponse(['status' => 'passed']);
        }));
    }

}