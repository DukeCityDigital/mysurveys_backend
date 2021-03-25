<?php

namespace Tests\Unit;

use Tests\TestCase;


class loginAdminTest extends TestCase
{
    /**
     * testLoginAdmin
     *@group 004
     * @return void
     */
    public function testloginAdmin()
    {
        $this->assertTrue(true);
        $ad = \App\User::find(1);
        $this->actingAs($ad, 'api');
        $response2 = $this->postJson('api/auth/login', [
            'email' => 'admin@mysurveys.santafe.edu',
            'password' => 'Testpass12!',
        ]);

        $response2
            ->assertStatus(200)
            ->assertJson([
                'access_token' => true,
            ]);
    }
}
