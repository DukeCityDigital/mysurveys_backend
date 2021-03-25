<?php

namespace Tests\Unit;

use Tests\TestCase;

class MotdTest extends TestCase
{

    /**
     * setMotd
     * deprecated
     * @group 2
     * @return void
     */
    public function testMotd()
    {

        /**
         * Participant MOTD
         */
        $p = \App\Participant::with('user')->first();

        $this->actingAs($p->user, 'api');
        $selection_response = $this->getJson('api/motd');
        $selection_response->assertStatus(200);
        /**
         * Researcher MOTD
         */
        $r = \App\Researcher::with('user')->first();
        $this->actingAs($r->user, 'api');
        $selection_response_r = $this->getJson('api/motd');

        $selection_response_r->assertStatus(200);
    }
}
