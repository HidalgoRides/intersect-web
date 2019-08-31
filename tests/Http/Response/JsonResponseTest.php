<?php

namespace Tests\Http\Response\Handlers;

use PHPUnit\Framework\TestCase;
use Intersect\Http\Response\JsonResponse;

class JsonResponseTest extends TestCase {

    /**
     * @runInSeparateProcess
     */
    public function test_handle()
    {
        ob_start();
        
        $jsonResponse = new JsonResponse([
            'unit' => 'test'
        ]);
        $jsonResponse->handle();
        
        $response = ob_get_clean();

        $this->assertEquals('{"unit":"test"}', $response);
    }

}