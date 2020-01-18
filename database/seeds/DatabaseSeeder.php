<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $reActions = factory(\App\ReActions::class,10)->create();

//        $reActions->each(function ($reActions){
//            factory(\App\Total::class,1)->create(['nid' => $reActions->nid ]);
//        });

        // $this->call(UsersTableSeeder::class);
    }
}
