<?php

namespace Intersect\Http\Response;

interface Response {

    public function getBody();
    public function getStatus();
    public function handle();

}