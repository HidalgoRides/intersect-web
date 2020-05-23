<?php

namespace Intersect\Http\Response;

class RedirectResponse extends AbstractResponse {

    private $data;
    private $location;

    public function __construct($location, array $data = [], $status = 302)
    {
        parent::__construct(null, $status);
        $this->data = $data;
        $this->location = $location;
    }

    public function handle()
    {
        header('Location: ' . $this->location);
        exit();
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

}