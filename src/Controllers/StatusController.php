<?php

namespace Intersect\Controllers;

use Intersect\Utils\ComposerUtils;
use Intersect\Http\Response\JsonResponse;

class StatusController extends AbstractController {

    public function version()
    {
        return new JsonResponse(ComposerUtils::getVersions());
    }

    public function healthCheck()
    {
        return new JsonResponse(['status' => 'passed']);
    }

}