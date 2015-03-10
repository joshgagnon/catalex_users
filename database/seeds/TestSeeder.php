<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TestSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$this->call('DatabaseSeeder');

		$this->call('TestDataSeeder');
	}
}
