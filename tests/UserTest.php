<?php

use App\User;
use App\Organisation;

class UserTest extends TestCase {

	/**
	 * Test that users and organisation members returns correct set of users for combinations
	 * of active/inactive live/deleted.
	 *
	 * @return void
	 */
	public function testActiveUsers() {
		// All users
		$usersActive = User::all();
		$this->assertEquals(4, count($usersActive));
		foreach(['Test1 User1', 'Test2 User2', 'Test6 User6', 'Test7 User7'] as $index => $name) {
			$this->assertEquals($name, $usersActive[$index]->fullName());
		}

		$usersWithDeleted = User::withTrashed()->get();
		$this->assertEquals(6, count($usersWithDeleted));
		foreach(['Test1 User1', 'Test2 User2', 'Test4 User4', 'Test6 User6', 'Test7 User7', 'Test9 User9'] as $index => $name) {
			$this->assertEquals($name, $usersWithDeleted[$index]->fullName());
		}

		$usersWithInactive = User::withInactive()->get();
		$this->assertEquals(6, count($usersWithInactive));
		foreach(['Test1 User1', 'Test2 User2', 'Test3 User3', 'Test6 User6', 'Test7 User7', 'Test8 User8'] as $index => $name) {
			$this->assertEquals($name, $usersWithInactive[$index]->fullName());
		}

		$allUsers1 = User::withInactive()->withTrashed()->get();
		$allUsers2 = User::withTrashed()->withInactive()->get();
		$this->assertEquals(10, count($allUsers1));
		$this->assertEquals(10, count($allUsers2));
		foreach(['Test1 User1', 'Test2 User2', 'Test3 User3', 'Test4 User4', 'Test5 User5', 'Test6 User6', 'Test7 User7', 'Test8 User8', 'Test9 User9', 'Test10 User10'] as $index => $name) {
			$this->assertEquals($name, $allUsers1[$index]->fullName());
			$this->assertEquals($name, $allUsers2[$index]->fullName());
		}

		// Organisation 1
		$o1 = Organisation::find(1);

		$o1ActiveMembers = $o1->members;
		$this->assertEquals(2, count($o1ActiveMembers));
		foreach(['Test1 User1', 'Test2 User2'] as $index => $name) {
			$this->assertEquals($name, $o1ActiveMembers[$index]->fullName());
		}

		$o1ActiveWithDeleted = $o1->members()->withTrashed()->get();
		$this->assertEquals(3, count($o1ActiveWithDeleted));
		foreach(['Test1 User1', 'Test2 User2', 'Test4 User4'] as $index => $name) {
			$this->assertEquals($name, $o1ActiveWithDeleted[$index]->fullName());
		}

		$o1MembersWithInactive = $o1->members()->withInactive()->get();
		$this->assertEquals(3, count($o1MembersWithInactive));
		foreach(['Test1 User1', 'Test2 User2', 'Test3 User3'] as $index => $name) {
			$this->assertEquals($name, $o1MembersWithInactive[$index]->fullName());
		}

		$o1AllMembers1 = $o1->members()->withInactive()->withTrashed()->get();
		$o1AllMembers2 = $o1->members()->withTrashed()->withInactive()->get();
		$this->assertEquals(5, count($o1AllMembers1));
		$this->assertEquals(5, count($o1AllMembers2));
		foreach(['Test1 User1', 'Test2 User2', 'Test3 User3', 'Test4 User4', 'Test5 User5'] as $index => $name) {
			$this->assertEquals($name, $o1AllMembers1[$index]->fullName());
			$this->assertEquals($name, $o1AllMembers2[$index]->fullName());
		}

		// Organisation 2
		$o2 = Organisation::find(2);

		$o2ActiveMembers = $o2->members;
		$this->assertEquals(2, count($o2ActiveMembers));
		foreach(['Test6 User6', 'Test7 User7'] as $index => $name) {
			$this->assertEquals($name, $o2ActiveMembers[$index]->fullName());
		}

		$o2ActiveWithDeleted = $o2->members()->withTrashed()->get();
		$this->assertEquals(3, count($o2ActiveWithDeleted));
		foreach(['Test6 User6', 'Test7 User7', 'Test9 User9'] as $index => $name) {
			$this->assertEquals($name, $o2ActiveWithDeleted[$index]->fullName());
		}

		$o2MembersWithInactive = $o2->members()->withInactive()->get();
		$this->assertEquals(3, count($o2MembersWithInactive));
		foreach(['Test6 User6', 'Test7 User7', 'Test8 User8'] as $index => $name) {
			$this->assertEquals($name, $o2MembersWithInactive[$index]->fullName());
		}

		$o2AllMembers1 = $o2->members()->withInactive()->withTrashed()->get();
		$o2AllMembers2 = $o2->members()->withTrashed()->withInactive()->get();
		$this->assertEquals(5, count($o2AllMembers1));
		$this->assertEquals(5, count($o2AllMembers2));
		foreach(['Test6 User6', 'Test7 User7', 'Test8 User8', 'Test9 User9', 'Test10 User10'] as $index => $name) {
			$this->assertEquals($name, $o2AllMembers1[$index]->fullName());
			$this->assertEquals($name, $o2AllMembers2[$index]->fullName());
		}
	}

	/**
	 * Ensure that activate and deactivate correctly update database.
	 *
	 * @return void
	 */
	public function testActivateDeactivate() {
		User::find(1)->deactivate(); // t -> f
		User::withInactive()->find(3)->activate(); // f -> t
		User::withTrashed()->find(4)->deactivate(); // t -> f
		User::withInactive()->withTrashed()->find(5)->activate(); // f -> t
		User::find(6)->activate(); // t -> t
		User::withInactive()->find(8)->deactivate(); // f -> f

		$allUsers = User::withInactive()->withTrashed()->orderBy('id')->get();
		foreach([false, true, true, false, true, true, true, false, true, false] as $index => $state) {
			$this->assertEquals($state, $allUsers[$index]->active);
		}
	}
}
