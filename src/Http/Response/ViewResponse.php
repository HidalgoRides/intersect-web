<?php

namespace Intersect\Http\Response;

class ViewResponse extends AbstractResponse {

    private $data = [];
    private $viewFile;

    public function __construct($viewFile, array $data = [], $status = 200)
    {
        $this->viewFile = $viewFile;
        $this->data = $data;

        parent::__construct(null, $status);
    }

    public function handle($templatePath = null)
    {
        $templatePath = rtrim($templatePath, '/');

        extract($this->data);
        require_once $templatePath . '/' . ltrim($this->viewFile, '/');
    }

}