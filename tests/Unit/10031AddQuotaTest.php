<?php

namespace Tests\Unit;

use Tests\TestCase;

class AddQuotaTest extends TestCase {


    /**
     * testAddQuota
     *
     * @group 2
     */
    public function testAddQuota() {

        $response = $this->postJson('api/quota', [
            'project_id' => 1,
            'amount' => 22
                ]
        );
        $response
                ->assertStatus(200);
    }

}
