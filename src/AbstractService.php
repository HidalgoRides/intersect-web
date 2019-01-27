<?php

namespace Intersect;

use Intersect\Application;

abstract class AbstractService {

    /** @var Application */
    private $application;

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

}