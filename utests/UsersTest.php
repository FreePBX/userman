<?php

class ModulesTest extends PHPUnit_Framework_TestCase {

	protected static $f;
	protected static $u;
	protected static $id;
	protected static $randomUser;

	public static function setUpBeforeClass() {
		include 'setuptests.php';
		self::$f = FreePBX::create();
		self::$u = self::$f->Userman;
	}

	public function testPHPUnit() {
		$this->assertEquals("test", "test", "PHPUnit is broken.");
		$this->assertNotEquals("test", "nottest", "PHPUnit is broken.");
	}

	public function testAddUser() {
		$r = self::$u->addUser("usermanTest", "randomPassword", 'none');
		self::$id = $r['id'];
		$this->assertTrue($r['status'], "Unable to add user for reason: " . $r['message']);
	}

	public function testUpdateUser() {
		$r = self::$u->updateUser("usermanTest", "usermanTest");
		$this->assertTrue($r['status'], "Unable to update user for reason: " . $r['message']);
	}

	public function testGetAllUsers() {
		$r = self::$u->getAllUsers();
		foreach($r as $u) {
			if(!empty($u['default_extension']) && $u['default_extension'] != "none") {
				self::$randomUser = $u;
				break;
			}
		}
		$this->assertTrue(!empty($r), "No users were returned!");
	}

	public function testGetUserByDefaultExtension() {
		$r = self::$u->getUserByDefaultExtension(self::$randomUser['default_extension']);
		$this->assertEquals($r['id'], self::$randomUser['id'], "Lookup Failed");
	}

	public function testGetUserByUsername() {
		$r = self::$u->getUserByUsername(self::$randomUser['username']);
		$this->assertEquals($r['id'], self::$randomUser['id'], "Lookup Failed");
	}

	public function testGetUserByID() {
		$r = self::$u->getUserByID(self::$randomUser['id']);
		$this->assertEquals($r['id'], self::$randomUser['id'], "Lookup Failed");
	}

	public function testDelUser() {
		$r = self::$u->deleteUserByID(self::$id);
		$this->assertTrue($r['status'], "Unable to delete user for reason: " . $r['message']);
	}
}
