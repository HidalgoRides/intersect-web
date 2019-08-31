<?php

namespace Intersect\Http\Response;

use TimKippDev\ArrayToXmlConverter\ArrayToXmlConverter;

class XmlResponse extends AbstractResponse {

    protected $options;

    public function __construct(array $body, array $options = [], int $status = 200)
    {
        parent::__construct($body, $status);
        $this->options = $options;
    }

    public function handle()
    {
        header('Content-Type: application/xml');
        echo ArrayToXmlConverter::convert($this->getBody(), $this->options);
    }

}