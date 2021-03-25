<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
//use DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Faker\Generator as Faker;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{

    use CreatesApplication;
    use DatabaseMigrations;
    //        DatabaseTransactions;
    //

    /**
     * Set up the test
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');
    }

    /**
     * Reset the migrations
     */
    public function tearDown(): void
    {
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

    /**
     * Set the currently logged in user for the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string|null                                $driver
     * @return $this
     */
    public function actingAs($user, $driver = null)
    {
        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', "Bearer {$token}");
        parent::actingAs($user);
        // var_dump($user);
        return $this;
    }
}
