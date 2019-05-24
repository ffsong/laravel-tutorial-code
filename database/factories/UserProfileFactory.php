<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\UserProfile;
use Faker\Generator as Faker;

$factory->define(UserProfile::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(App\User::class)->create()->id;
        },
        'bio' => rand(100,9999),
        'city' => $faker->randomDigit,
        'hobby' => '{}',
    ];
});
