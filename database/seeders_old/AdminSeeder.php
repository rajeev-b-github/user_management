<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Rajeev',
            'email' => 'rajeev.b.hestabit@gmail.com',
            'password' => bcrypt('123456'),
            'user_type' => 'admin',
            'is_approved' => 1,
        ]);
    }
}
