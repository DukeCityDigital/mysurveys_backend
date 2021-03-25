<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Project;
use App\Participant;

class UserConfig extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //        create admin

        $makeParticipants = 3;

        $should_create_participants  = true;

        $DEFAULT_PASSWORD = \config('constants.APP_DEFAULT_PASSWORD');

        $admin = User::create([
            'username' => 'Admin',
            'email' => 'admin@mysurveys.santafe.edu',
            'password' => bcrypt($DEFAULT_PASSWORD)
        ]);
        $admin->assignRole('administrator');
        $admin->email_verified_at = Carbon::now();
        Participant::create(['user_id' => 1]);

        $admin->save();
        //initial researcher
        $researcher = User::create([
            'username' => 'Researcher',
            'email' => 'researcher@mysurveys.santafe.edutest',
            'password' => bcrypt($DEFAULT_PASSWORD)
        ]);
        $researcher->assignRole('researcher');
        $researcher->email_verified_at = Carbon::now();
        $researcher->save();

        DB::table("researchers")->insert([
            ['nickname' => 'researcher1', 'user_id' => 2]
        ]);
        Participant::create(['user_id' => 2]);

        $participant = User::create([
            'username' => 'test Participant',
            'email' => 'participant@mysurveys.santafe.edutest',
            'password' => bcrypt($DEFAULT_PASSWORD)
        ]);
        $participant->assignRole('participant');
        $participant->email_verified_at = Carbon::now();
        $participant->touch();
        $participant->save();

        $pData = [
            "first_name" => "TestParticipant",
            "family_name" => "Lastname",
            "birthyear" => "1970",
            "language_id" => "1",
            "user_id" => "3",
            "open_for_invitations" => "1",
            "open_for_invitations" => "0",
            "paypal_id" => "0",
            "paypal_id_status" => "Ok",
            "street" => "123 Test Ave",
            "zip" => "12345",
            "city" => "Test Town",
            "qualification_parents" => "1",
            "qualification_friends" => "1",
            "is_seed" => "1",
            "qualification_gm" => "1",
            "qualification_vac" => "1",
            "qualification_us" => "1",
        ];

        $participantActual = Participant::create($pData);
        $participant->assignRole('participant');

        //            $participantActual->id = 3;
        $participantActual->save();

        DB::transaction(function () {
            $DEFAULT_PASSWORD = \config('constants.APP_DEFAULT_PASSWORD');

            $participant = User::create([
                'id' => 4,
                'username' => 'phil@dukecitydigital.com',
                'email' => 'phil@dukecitydigital.com',
                'password' => bcrypt($DEFAULT_PASSWORD)
            ]);
            $participant->assignRole('participant');
            $participant->email_verified_at = Carbon::now();
            $participant->touch();
            $participant->save();

            $pData = [
                "first_name" => "DCD Participant",
                "family_name" => "Lastname",
                "birthyear" => "1970",
                "language_id" => "1",
                "user_id" => "4",
                "open_for_invitations" => "1",
                "open_for_invitations" => "0",
                "paypal_id" => "0",
                "paypal_id_status" => "Ok",
                "street" => "123 Test Ave",
                "zip" => "12345",
                "city" => "Test Town",
                "qualification_parents" => "1",
                "qualification_friends" => "1",
                "is_seed" => "1",
                "qualification_gm" => "1",
                "qualification_vac" => "1",
                "qualification_us" => "1",
            ];

            $participantActual = Participant::create($pData);
            //            $participantActual->id = 4;
            $participantActual->save();
        });


        //projects
        //Project
        $pData = [
            "project_title" => 'Example Project',
            "creator_userid" => 2,
            "state" => "Design",
            "start_state" => "Open",
            // "link_method" => 'Direct',
            "link" => "https://ww2.unipark.de/uc/scitest/",
            "responsible_person" => "researcher@support.santafe.edu",
            "description" => "Sample project description",
            "defaultend" => "2020-12-31 00:00:00",
            "defaultstart" => "2020-12-01 00:00:00",
            "payout_type" => "Fixed",
            "desired_sample_size" => 100,
            "desired_num_invitations" => 100,
            "max_participants" => 1000,
            "max_invitations" => 1000,
        ];
        $signupProject = Project::create($pData);
        //        $signupProject->researchers()->sync([2]);

        $faker = \Faker\Factory::create();

        //pause mock participants

        // TODO 
        // PL researcher 
        $researcher = User::create([
            'username' => 'PL Researcher',
            'email' => 'rphil@dukecitydigital.com',
            'password' => bcrypt($DEFAULT_PASSWORD)
        ]);
        $researcher->assignRole('researcher');
        $researcher->email_verified_at = Carbon::now();
        $researcher->save();

        DB::table("researchers")->insert([
            ['nickname' => 'rphil', 'user_id' => 2]
        ]);

        if ($should_create_participants) :
            for ($i = 0; $i <= $makeParticipants; $i++) :

                $firstn = $faker->firstName;

                $first = $firstn;
                $last = $faker->lastName;
                $year = $faker->year();

                $email = $first . "." . $last . "." . $faker->email();

                $city = $faker->city;

                $street = $faker->streetName;


                $fake = User::create([
                    'username' => $first,
                    'email' => $email,
                    'password' => bcrypt($DEFAULT_PASSWORD)
                ]);

                $fake->assignRole('participant');

                $id = $fake->id;

                $pData = [
                    "user_id" => $id,
                    "first_name" => $first,
                    "family_name" => $last,
                    "birthyear" => $year,
                    "language_id" => "1",
                    "open_for_invitations" => "1",
                    "is_seed" => "1",
                    "open_for_invitations" => "0",
                    "paypal_id" => "0",
                    "street" => $street,
                    "zip" => '87505',
                    "city" => $city,
                    "qualification_parents" => "1",
                    "qualification_friends" => "1",
                    "qualification_gm" => rand(1, 5),
                    "qualification_vac" => rand(1, 5),
                    "qualification_us" => "1",
                ];

                DB::table('participants')
                    ->insert($pData);
            endfor;
        endif;
    }
}
