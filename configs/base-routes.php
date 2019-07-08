<?php

use Intersect\Http\Router\Route;
use Intersect\Utils\ComposerUtils;
use Intersect\Http\Response\JsonResponse;

return [
    Route::get('/_version', function() {
        return new JsonResponse(ComposerUtils::getVersions());
    })
];