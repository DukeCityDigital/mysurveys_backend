<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Participant;
use Faker\Generator as Faker;

$factory->define(Participant::class, function (Faker $faker) {


    return [
        "first_name" => $faker->name,
        "family_name" => $faker->lastName,
        "birthyear" => $faker->year(),
        "language_id" => "1",
        "id" => "",
        "open_for_invitations" => rand(0, 1),
        "paypal_id" => "0",
        "paypal_id_status" => 'New',
        "street" => $faker->streetName,
        "zip" => $faker->postcode,
        "city" => $faker->city,
        "qualification_parents" => 1,
        "qualification_friends" => 1,
        "is_seed" => 0,
        "qualification_gm" =>1,
        "qualification_vac" => 1,
        "qualification_us" => 1,
    ];
});
