<?php

namespace Tests\Feature;

use Faker\Generator as Faker;
//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use \App\Role;
use \App\Participant;

class ProjectsTest extends TestCase
{

    /**
     * A basic unit test example.
     * @group 005
     * @return void
     */
    public function testProjectInit()
    {

        $faker = \Faker\Factory::create();

        $user = \App\User::find(2);
        $this->withoutMiddleware();

        $this->actingAs($user, 'api');
        $project_response = $this->postJson('api/projects', [
            'project_title' => $faker->company,
            'description' => $faker->bs,
            'exp_payout' => 20,
            'quota' => 455,
            'max_payout' => 25,
            'link_method' => 'Direct',
        ]);
        // $project_response->dump()->assertStatus(200);

        $_SELECTION['project_id'] = $project_response['data']['id'];

        $participant_role = \Spatie\Permission\Models\Role::where("name", "participant")->first();

        //make some participant users
        $participants_users = factory(\App\User::class, 25)->create();

        // $participants_users->roles()->attach($participant_role);

        $participant_role->users()->attach($participants_users);

        foreach ($participants_users as $p) {

            $p->assignRole('participant');
            $new = \App\Participant::checkIfNew($p->user_id);
            var_dump($new);
            // var_dump($p->id);
            if (!$new) {
                $last = $faker->lastName;

                $qualification_parents = rand(0, 1);
                $qualification_friends = rand(0, 5);
                $qualification_gm = rand(0, 5);
                $qualification_vac = rand(0, 5);
                $qualification_us = rand(0, 1);

                $paypal_id_status = rand(0, 1) ? 'Ok' : 'New';
                $n = new \App\Participant();
                $data =   [
                    'user_id' => $p->id,
                    'first_name' => '$user->name',
                    'family_name' => $last,
                    'paypal_id_status' => $paypal_id_status,
                    'paypal_id' => 0,
                    "qualification_parents" => $qualification_parents,
                    "qualification_friends" => $qualification_friends,
                    "is_seed" => 1,
                    "qualification_gm" => $qualification_gm,
                    "qualification_vac" => $qualification_vac,
                    "qualification_us" => $qualification_us,
                ];
                $n->fill($data);
                $n->save();
            }
            // $p->assignRole('participant');

            // $participant_role->users()->attach($friends_users);







            // $last = $faker->lastName;

            // $qualification_parents = rand(0, 1);
            // $qualification_friends = rand(0, 5);
            // $qualification_gm = rand(0, 5);
            // $qualification_vac = rand(0, 5);
            // $qualification_us = rand(0, 1);

            // $paypal_id_status = rand(0, 1) ? 'Ok' : '0';

            // $new = \App\Participant::checkIfNew($p->user_id);
            // $n = new \App\Participant();
            // $data =   [
            //     'user_id' => $p->id,
            //     'first_name' => $user->name,
            //     'family_name' => $last,
            //     'paypal_id_status' => $paypal_id_status,
            //     'paypal_id' => 0,
            //     "qualification_parents" => $qualification_parents,
            //     "qualification_friends" => $qualification_friends,
            //     "is_seed" => 1,
            //     "qualification_gm" => $qualification_gm,
            //     "qualification_vac" => $qualification_vac,
            //     "qualification_us" => $qualification_us,
            // ];
            // $n->fill($data);
            // $n->save();
            // var_dump($n);


            // if ($new !== false) {
            //     $n = new \App\Participant();
            //     $data =   [
            //         'user_id' => $p->id,
            //         'first_name' => $user->name,
            //         'family_name' => $last,
            //         'paypal_id_status' => $paypal_id_status,
            //         'paypal_id' => 0,
            //         "qualification_parents" => $qualification_parents,
            //         "qualification_friends" => $qualification_friends,
            //         "is_seed" => 1,
            //         "qualification_gm" => $qualification_gm,
            //         "qualification_vac" => $qualification_vac,
            //         "qualification_us" => $qualification_us,
            //     ];
            //     $n->fill($data);
            //     $n->save();


            // }


            //     $friends_users = factory(\App\User::class, 2)->create();

            //     $participant_role->users()->attach($friends_users);


            //     foreach ($friends_users as $fa) {

            //         $last2 = $faker->lastName;

            //         $friends_parts = factory(\App\Participant::class)->create(
            //             [
            //                 'user_id' => $fa->id,
            //                 'first_name' => $fa->name,
            //                 'family_name' => $last2,
            //                 'paypal_id_status' => $paypal_id_status,
            //                 'paypal_id' => 0,
            //                 "is_seed" => 0,
            //                 "seed_id" => $p->id
            //             ]
            //         );
            //     }
            // } else {
            //     echo 'participant exists';
            // }
        }
        $friends_users = factory(\App\User::class, 4)->create();

        foreach ($friends_users as $fa) {
            $fa->assignRole('participant');
            $new = \App\Participant::checkIfNew($fa->user_id);
            if (!$new) {
                $last = $faker->lastName;

                $qualification_parents = rand(0, 1);
                $qualification_friends = rand(0, 5);
                $qualification_gm = rand(0, 5);
                $qualification_vac = rand(0, 5);
                $qualification_us = rand(0, 1);

                $paypal_id_status = rand(0, 1) ? 'Ok' : 'New';
                $ddata =   [
                    'user_id' => $fa->id,
                    'first_name' => '$user->name',
                    'family_name' => $last,
                    'paypal_id_status' => $paypal_id_status,
                    'paypal_id' => 0,
                    "qualification_parents" => $qualification_parents,
                    "qualification_friends" => $qualification_friends,
                    "is_seed" => null,
                    "qualification_gm" => $qualification_gm,
                    "qualification_vac" => $qualification_vac,
                    "qualification_us" => $qualification_us,
                ];



                $nn = new \App\Participant();

                $nn->fill($ddata);
                $nn->save();
            }

            // $last2 = $faker->lastName;

            // $friends_parts = factory(\App\Participant::class)->create(
            //     [
            //         'user_id' => $fa->id,
            //         'first_name' => $fa->name,
            //         'family_name' => $last2,
            //         'paypal_id_status' => $paypal_id_status,
            //         'paypal_id' => 0,
            //         "is_seed" => 0,
            //         "seed_id" => $p->id
            //     ]
            // );
            $_SELECTION = [
                "categoryForm" => [],
                "eligible_peers" => "",
                "eligible_seed" => true,
                "paypal_status_ok" => true,
                "project_id" => null,
            ];
            $_SELECTION['project_id'] = $project_response['data']['id'];
            $selection_response = $this->postJson('api/get_advanced_selection', [
                'categoryForm' => $_SELECTION['categoryForm'],
                'eligible_peers' => $_SELECTION['eligible_peers'],
                'eligible_seed' => $_SELECTION['eligible_seed'],
                'paypal_status_ok' => $_SELECTION['paypal_status_ok'],
                'project_id' => $_SELECTION['project_id'],
            ]);

            $selection_response->dump();

            // $selection_users = json_decode($selection_response->getContent())->data;

            // var_dump($selection_users);

            $selection_ids = [];
            // foreach ($selection_users as $su) {
            //     $selection_ids[] = $su->user_id;
            // }

            // echo 'count su ids', count($selection_ids);

            // $create_selection = $this->postJson('api/create_selection', [
            //     'users' => $selection_ids,
            //     'project_id' => $_SELECTION['project_id'],
            // ]);
        }
    }

