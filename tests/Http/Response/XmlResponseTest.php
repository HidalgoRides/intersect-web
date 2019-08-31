<?php

namespace Tests\Http\Response\Handlers;

use PHPUnit\Framework\TestCase;
use Intersect\Http\Response\XmlResponse;

class XmlResponseTest extends TestCase {

    /**
     * @runInSeparateProcess
     */
    public function test_handle()
    {
        ob_start();
        
        $xmlResponse = new XmlResponse([
            'unit' => 'test',
            'foo' => [
                'bar' => 'bell'
            ],
            'taco' => [
                ['shell' => 'soft'],
                ['shell' => 'hard']
            ]
        ]);
        $xmlResponse->handle();
        
        $response = ob_get_clean();

        $response = str_replace("\n", '', $response);
        $response = str_replace("  ", '', $response);

        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?><root><unit>test</unit><foo><bar>bell</bar></foo><taco><shell>soft</shell></taco><taco><shell>hard</shell></taco></root>', $response);
    }

}