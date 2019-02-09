<?php

use Illuminate\Database\Seeder;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->delete();
        DB::table('admins')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@foodie.com',
                'password' => bcrypt('123456'),
                'phone' => '+911234565434'
            ]
        ]);
    }
}