    /**
     * A basic unit test example.
     * @group 006
     * @return void
     */
    public function testProjectCycle()
    {



        /**
         * Config
         *
         *
         */
        $_USERS_COUNT = 2;

        $_INVITES_PER_USER = 2;

        //array for selection/invitation
        $_SELECTION = [
            "categoryForm" => [],
            "eligible_peers" => "",
            "eligible_seed" => true,
            "paypal_status_ok" => true,
            "project_id" => null,
        ];

        //Quota
        $_QUOTA = 5000;

        //        Set Project state to started after creation?

        $_INVITE_SLICE = -2; //num of users to invite right away
        //        ==============================
        $faker = \Faker\Factory::create();

        //first researcher user
        $role = \Spatie\Permission\Models\Role::where("name", "researcher")->first();

        $user = factory(\App\User::class)->create();
        $user->assignRole($role);
        //researcher actual
        // $r = factory(\App\Researcher::class)->create([
        //     'user_id' => $user->id,
        // ]);

        $r = \App\User::find(2);

        /**
         * Create Project - 200
         *
         */
        $start = '2020-11-20 20:24:00';
        $end = '2020-12-20 20:24:00';

        // $this->withoutMiddleware();

        // var_dump($r->id, $user->id);
        // $this->actingAs($r, 'api');
        // $project_response = $this->postJson('api/projects', [
        //     'project_title' => $faker->company,
        //     'description' => $faker->bs,
        //     'exp_payout' => 20,
        //     'quota' => $_QUOTA,
        //     'max_payout' => 25,
        //     'payout_type' => 'fixed',
        //     'link_method' => 'Direct',
        //     'link' => 'wwwtest.unipark.edu/survey676',
        //     'desired_sample_size' => 500,
        //     'desired_num_invitations' => 600,
        //     'defaultstart' => $start,
        //     'defaultend' => $end,
        //     'responsible_person' => 'responsible_test_person@rptest.test'
        // ]);
        $faker = \Faker\Factory::create();

        $user = \App\User::find(2);
        $this->withoutMiddleware();

        $this->actingAs($user, 'api');
        $project_response = $this->postJson('api/projects', [
            'project_title' => $faker->company,
            'description' => $faker->bs,
            'exp_payout' => 20,
            'quota' => 455,
            'max_payout' => 25,
            'link_method' => 'Direct',
        ]);
        $project_response->dump()->assertStatus(200);

        // exit();


        /**
         * Create participants
         *
         */
        //set project ID for participant selection
        $_SELECTION['project_id'] = $project_response['data']['id'];

        $participant_role = \Spatie\Permission\Models\Role::where("name", "participant")->first();

        //make some participant users
        $participants_users = factory(\App\User::class, $_USERS_COUNT)->create();

        $participant_role->users()->attach($participants_users);
        //populate the participants actual
        foreach ($participants_users as $p) {

            //fake last name
            $last = $faker->lastName;

            //make seeds first
            $qualification_parents = rand(0, 1);
            $qualification_friends = rand(0, 5);
            $qualification_gm = rand(0, 5);
            $qualification_vac = rand(0, 5);
            $qualification_us = rand(0, 1);

            $paypal_id_status = rand(0, 1) ? 'Ok' : '0';

            $new = \App\Participant::checkIfNew($p->user_id);
            //create seeds
            if ($new !== false) {
                $parts = factory(\App\Participant::class)->create(
                    [
                        'user_id' => $p->id,
                        'first_name' => $user->name,
                        'family_name' => $last,
                        'paypal_id_status' => $paypal_id_status,
                        'paypal_id' => 0,
                        "qualification_parents" => $qualification_parents,
                        "qualification_friends" => $qualification_friends,
                        "is_seed" => 1,
                        "qualification_gm" => $qualification_gm,
                        "qualification_vac" => $qualification_vac,
                        "qualification_us" => $qualification_us,
                    ]
                );
                //                echo 'seed ids';
                //                echo $parts->id . PHP_EOL;

                $friends_users = factory(\App\User::class, $_INVITES_PER_USER)->create();

                $participant_role->users()->attach($friends_users);

                //Invitations per seed (friend invitations)
                foreach ($friends_users as $fa) {

                    $last2 = $faker->lastName;

                    $friends_parts = factory(\App\Participant::class)->create(
                        [
                            'user_id' => $fa->id,
                            'first_name' => $fa->name,
                            'family_name' => $last2,
                            'paypal_id_status' => $paypal_id_status,
                            'paypal_id' => 0,
                            "is_seed" => 0,
                            "seed_id" => $p->id
                        ]
                    );
                }
            } else {
                echo 'participant exists';
            }
        }


        $selection_response = $this->postJson('api/get_advanced_selection', [
            'categoryForm' => $_SELECTION['categoryForm'],
            'eligible_peers' => $_SELECTION['eligible_peers'],
            'eligible_seed' => $_SELECTION['eligible_seed'],
            'paypal_status_ok' => $_SELECTION['paypal_status_ok'],
            'project_id' => $_SELECTION['project_id'],
        ]);

        $selection_users = json_decode($selection_response->getContent())->data;


        $selection_ids = [];
        foreach ($selection_users as $su) {
            $selection_ids[] = $su->id;
        }

        echo 'count su ids', count($selection_ids);

        $create_selection = $this->postJson('api/create_selection', [
            'users' => $selection_ids,
            'project_id' => $_SELECTION['project_id'],
        ]);

        $create_selection
            ->assertJson([
                'data' => true,
            ]);

        //        TODO here's where we do a numbers check'
        /**
         *
         * duplicate Qual checks on init
         */
        $test_send_emails = $this->postJson('api/send_project_invitations', [
            'TEST_MODE' => 'DEVELOPMENT',
            'ids' => $selection_ids,
            'project_id' => $_SELECTION['project_id'],
        ]);
        echo PHP_EOL . '*******************' . PHP_EOL . 'ERRORS: ' . PHP_EOL;
        $invite_errors = json_decode($test_send_emails->getContent())->data->ERRORS;
        foreach ($invite_errors as $ie) {
            echo $ie . PHP_EOL;
        }
        echo PHP_EOL . '*******************' . PHP_EOL .
            $Project = \App\Project::find($_SELECTION['project_id']);

        $Project->state = "Started";
        $Project->save();

        $test_send_emails2 = $this->postJson('api/send_project_invitations', [
            'TEST_MODE' => 'DEVELOPMENT',
            'ids' => $selection_ids,
            'project_id' => $_SELECTION['project_id'],
        ]);
        echo PHP_EOL . '*********SECOND TRY**********' . PHP_EOL . 'ERRORS: ' . PHP_EOL;
        $invite_errors2 = json_decode($test_send_emails2->getContent())->data->ERRORS;
        foreach ($invite_errors2 as $ie) {
            echo $ie . PHP_EOL;
        }
        echo PHP_EOL . '**********SECOND TRY*********' . PHP_EOL;
        echo PHP_EOL . '---------- PRODUCTION INVITE >>>>>>>>>>>>>' . PHP_EOL;
        $sliced_ids = array_slice($selection_ids, $_INVITE_SLICE);
        $_SELECTION['sliced_ids'] = $sliced_ids;
        $_SELECTION['TEST_MODE'] = 'PRODUCTION';
        usleep(1000000);

        $actual_send_emails2 = $this->postJson('api/send_project_invitations', [
            'TEST_MODE' => $_SELECTION['TEST_MODE'],
            'ids' => $_SELECTION['sliced_ids'],
            'project_id' => $_SELECTION['project_id'],
        ]);
        $actual_r = $actual_send_emails2->getContent();
        echo $actual_r;
        echo PHP_EOL . '----------.>>>>>>>>>>>>>' . PHP_EOL;
        echo PHP_EOL . '**********PARTICIPANTS ACCEPT INVITE*********' . PHP_EOL;
        //TODO test with non accepters
        $invitee_users = \App\User::whereIn("id", $_SELECTION['sliced_ids'])->get();
        echo 'have invitee users should match number of previous: ', count($invitee_users);
        $started_users = [];
        foreach ($invitee_users as $invitee) {

            $this->actingAs($invitee, 'api');
            $start_project = $this->postJson('api/start_project', [
                'project_id' => $_SELECTION['project_id'],
            ]);
            echo PHP_EOL . '==-->Invitee*********' . PHP_EOL;
            $sp_content = $start_project->getContent();

            $success = json_decode($sp_content)->success;
            if ($success == 'true') {
                $started_users[] = $invitee->id;
            }
        }
        echo PHP_EOL . '#################started###########' . PHP_EOL;


        echo PHP_EOL . '////////////PARTICIPANTS ACCEPTED? INVITE*********' . PHP_EOL;

        echo PHP_EOL . '------------------>>Successfully started userS: ' . count($started_users) . PHP_EOL;
        echo PHP_EOL . count($started_users) . '--->>Successfully started userS: ' . PHP_EOL;

        $code_holders = \App\ProjectParticipant::with('user')->whereIn("id", $started_users)
            ->where("projects_projectid", $Project->id)->get(['id', 'safeid']);

        echo 'count codes', count($code_holders);

        foreach ($code_holders as $ch) {
            echo PHP_EOL . 'userid ' . $ch->user->id . PHP_EOL;
            echo PHP_EOL . 'projid ' . $Project->id . PHP_EOL;
            echo PHP_EOL . 'code ' . $ch->safeid . PHP_EOL;


            $this->actingAs($ch->user, 'api');

            $code_confirm = $this->postJson('api/verify_project_code', [
                'project_id' => $Project->id,
                'code' => $ch->safeid
            ]);
            $cc_content = $code_confirm->getContent();
            echo 'cc decode';
            var_dump(json_decode($cc_content));

            echo 'cc content', json_decode($cc_content) . PHP_EOL;
        }



        //        submit those participants as project participants, ensuring the req'd conditional checks'
        //start the project
        //        do an invite on the selection, again ensuring the conditions
        //Invitees: accept the invite for some of the participants
        //some of those participants accept the project
        //        ->generate the project invite stuff incl. URL and handle the project link click types
        //        simulate a unipark survey and return to MySurveys, filling in necesary DB values
        //        study finish time entered, amount to pay entered in DB
        //RESEARCHER // ADMIN
        //        approves the payouts - projectparticipants amoutn to pay entered, validated = timestamp
        //        (this testing is the TDD that we can run and expect mass errors at first)
        //ADMIN - check validation for PP payments -
        //        ASSERT true(false) for now
        //        echo 'userid', $user, $r, $response->dump()->data;
        //                fwrite(STDERR, print_r($user));
        //        fwrite(STDERR, print_r($response->dump()));
        //create project based on researcher id

        $this->assertTrue(true);
    }
}
