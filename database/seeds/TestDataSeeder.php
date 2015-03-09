<?php

use Illuminate\Database\Seeder;

use App\User;
use App\Address;
use App\Organisation;
use App\BillingDetail;

class TestDataSeeder extends Seeder {

	/**
	 * Create data set required for testing.
	 *
	 * @return void
	 */
	public function run() {
		// Create 2 normal users, 1 inactive, 1 deleted, 1 inactive + deleted in two organisations
		$a1 = Address::create([
			'line_1' => '1 Test Street',
			'city' => 'Test City 1',
			'iso3166_country' => 'NZ',
		]);
		$b1 = BillingDetail::create([
			'period' => 'monthly',
			'address_id' => $a1->id
		]);
		$o1 = Organisation::create([
			'name' => 'Organisation 1',
			'billing_detail_id' => $b1->id
		]);
		User::create([
			'first_name' => 'Test1',
			'last_name' => 'User1',
			'email' => 'test1@example.com',
			'password' => bcrypt('password'),
			'organisation_id' => $o1->id,
			'active' => true,
		]);
		User::create([
			'first_name' => 'Test2',
			'last_name' => 'User2',
			'email' => 'test2@example.com',
			'password' => bcrypt('password'),
			'organisation_id' => $o1->id,
			'active' => true,
		]);
		User::create([
			'first_name' => 'Test3',
			'last_name' => 'User3',
			'email' => 'test3@example.com',
			'password' => bcrypt('password'),
			'organisation_id' => $o1->id,
			'active' => false,
		]);
		$u4 = User::create([
			'first_name' => 'Test4',
			'last_name' => 'User4',
			'email' => 'test4@example.com',
			'password' => bcrypt('password'),
			'organisation_id' => $o1->id,
			'active' => true,
		]);
		$u4->delete();
		$u5 = User::create([
			'first_name' => 'Test5',
			'last_name' => 'User5',
			'email' => 'test5@example.com',
			'password' => bcrypt('password'),
			'organisation_id' => $o1->id,
			'active' => false,
		]);
		$u5->delete();


		$a2 = Address::create([
			'line_1' => '2 Test Street',
			'city' => 'Test City 2',
			'iso3166_country' => 'NZ',
		]);
		$b2 = BillingDetail::create([
			'period' => 'monthly',
			'address_id' => $a2->id
		]);
		$o2 = Organisation::create([
			'name' => 'Organisation 2',
			'billing_detail_id' => $b2->id
		]);
		User::create([
			'first_name' => 'Test6',
			'last_name' => 'User6',
			'email' => 'test6@example.com',
			'password' => bcrypt('password'),
			'organisation_id' => $o2->id,
			'active' => true,
		]);
		User::create([
			'first_name' => 'Test7',
			'last_name' => 'User7',
			'email' => 'test7@example.com',
			'password' => bcrypt('password'),
			'organisation_id' => $o2->id,
			'active' => true,
		]);
		User::create([
			'first_name' => 'Test8',
			'last_name' => 'User8',
			'email' => 'test8@example.com',
			'password' => bcrypt('password'),
			'organisation_id' => $o2->id,
			'active' => false,
		]);
		$u9 = User::create([
			'first_name' => 'Test9',
			'last_name' => 'User9',
			'email' => 'test9@example.com',
			'password' => bcrypt('password'),
			'organisation_id' => $o2->id,
			'active' => true,
		]);
		$u9->delete();
		$u10 = User::create([
			'first_name' => 'Test10',
			'last_name' => 'User10',
			'email' => 'test10@example.com',
			'password' => bcrypt('password'),
			'organisation_id' => $o2->id,
			'active' => false,
		]);
		$u10->delete();
	}
}
