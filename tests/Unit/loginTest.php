<?php

// namespace Tests\Unit;
namespace Tests\Feature;

use Tests\TestCase;
use CreatesApplication;
use DatabaseMigrations;

class loginTest extends TestCase
{

    /**
     * testLogin
     *@group 002
     * @return void
     */
    public function testloginAdmin()
    {
        $this->withoutExceptionHandling();

        $ad = \App\User::find(1);


        $this->actingAs($ad, 'api');
        $response2 = $this->postJson('api/auth/login', [
            'email' => 'admin@mysurveys.santafe.edu',
            'password' => 'Mc{&V=HQ@T<z4YaL',
        ]);

        $response2
            ->assertStatus(200)
            ->assertJson([
                'access_token' => true,
            ]);
    }
}
