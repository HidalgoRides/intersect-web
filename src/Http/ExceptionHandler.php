<?php

namespace Intersect\Http;

interface ExceptionHandler {

    public function handle(\Exception $e);

}