<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        for ($i = 0; $i < 60000; $i++) {
            DB::table('user')->insert([
                'name' => Str::random(10),
                'phone' => Str::random(10),
                //'created_at' => time()            
            ]);
        }

        //User::factory()->times(1)->create();
    }
}
