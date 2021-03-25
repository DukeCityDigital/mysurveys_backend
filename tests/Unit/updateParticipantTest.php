<?php

namespace Tests\Unit;

use Tests\TestCase;


class updateParticipantTest extends TestCase {

    /**
     * A basic unit test example.
     * @group participant
     * @return void
     */
    public function testUpdateParticipant() {
        $admin = \App\User::find(1);

        $this->actingAs($admin, 'api');
        $response = $this->postJson('api/participants', [
            'id' => 3,
            'first_name' => 'Updated participantname'
                ]
        );
        $response
                ->assertStatus(200);
    }

}
