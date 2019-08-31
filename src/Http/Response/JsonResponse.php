<?php

namespace Intersect\Http\Response;

class JsonResponse extends AbstractResponse {

    public function handle()
    {
        header('Content-Type: application/json');
        echo json_encode($this->getBody());
    }

}