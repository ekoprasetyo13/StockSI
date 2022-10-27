<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdSd extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(['id' => 1], [
            'name' => "AdminKU",
            'email' => "admin@mail.com",
            'password' => bcrypt("#kuadmiDI"),
        ]);
    }
}
