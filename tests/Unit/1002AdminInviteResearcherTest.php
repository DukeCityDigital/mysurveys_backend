<?php

namespace Tests\Feature;

use Tests\TestCase;
use WithoutMiddleware;

class InviteResearcherTest extends TestCase
{

    /**
     * A basic feature test example.
     * @group 003
     * @return void
     */
    public function testInviteResearcher()
    {

        $this->faker = \Faker\Factory::create();

        $admin = \App\User::find(1);

        // echo $admin->email;
        //fake researcher info
        $name = $this->faker->firstName;
        $email = $this->faker->email;

        // $user = $this->postJson('api/auth/login', [
        //     'email' => 'admin@mysurveys.santafe.edu',
        //     'password' => 'Mc{&V=HQ@T<z4YaL'
        // ]);

        // $user->dump();

        // $token = $user->token;
        $this->withoutMiddleware();
        // $this->actingAs($admin, 'web');
        // $this->actingAs(factory('App\User')->create());
        $response = $this->postJson('api/invite_researcher', [
            'nickname' => $name,
            'email' => $email
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true,
            ]);

        $researcher = \App\User::where("email", $email)->first();
        //R verification code
        $r_code = $researcher->verification_code;

        //        $this->actingAs(\Illuminate\Support\Facades\Auth::Guest());
        $response2 = $this->postJson('api/auth/check_verification_code', [
            'code' => $r_code
        ]);
        $response2->dump();


        $response2
            ->assertStatus(200)
            ->assertJson([
                'user' => true,
            ]);
    }
}
