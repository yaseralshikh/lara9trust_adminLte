<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = \App\Models\User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'description' => 'Web Designer',
            'email' => 'super_admin@app.com',
            'phone' => '0500000000',
            'address' => 'Street 123, Demo City',
            'password' => bcrypt('123123123'),
        ]);

        $user->attachRole('superadmin');
    }
}
