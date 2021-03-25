<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
class RoleSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $a = Role::create(['name' => 'administrator']);
        $r = Role::create(['name' => 'researcher']);
        $p = Role::create(['name' => 'participant']);
    }

}
