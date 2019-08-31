<?php

namespace Tests\Http\Response\Handlers;

use PHPUnit\Framework\TestCase;
use Intersect\Http\Response\TwigResponse;

class TwigResponseTest extends TestCase {

    /**
     * @runInSeparateProcess
     */
    public function test_handle()
    {
        ob_start();
        
        $viewResponse = new TwigResponse('test.twig', ['data' => 'test']);
        $viewResponse->handle(dirname(__FILE__) . '/../templates');
        
        $response = ob_get_clean();

        $this->assertEquals('twig passed: test', $response);
    }

}