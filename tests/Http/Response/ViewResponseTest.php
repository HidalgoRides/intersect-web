<?php

namespace Tests\Http\Response\Handlers;

use PHPUnit\Framework\TestCase;
use Intersect\Http\Response\ViewResponse;

class ViewResponseTest extends TestCase {

    /**
     * @runInSeparateProcess
     */
    public function test_handle()
    {
        ob_start();
        
        $viewResponse = new ViewResponse('test.php', ['data' => 'test']);
        $viewResponse->handle(dirname(__FILE__) . '/../templates');
        
        $response = ob_get_clean();

        $this->assertEquals('view passed: test', $response);
    }

}