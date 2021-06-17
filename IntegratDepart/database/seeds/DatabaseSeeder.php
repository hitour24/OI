<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SourcesSeeder::class);
        $this->call(DomainsSeeder::class);
        $this->call(CountriesSeeder::class);
        $this->call(MinbidSeeder::class);
    }
}
