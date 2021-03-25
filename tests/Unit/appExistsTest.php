<?php

namespace Tests\Unit;

use Tests\TestCase;

class appExistsTest extends TestCase
{
    /**
     * Test App exists
     *@group 00
     * @return void
     */
    public function testAppExists()
    {
        $this->assertTrue(true);
         $response = $this->getJson('api/test');

        $response->assertStatus(200);
    }
}
