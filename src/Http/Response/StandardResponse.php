<?php

namespace Intersect\Http\Response;

class StandardResponse extends AbstractResponse {

    public function handle()
    {
        $body = $this->getBody();

        if (is_object($body))
        {
            throw new \Exception('Objects are not supported in default response handler');
        }

        if (is_array($body))
        {
            header('Content-Type: application/json');
            $body = json_encode($body);
        }
        
        echo $body;
    }

}