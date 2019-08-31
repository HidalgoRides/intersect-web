<?php

namespace Intersect\Http\Response;

abstract class AbstractResponse implements Response {

    private $body;
    private $status;

    abstract public function handle();

    public function __construct($body, $status = 200)
    {
        $this->body = $body;
        $this->status = (int) $status;

        http_response_code($status);
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getStatus()
    {
        return $this->status;
    }

}