<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;

class DatabaseSeeder extends Seeder {

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {
        $this->call(RoleSeeder::class);#gtg
        $this->call(UserConfig::class);

        $this->call(SettingsSeeder::class);
        $this->call(EmailTemplates::class);
    }

}
