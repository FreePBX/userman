<?php
// vim: set ai ts=4 sw=4 ft=php:
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
namespace FreePBX\modules\Userman\Auth;

abstract class Auth implements Base {
	protected $userTable = 'userman_users';
	protected $userSettingsTable = 'userman_users_settings';
	protected $groupTable = 'userman_groups';
	protected $groupSettingsTable = 'userman_groups_settings';

	public function __construct($userman, $freepbx) {
		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
		$this->userman = $userman;
	}

	public function getPermissions() {
		return array(
			"addGroup" => true,
			"addUser" => true,
			"modifyGroup" => true,
			"modifyUser" => true,
			"modifyGroupAttrs" => true,
			"modifyUserAttrs" => true,
			"removeGroup" => true,
			"deleteGroup" => true
		);
	}

	/**
	 * Get User Information by Username
	 *
	 * This gets user information by username
	 *
	 * @param string $username The User Manager Username
	 * @return bool
	 */
	public function getUserByUsername($username) {
		$sql = "SELECT * FROM ".$this->userTable." WHERE username = :username";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':username' => $username));
		$user = $sth->fetch(\PDO::FETCH_ASSOC);
		return $user;
	}

	/**
	 * Get User Information by Email
	 *
	 * This gets user information by Email
	 *
	 * @param string $username The User Manager Email Address
	 * @return bool
	 */
	public function getUserByEmail($username) {
		$sql = "SELECT * FROM ".$this->userTable." WHERE email = :email";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':email' => $username));
		$user = $sth->fetch(\PDO::FETCH_ASSOC);
		$user = $this->userman->getExtraContactInfo($user);
		return $user;
	}

	/**
	 * Get User Information by User ID
	 *
	 * This gets user information by User Manager User ID
	 *
	 * @param string $id The ID of the user from User Manager
	 * @return bool
	 */
	public function getUserByID($id) {
		$sql = "SELECT * FROM ".$this->userTable." WHERE id = :id";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':id' => $id));
		$user = $sth->fetch(\PDO::FETCH_ASSOC);
		$user = $this->userman->getExtraContactInfo($user);
		return $user;
	}

	/**
	 * Get All Users
	 *
	 * Get a List of all User Manager users and their data
	 *
	 * @return array
	 */
	public function getAllUsers($auth='freepbx') {
		$sql = "SELECT *, coalesce(displayname, username) as dn FROM ".$this->userTable." WHERE auth = :auth ORDER BY username";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(":auth" => $auth));
		return $sth->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * Link user from external auth system into Usermanager
	 * @param string $username    The username of the user
	 * @param string $default     the default extension to assign
	 * @param string $description The description
	 * @param string $auth        The auth type (class)
	 * @param string $authid      The authID
	 */
	public function linkUser($username, $auth = 'freepbx', $authid = null) {
		$request = $_REQUEST;
		$display = !empty($request['display']) ? $request['display'] : "";
		$description = !empty($description) ? $description : null;
		if(empty($username)) {
			return array("status" => false, "type" => "danger", "message" => _("Username Can Not Be Blank!"));
		}
		$sql = "SELECT * FROM ".$this->userTable." WHERE auth = :auth AND authid = :authid";
		$sth = $this->db->prepare($sql);
		try {
			$sth->execute(array(':auth' => $auth, ":authid" => $authid));
			$previous = $sth->fetch(\PDO::FETCH_ASSOC);
		} catch (\Exception $e) {
			return array("status" => false, "type" => "danger", "message" => $e->getMessage());
		}
		if(!$previous) {
			$sql = "INSERT INTO ".$this->userTable." (`username`,`auth`,`authid`) VALUES (:username,:auth,:authid)";
			$sth = $this->db->prepare($sql);
			try {
				$sth->execute(array(':username' => $username, ':auth' => $auth, ":authid" => $authid));
			} catch (\Exception $e) {
				return array("status" => false, "type" => "danger", "message" => $e->getMessage());
			}

			$id = $this->db->lastInsertId();
			return array("status" => true, "type" => "success", "message" => _("User Successfully Added"), "id" => $id);
		} else {
			$sql = "UPDATE ".$this->userTable." SET username = :username WHERE auth = :auth AND authid = :authid AND id = :id";
			$sth = $this->db->prepare($sql);
			try {
				$sth->execute(array(':username' => $username, ':auth' => $auth, ":authid" => $authid, ":id" => $previous['id']));
			} catch (\Exception $e) {
				return array("status" => false, "type" => "danger", "message" => $e->getMessage());
			}
			return array("status" => true, "type" => "success", "message" => _("User Successfully Updated"), "id" => $previous['id']);
		}
	}

	public function updateUserData($uid, $extraData = array()) {
		if(empty($data)) {
			return true;
		}
		$sql = "UPDATE ".$this->userTable." SET `fname` = :fname, `lname` = :lname, `displayname` = :displayname, `company` = :company, `title` = :title, `email` = :email, `cell` = :cell, `work` = :work, `home` = :home, `fax` = :fax, `department` = :department, `description` = :description, `primary_group` = :primary_group WHERE `id` = :uid";
		$defaults = $this->getUserByID($id);
		$sth = $this->db->prepare($sql);
		$fname = !empty($data['fname']) ? $data['fname'] : (!isset($data['fname']) && !empty($defaults['fname']) ? $defaults['fname'] : null);
		$lname = !empty($data['lname']) ? $data['lname'] : (!isset($data['lname']) && !empty($defaults['lname']) ? $defaults['lname'] : null);
		$title = !empty($data['title']) ? $data['title'] : (!isset($data['title']) && !empty($defaults['title']) ? $defaults['title'] : null);
		$company = !empty($data['company']) ? $data['company'] : (!isset($data['company']) && !empty($defaults['company']) ? $defaults['company'] : null);
		$email = !empty($data['email']) ? $data['email'] : (!isset($data['email']) && !empty($defaults['email']) ? $defaults['email'] : null);
		$cell = !empty($data['cell']) ? $data['cell'] : (!isset($data['cell']) && !empty($defaults['cell']) ? $defaults['cell'] : null);
		$home = !empty($data['home']) ? $data['home'] : (!isset($data['home']) && !empty($defaults['home']) ? $defaults['home'] : null);
		$work = !empty($data['work']) ? $data['work'] : (!isset($data['work']) && !empty($defaults['work']) ? $defaults['work'] : null);
		$fax = !empty($data['fax']) ? $data['fax'] : (!isset($data['fax']) && !empty($defaults['fax']) ? $defaults['fax'] : null);
		$displayname = !empty($data['displayname']) ? $data['displayname'] : (!isset($data['displayname']) && !empty($defaults['displayname']) ? $defaults['displayname'] : null);
		$department = !empty($data['department']) ? $data['department'] : (!isset($data['department']) && !empty($defaults['department']) ? $defaults['department'] : null);
		$description = !empty($data['description']) ? $data['description'] : (!isset($data['description']) && !empty($defaults['description']) ? $defaults['description'] : null);
		$primary_group = !empty($data['primary_group']) ? $data['primary_group'] : (!isset($data['primary_group']) && !empty($defaults['primary_group']) ? $defaults['primary_group'] : null);

		try {
			$sth->execute(
				array(
					':fname' => $fname,
					':lname' => $lname,
					':displayname' => $displayname,
					':title' => $title,
					':company' => $company,
					':email' => $email,
					':cell' => $cell,
					':work' => $work,
					':home' => $home,
					':fax' => $fax,
					':department' => $department,
					':description' => $description,
					':primary_group' => $primary_group,
					':uid' => $uid,
				)
			);
		} catch (\Exception $e) {
			return false;
		}
		return true;
	}
}
