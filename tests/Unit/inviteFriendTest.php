<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Researcher;
use App\User;
use App\Participant;
// TODO TEST MANUAL
class inviteFriendTest extends TestCase
{
    /**
     * Invite friend test
     * @group register
     *@group 4
     * @return void
     */

    public function testExample()
    {
        $testappend = '@817test.test';

        $t = substr(time(), 5);
        $email = $t .  $testappend;

        $nick = strval($t) . 'nickname';

        $admin = User::find(1);

        $this->actingAs($admin);
        $response = $this->postJson('api/invite_researcher', [
            'nickname' => $nick,
            'email' =>  $email
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'user' => true,
            ]);


        $researcher_id = $response['user']['id'];

        $U = User::find($researcher_id);
        $U->email_verified_at = '2020-07-21 10:26:31';
        $U->save();


        $friend =  $t . 'friend';
        $friendnick = $t . 'Nick';
        $friendemail = 'F' . $t .  $testappend;


        $this->actingAs($U, 'api');
        $response = $this->postJson('api/invite_friend', [
            'nickname' => $friendnick,
            'email' =>  $friendemail
        ]);



        $response->assertStatus(201);
    }
}
