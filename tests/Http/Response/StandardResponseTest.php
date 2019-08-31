<?php

namespace Tests\Http\Response;

use PHPUnit\Framework\TestCase;
use Intersect\Http\Response\StandardResponse;

class StandardResponseTest extends TestCase {

    public function test_response()
    {
        $response = new StandardResponse('body');
        $this->assertEquals('body', $response->getBody());
        $this->assertEquals(200, $response->getStatus());
    }

    public function test_response_overrideStatusCode()
    {
        $response = new StandardResponse('body', 404);
        $this->assertEquals('body', $response->getBody());
        $this->assertEquals(404, $response->getStatus());
    }

    /**
     * @runInSeparateProcess
     */
    public function test_handle_array()
    {
        ob_start();
        
        $standardResponse = new StandardResponse([
            'unit' => 'test'
        ]);
        $standardResponse->handle();
        
        $response = ob_get_clean();

        $this->assertEquals('{"unit":"test"}', $response);
    }

    /**
     * @runInSeparateProcess
     */
    public function test_handle_string()
    {
        ob_start();

        $standardResponse = new StandardResponse('unit');
        $standardResponse->handle();
        
        $response = ob_get_clean();

        $this->assertEquals('unit', $response);
    }

}