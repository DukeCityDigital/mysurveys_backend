<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Researcher;
use Faker\Generator as Faker;

$factory->define(Researcher::class, function (Faker $faker) {
    return [
        //
        "nickname" => $faker->name,
        "user_id" => 552,
    ];
});
