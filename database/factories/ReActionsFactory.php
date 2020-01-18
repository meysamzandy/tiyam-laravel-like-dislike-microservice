<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;

$factory->define(\App\ReActions::class, function (Faker $faker) {
    return [
        'uuid' =>$faker->uuid,
        'nid' => $faker->numberBetween(1,999999),
        'status' => $faker->numberBetween(0,2),
        'change_number' => $faker->numberBetween(0,5),
    ];
});
