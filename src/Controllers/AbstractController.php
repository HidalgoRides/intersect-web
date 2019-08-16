<?php

namespace Intersect\Controllers;

use Intersect\Application;
use Intersect\Core\Http\Request;

abstract class AbstractController {

    /** @var Application */
    private $application;

    /** @var Request */
    private $request;

    public function init() {}

    /**
     * @return Application
     */
    protected function getApplication()
    {
        if (is_null($this->application))
        {
            $this->application = Application::instance();
        }

        return $this->application;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param $location
     * @param $statusCode
     */
    protected function redirect($location, $statusCode = 302)
    {
        header('Location: ' . $location, true, $statusCode);
        exit();
    }

}