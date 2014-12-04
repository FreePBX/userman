<?php
// vim: set ai ts=4 sw=4 ft=php:
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//

namespace FreePBX\modules;
class Userman implements \BMO {
	private $registeredFunctions = array();
	private $message = '';
	private $userTable = 'freepbx_users';
	private $userSettingsTable = 'freepbx_users_settings';
	private $brand = 'FreePBX';
	private $contacts = array();

	public function __construct($freepbx = null) {
		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;

		if (!defined('DASHBOARD_FREEPBX_BRAND')) {
			if (!empty($_SESSION['DASHBOARD_FREEPBX_BRAND'])) {
				define('DASHBOARD_FREEPBX_BRAND', $_SESSION['DASHBOARD_FREEPBX_BRAND']);
			} else {
				define('DASHBOARD_FREEPBX_BRAND', \FreePBX::Config()->get("DASHBOARD_FREEPBX_BRAND"));
			}
		} else {
			$_SESSION['DASHBOARD_FREEPBX_BRAND'] = DASHBOARD_FREEPBX_BRAND;
		}

		$this->brand = DASHBOARD_FREEPBX_BRAND;
	}

	function &create() {
		static $obj;
		if (!isset($obj) || !is_object($obj)) {
			$obj = new \Userman();
		}
		return $obj;
	}

	public function install() {

	}
	public function uninstall() {

	}
	public function backup(){

	}
	public function restore($backup){

	}
	public function genConfig() {

	}

	public function writeConfig($conf){
	}

	public function setMessage($message,$type='info') {
		$this->message = array(
			'message' => $message,
			'type' => $type
		);
		return true;
	}

	public function doConfigPageInit($display) {
		if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'deluser') {
			$ret = $this->deleteUserByID($_REQUEST['user']);
			$this->message = array(
				'message' => $ret['message'],
				'type' => $ret['type']
			);
			return true;
		}
		if(isset($_POST['submit'])) {
			switch($_POST['type']) {
				case 'user':
					$username = !empty($_POST['username']) ? $_POST['username'] : '';
					$password = !empty($_POST['password']) ? $_POST['password'] : '';
					$description = !empty($_POST['description']) ? $_POST['description'] : '';
					$prevUsername = !empty($_POST['prevUsername']) ? $_POST['prevUsername'] : '';
					$assigned = !empty($_POST['assigned']) ? $_POST['assigned'] : array();
					$extraData = array(
						'fname' => isset($_POST['fname']) ? $_POST['fname'] : null,
						'lname' => isset($_POST['lname']) ? $_POST['lname'] : null,
						'title' => isset($_POST['title']) ? $_POST['title'] : null,
						'company' => isset($_POST['company']) ? $_POST['company'] : null,
						'email' => isset($_POST['email']) ? $_POST['email'] : null,
						'cell' => isset($_POST['cell']) ? $_POST['cell'] : null,
						'work' => isset($_POST['work']) ? $_POST['work'] : null,
						'home' => isset($_POST['home']) ? $_POST['home'] : null,
						'fax' => isset($_POST['fax']) ? $_POST['fax'] : null,
						'displayname' => isset($_POST['displayname']) ? $_POST['displayname'] : null
					);
					$default = !empty($_POST['defaultextension']) ? $_POST['defaultextension'] : 'none';
					if(empty($password)) {
						$this->message = array(
							'message' => _('The Password Can Not Be blank!'),
							'type' => 'danger'
						);
						return false;
					}
					if(!empty($username) && empty($prevUsername)) {
						$ret = $this->addUser($username, $password, $default, $description, $extraData);
						if($ret['status']) {
							$this->setGlobalSettingByID($ret['id'],'assigned',$assigned);
							$this->message = array(
								'message' => $ret['message'],
								'type' => $ret['type']
							);
						} else {
							$this->message = array(
								'message' => $ret['message'],
								'type' => $ret['type']
							);
						}
					} elseif(!empty($username) && !empty($prevUsername)) {
						$password = ($password != '******') ? $password : null;
						$ret = $this->updateUser($prevUsername, $username, $default, $description, $extraData, $password);
						if($ret['status']) {
							$this->setGlobalSettingByID($ret['id'],'assigned',$assigned);
							$this->message = array(
								'message' => $ret['message'],
								'type' => $ret['type']
							);
						} else {
							$this->message = array(
								'message' => $ret['message'],
								'type' => $ret['type']
							);
						}
					} else {
						$this->message = array(
							'message' => _('Username Can Not Be Blank'),
							'type' => 'danger'
						);
						return false;
					}
					if($_POST['sendEmail'] == 'yes') {
						$this->sendWelcomeEmail($username, $password);
					}
				break;
				case 'general':
					$this->setGlobalsetting('emailbody',$_POST['emailbody']);
					$this->setGlobalsetting('emailsubject',$_POST['emailsubject']);
					$this->message = array(
						'message' => _('Saved'),
						'type' => 'success'
					);
				break;
			}
		}
	}

	public function myShowPage() {
		if(!function_exists('core_users_list')) {
			return _("Module Core is disabled. Please enable it");
		}
		$module_hook = \moduleHook::create();
		$mods = $this->FreePBX->Hooks->processHooks();
		$moduleHtml = '';
		foreach($mods as $mod) {
			$moduleHtml .= $mod;
		}

		$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
		$html = '';

		$users = $this->getAllUsers();

		$html .= load_view(dirname(__FILE__).'/views/rnav.php',array("users"=>$users));
		switch($action) {
			case 'showuser':
			case 'adduser':
				if($action == 'showuser' && !empty($_REQUEST['user'])) {
					$user = $this->getUserByID($_REQUEST['user']);
					$assigned = $this->getGlobalSettingByID($_REQUEST['user'],'assigned');
					$assigned = !(empty($assigned)) ? $assigned : array();
					$default = $user['default_extension'];
				} else {
					$user = array();
					$assigned = array();
					$default = null;
				}
				$fpbxusers = array();
				$dfpbxusers = array();
				$cul = array();
				foreach(core_users_list() as $list) {
					$cul[$list[0]] = array(
						"name" => $list[1],
						"vmcontext" => $list[2]
					);
				}
				foreach($cul as $e => $u) {
					$fpbxusers[] = array("ext" => $e, "name" => $u['name'], "selected" => in_array($e,$assigned));
				}

				$iuext = $this->getAllInUseExtensions();
				$dfpbxusers[] = array("ext" => 'none', "name" => 'none', "selected" => false);
				foreach($cul as $e => $u) {
					if($e != $default && in_array($e,$iuext)) {
						continue;
					}
					$dfpbxusers[] = array("ext" => $e, "name" => $u['name'], "selected" => ($e == $default));
				}
				$html .= load_view(dirname(__FILE__).'/views/users.php',array("dfpbxusers" => $dfpbxusers, "fpbxusers" => $fpbxusers, "moduleHtml" => $moduleHtml, "hookHtml" => $module_hook->hookHtml, "user" => $user, "message" => $this->message));
			break;
			case 'general':
				$html .= load_view(dirname(__FILE__).'/views/general.php',array("subject" => $this->getGlobalsetting('emailsubject'), "email" => $this->getGlobalsetting('emailbody'), "message" => $this->message, "brand" => $this->brand));
			break;
			default:
				$html .= load_view(dirname(__FILE__).'/views/welcome.php',array());
			break;
		}

		return $html;
	}

	/**
	 * Registers a hookable call
	 *
	 * This registers a global function to a hook action
	 *
	 * @param string $action Hook action of: addUser,updateUser or delUser
	 * @return bool
	 */
	public function registerHook($action,$function) {
		$this->registeredFunctions[$action][] = $function;
		return true;
	}

	/**
	 * Get All Users
	 *
	 * Get a List of all User Manager users and their data
	 *
	 * @return array
	 */
	public function getAllUsers() {
		$sql = "SELECT *, coalesce(displayname, username) as dn FROM ".$this->userTable." ORDER BY username";
		$sth = $this->db->prepare($sql);
		$sth->execute();
		return $sth->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * Get all Users as contacts
	 *
	 * @return array
	 */
	public function getAllContactInfo() {
		if(!empty($this->contacts)) {
			return $this->contacts;
		}
		$sql = "SELECT id, default_extension as internal, username, description, fname, lname, coalesce(displayname, CONCAT_WS(' ', fname, lname)) AS displayname, title, company, department, email, cell, work, home, fax FROM ".$this->userTable;
		$sth = $this->db->prepare($sql);
		$sth->execute();
		$users = $sth->fetchAll(\PDO::FETCH_ASSOC);
		if(empty($users)) {
			return array();
		}
		foreach($users as &$user) {
			//dont let displayname escape without a value
			$user['displayname'] = !empty($user['displayname']) ? $user['displayname'] : $user['username'];
			$user['internal'] = !empty($user['internal']) && $user['internal'] != "none" ? $user['internal'] : "";
			$user = $this->getExtraContactInfo($user);
		}

		$this->contacts = $users;
		return $this->contacts;
	}

	/**
	 * Get additional contact information from other modules that may hook into Userman
	 * @param array $user The User Array
	 */
	public function getExtraContactInfo($user) {
		$mods = $this->FreePBX->Hooks->processHooks($user);
		foreach($mods as $mod) {
			if(!empty($mod) && is_array($mod)) {
				$user = array_merge($user, $mod);
			}
		}
		return $user;
	}

	/**
	 * Get User Information by the Default Extension
	 *
	 * This gets user information from the user which has said extension defined as it's default
	 *
	 * @param string $extension The User (from Device/User Mode) or Extension to which this User is attached
	 * @return bool
	 */
	public function getUserByDefaultExtension($extension) {
		$sql = "SELECT * FROM ".$this->userTable." WHERE default_extension = :extension";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':extension' => $extension));
		$user = $sth->fetch(\PDO::FETCH_ASSOC);
		return $user;
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
		$user = $this->getExtraContactInfo($user);
		return $user;
	}

	/**
	 * Get User Information by Username
	 *
	 * This gets user information by username.
	 * !!This should never be called externally outside of User Manager!!
	 *
	 * @param string $id The ID of the user from User Manager
	 * @return array
	 */
	public function deleteUserByID($id) {
		$user = $this->getUserByID($id);
		if(!$user) {
			return array("status" => false, "type" => "danger", "message" => _("User Does Not Exist"));
		}
		$sql = "DELETE FROM ".$this->userTable." WHERE `id` = :id";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':id' => $id));

		$sql = "DELETE FROM ".$this->userSettingsTable." WHERE `uid` = :uid";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':uid' => $id));
		$this->callHooks('delUser',array("id" => $id));
		$this->delUser($id);
		return array("status" => true, "type" => "success", "message" => _("User Successfully Deleted"));
	}

	/**
	 * This is here so that the processhooks callback has the righ function name to hook into
	 *
	 * Note: Should never be called externally, use the above function!!
	 *
	 * @param {int} $id the user id of the deleted user
	 */
	private function delUser($id) {
		$display = !empty($_REQUEST['display']) ? $_REQUEST['display'] : "";
		$this->FreePBX->Hooks->processHooks($id, $display, array("id" => $id));
	}

	/**
	 * Add a user to User Manager
	 *
	 * This adds a new user to user manager
	 *
	 * @param string $username The username
	 * @param string $password The user Password
	 * @param string $default The default user extension, there is an integrity constraint here so there can't be duplicates
	 * @param string $description a short description of this account
	 * @param array $extraData A hash of extra data to provide about this account (work, email, telephone, etc)
	 * @param bool $encrypt Whether to encrypt the password or not. If this is false the system will still assume its hashed as sha1, so this is only useful if importing accounts with previous sha1 passwords
	 * @return array
	 */
	public function addUser($username, $password, $default='none', $description=null, $extraData=array(), $encrypt = true) {
		$display = !empty($_REQUEST['display']) ? $_REQUEST['display'] : "";
		$description = !empty($description) ? $description : null;
		if(empty($username) || empty($password)) {
			return array("status" => false, "type" => "danger", "message" => _("Username/Password Can Not Be Blank!"));
		}
		if($this->getUserByUsername($username)) {
			return array("status" => false, "type" => "danger", "message" => sprintf(_("User '%s' Already Exists"),$username));
		}
		$sql = "INSERT INTO ".$this->userTable." (`username`,`password`,`description`,`default_extension`) VALUES (:username,:password,:description,:default_extension)";
		$sth = $this->db->prepare($sql);
		$password = ($encrypt) ? sha1($password) : $password;
		$sth->execute(array(':username' => $username, ':password' => $password, ':description' => $description, ':default_extension' => $default));

		$id = $this->db->lastInsertId();
		$this->updateUserExtraData($id,$extraData);
		$this->callHooks('addUser',array("id" => $id, "username" => $username, "description" => $description, "password" => $password, "encrypted" => $encrypt, "extraData" => $extraData));
		$this->FreePBX->Hooks->processHooks($id, $display, array("id" => $id, "username" => $username, "description" => $description, "password" => $password, "encrypted" => $encrypt, "extraData" => $extraData));
		return array("status" => true, "type" => "success", "message" => _("User Successfully Added"), "id" => $id);
	}

	/**
	 * Update a User in User Manager
	 *
	 * This Updates a User in User Manager
	 *
	 * @param string $username The username
	 * @param string $password The user Password
	 * @param string $default The default user extension, there is an integrity constraint here so there can't be duplicates
	 * @param string $description a short description of this account
	 * @param array $extraData A hash of extra data to provide about this account (work, email, telephone, etc)
	 * @param string $password The updated password, if null then password isn't updated
	 * @return array
	 */
	public function updateUser($prevUsername, $username, $default='none', $description=null, $extraData=array(), $password=null) {
		$display = !empty($_REQUEST['display']) ? $_REQUEST['display'] : "";
		$description = !empty($description) ? $description : null;
		$user = $this->getUserByUsername($prevUsername);
		if(!$user || empty($user)) {
			return array("status" => false, "type" => "danger", "message" => sprintf(_("User '%s' Does Not Exist"),$user));
		}
		if(isset($password) && (sha1($password) != $user['password'])) {
			$sql = "UPDATE ".$this->userTable." SET `username` = :username, `password` = :password, `description` = :description, `default_extension` = :default_extension WHERE `username` = :prevusername";
			$sth = $this->db->prepare($sql);
			$sth->execute(array(':username' => $username, ':prevusername' => $prevUsername, ':description' => $description, ':password' => sha1($password), ':default_extension' => $default));
		} elseif(($prevUsername != $username) || ($user['description'] != $description) || $user['default_extension'] != $default) {
			if(($prevUsername != $username) && $this->getUserByUsername($username)) {
				return array("status" => false, "type" => "danger", "message" => sprintf(_("User '%s' Already Exists"),$username));
			}
			$sql = "UPDATE ".$this->userTable." SET `username` = :username, `description` = :description, `default_extension` = :default_extension WHERE `username` = :prevusername";
			$sth = $this->db->prepare($sql);
			$sth->execute(array(':username' => $username, ':prevusername' => $prevUsername, ':description' => $description, ':default_extension' => $default));
		}
		$message = _("Updated User");

		$this->updateUserExtraData($user['id'],$extraData);

		$this->callHooks('updateUser',array("id" => $user['id'], "prevUsername" => $prevUsername, "username" => $username, "description" => $description, "password" => $password, "extraData" => $extraData));
		$this->FreePBX->Hooks->processHooks($user['id'], $display, array("id" => $user['id'], "prevUsername" => $prevUsername, "username" => $username, "description" => $description, "password" => $password, "extraData" => $extraData));
		return array("status" => true, "type" => "success", "message" => $message, "id" => $user['id']);
	}

	/**
	 * Update User Extra Data
	 *
	 * This updates Extra Data about the user
	 * (fname,lname,title,email,cell,work,home,department)
	 *
	 * @param int $id The User Manager User ID
	 * @param array $data A hash of data to update (see above)
	 */
	public function updateUserExtraData($id,$data=array()) {
		if(empty($data)) {
			return true;
		}
		$sql = "UPDATE ".$this->userTable." SET `fname` = :fname, `lname` = :lname, `displayname` = :displayname, `company` = :company, `title` = :title, `email` = :email, `cell` = :cell, `work` = :work, `home` = :home, `fax` = :fax, `department` = :department WHERE `id` = :uid";
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
				':uid' => $id
			)
		);
	}

	/**
	 * Get the assigned devices (Extensions or ﻿(device/user mode) Users) for this User
	 *
	 * Get the assigned devices (Extensions or ﻿(device/user mode) Users) for this User as a Hashed Array
	 *
	 * @param int $id The ID of the user from User Manager
	 * @return array
	 */
	public function getAssignedDevices($id) {
		return $this->getGlobalSettingByID($id,'assigned');
	}

	/**
	 * Set the assigned devices (Extensions or ﻿(device/user mode) Users) for this User
	 *
	 * Set the assigned devices (Extensions or ﻿(device/user mode) Users) for this User as a Hashed Array
	 *
	 * @param int $id The ID of the user from User Manager
	 * @param array $devices The devices to add to this user as an array
	 * @return array
	 */
	public function setAssignedDevices($id,$devices=array()) {
		return $this->setGlobalSettingByID($id,'assigned',$devices);
	}

	/**
	 * Get Globally Defined Sub Settings
	 *
	 * Gets all Globally Defined Sub Settings
	 *
	 * @param int $uid The ID of the user from User Manager
	 * @return mixed false if nothing, else array
	 */
	public function getAllGlobalSettingsByID($uid) {
		$sql = "SELECT a.val, a.type, a.key FROM ".$this->userSettingsTable." a, ".$this->userTable." b WHERE b.id = a.uid AND b.id = :id AND a.module = 'global'";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':id' => $uid));
		$result = $sth->fetch(\PDO::FETCH_ASSOC);
		if($result) {
			$fout = array();
			foreach($result as $res) {
				$fout[$res['key']] = ($result['type'] == 'json-arr' && $this->isJson($result['type'])) ? json_decode($result['type'],true) : $result;
			}
			return $fout;
		}
		return false;
	}

	/**
	 * Get a single setting from a User
	 *
	 * Gets a single Globally Defined Sub Setting
	 *
	 * @param int $uid The ID of the user from User Manager
	 * @param string $setting The keyword that references said setting
	 * @return mixed false if nothing, else array
	 */
	public function getGlobalSettingByID($uid,$setting) {
		$sql = "SELECT a.val, a.type FROM ".$this->userSettingsTable." a, ".$this->userTable." b WHERE b.id = a.uid AND b.id = :id AND a.key = :setting AND a.module = 'global'";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':id' => $uid, ':setting' => $setting));
		$result = $sth->fetch(\PDO::FETCH_ASSOC);
		if($result) {
			return ($result['type'] == 'json-arr' && $this->isJson($result['val'])) ? json_decode($result['val'],true) : $result['val'];
		}
		return false;
	}

	/**
	 * Set Globally Defined Sub Setting
	 *
	 * Sets a Globally Defined Sub Setting
	 *
	 * @param int $uid The ID of the user from User Manager
	 * @param string $setting The keyword that references said setting
	 * @param mixed $value Can be an array, boolean or string or integer
	 * @return mixed false if nothing, else array
	 */
	public function setGlobalSettingByID($uid,$setting,$value) {
		if(is_bool($value)) {
			$value = ($value) ? 1 : 0;
		}
		$type = is_array($value) ? 'json-arr' : null;
		$value = is_array($value) ? json_encode($value) : $value;
		$sql = "REPLACE INTO ".$this->userSettingsTable." (`uid`, `module`, `key`, `val`, `type`) VALUES(:uid, :module, :setting, :value, :type)";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':uid' => $uid, ':module' => 'global', ':setting' => $setting, ':value' => $value, ':type' => $type));
	}

	/**
 	* Get All Defined Sub Settings by Module Name
	 *
	 * Get All Defined Sub Settings by Module Name
	 *
	 * @param int $uid The ID of the user from User Manager
	 * @param string $module The module rawname (this can be anything really, another reference ID)
	 * @return mixed false if nothing, else array
	 */
	public function getAllModuleSettingsByID($uid,$module) {
		$sql = "SELECT a.val, a.type, a.key FROM ".$this->userSettingsTable." a, ".$this->userTable." b WHERE b.id = :id AND b.id = a.uid AND a.module = :module";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':id' => $uid, ':module' => $module));
		$result = $sth->fetch(\PDO::FETCH_ASSOC);
		if($result) {
			$fout = array();
			foreach($result as $res) {
				$fout[$res['key']] = ($result['type'] == 'json-arr' && $this->isJson($result['val'])) ? json_decode($result['val'],true) : $result['val'];
			}
			return $fout;
		}
		return false;
	}

	/**
	 * Get a single setting from a User by Module
	 *
	 * Gets a single Module Defined Sub Setting
	 *
	 * @param int $uid The ID of the user from User Manager
	 * @param string $module The module rawname (this can be anything really, another reference ID)
	 * @param string $setting The keyword that references said setting
	 * @return mixed false if nothing, else array
	 */
	public function getModuleSettingByID($uid,$module,$setting) {
		$sql = "SELECT a.val, a.type FROM ".$this->userSettingsTable." a, ".$this->userTable." b WHERE b.id = :id AND b.id = a.uid AND a.module = :module AND a.key = :setting";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':id' => $uid, ':setting' => $setting, ':module' => $module));
		$result = $sth->fetch(\PDO::FETCH_ASSOC);
		if($result) {
			return ($result['type'] == 'json-arr' && $this->isJson($result['val'])) ? json_decode($result['val'],true) : $result['val'];
		}
		return false;
	}

	/**
	 * Set a Module Sub Setting
	 *
	 * Sets a Module Defined Sub Setting
	 *
	 * @param int $uid The ID of the user from User Manager
	 * @param string $module The module rawname (this can be anything really, another reference ID)
	 * @param string $setting The keyword that references said setting
	 * @param mixed $value Can be an array, boolean or string or integer
	 * @return mixed false if nothing, else array
	 */
	public function setModuleSettingByID($uid,$module,$setting,$value) {
		if(is_bool($value)) {
			$value = ($value) ? 1 : 0;
		}
		$type = is_array($value) ? 'json-arr' : null;
		$value = is_array($value) ? json_encode($value) : $value;
		$sql = "REPLACE INTO ".$this->userSettingsTable." (`uid`, `module`, `key`, `val`, `type`) VALUES(:id, :module, :setting, :value, :type)";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':id' => $uid, ':module' => $module, ':setting' => $setting, ':value' => $value, ':type' => $type));
	}

	/**
	 * Check Credentials against username with a passworded sha
	 * @param {string} $username      The username
	 * @param {string} $password_sha1 The sha
	 */
	public function checkCredentials($username, $password_sha1) {
		$sql = "SELECT id, password FROM ".$this->userTable." WHERE username = :username";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':username' => $username));
		$result = $sth->fetch(\PDO::FETCH_ASSOC);
		if(!empty($result) && ($password_sha1 == $result['password'])) {
			return $result['id'];
		}
		return false;
	}

	/**
	 * Set a global User Manager Setting
	 * @param {[type]} $key   [description]
	 * @param {[type]} $value [description]
	 */
	public function setGlobalsetting($key, $value) {
		$settings = $this->getGlobalsettings();
		$settings[$key] = $value;
		$sql = "REPLACE INTO module_xml (`id`, `data`) VALUES('userman_data', ?)";
		$sth = $this->db->prepare($sql);
		return $sth->execute(array(json_encode($settings)));
	}

	/**
	 * Get a global User Manager Setting
	 * @param {[type]} $key [description]
	 */
	public function getGlobalsetting($key) {
		$sql = "SELECT data FROM module_xml WHERE id = 'userman_data'";
		$sth = $this->db->prepare($sql);
		$sth->execute();
		$result = $sth->fetch(\PDO::FETCH_ASSOC);
		$results = !empty($result['data']) ? json_decode($result['data'], true) : array();
		return !empty($results[$key]) ? $results[$key] : null;
	}

	/**
	 * Get all global user manager settings
	 */
	public function getGlobalsettings() {
		$sql = "SELECT data FROM module_xml WHERE id = 'userman_data'";
		$sth = $this->db->prepare($sql);
		$sth->execute();
		$result = $sth->fetch(\PDO::FETCH_ASSOC);
		return !empty($result['data']) ? json_decode($result['data'], true) : array();
	}

	private function callHooks($action,$data=null) {
		$display = !empty($_REQUEST['display']) ? $_REQUEST['display'] : "";
		$ret = array();
		if(isset($this->registeredFunctions[$action])) {
			foreach($this->registeredFunctions[$action] as $function) {
				if(function_exists($function) && !empty($data['id'])) {
					$ret[$function] = $function($data['id'], $display, $data);
				}
			}
		}
		return $ret;
	}

	public function migrateVoicemailUsers($context = "default") {
		echo "Starting to migrate Voicemail users\\n";
		$config = $this->FreePBX->LoadConfig();
		$config->loadConfig("voicemail.conf");
		$context = empty($context) ? "default" : $context;
		if($context == "general" || empty($config->ProcessedConfig[$context])) {
			echo "Invalid Context: '".$context."'";
			return false;
		}

		foreach($config->ProcessedConfig[$context] as $exten => $vu) {
			$vars = explode(",",$vu);
			$password = $vars[0];
			$displayname = $vars[1];
			$z = $this->getUserByDefaultExtension($exten);
			if(!empty($z)) {
				echo "Voicemail User '".$z['username']."' already has '".$exten."' as it's default extension. Skipping\\n";
				continue;
			}
			$z = $this->getUserByUsername($exten);
			if(!empty($z)) {
				echo "Voicemail User '".$z['username']."' already exists. Skipping\\n";
				continue;
			}
			$user = $this->addUser($exten, $password, $exten, $displayname);
			if(!empty($user['id'])) {
				echo "Added ".$exten." with password of ".$password."\\n";
				$this->setAssignedDevices($user['id'], array($exten));
			} else {
				echo "Could not add ".$exten." because: ".$user['message']."\\n";
			}
		}
		echo "\\nNow run: amportal a ucp enableall\\nTo give all users access to UCP";
	}

	/**
	 * Sends a welcome email
	 * @param {string} $username The username to send to
	 * @param {string} $password =              null If you want to send the password set it here
	 */
	public function sendWelcomeEmail($username, $password =  null) {
		global $amp_conf;
		$user = $this->getUserByUsername($username);
		if(empty($user) || empty($user['email'])) {
			return false;
		}

		$user['host'] = 'http://'.$_SERVER["SERVER_NAME"];
		$user['brand'] = $this->brand;

		$user['password'] = !empty($password) ? $password : "<" . _('hidden') . ">";

		$mods = $this->callHooks('welcome',array('id' => $user['id'], 'brand' => $user['brand'], 'host' => $user['host']));
		$user['services'] = '';
		foreach($mods as $mod) {
			$user['services'] .= $mod . "\n";
		}

		$mods = $this->FreePBX->Hooks->processHooks($user['id'], $_REQUEST['display'], array('id' => $user['id'], 'brand' => $user['brand'], 'host' => $user['host']));
		foreach($mods as $mod) {
			$user['services'] .= $mod . "\n";
		}

		$dbemail = $this->getGlobalsetting('emailbody');
		$template = !empty($dbemail) ? $dbemail : file_get_contents(__DIR__.'/views/emails/welcome_text.tpl');
		preg_match_all('/%([\w|\d]*)%/',$template,$matches);

		foreach($matches[1] as $match) {
			$replacement = !empty($user[$match]) ? $user[$match] : '';
			$template = str_replace('%'.$match.'%',$replacement,$template);
		}
		$email_options = array('useragent' => $this->brand, 'protocol' => 'mail');
		$email = new \CI_Email();
		$from = !empty($amp_conf['AMPUSERMANEMAILFROM']) ? $amp_conf['AMPUSERMANEMAILFROM'] : 'freepbx@freepbx.org';

		$email->from($from);
		$email->to($user['email']);
		$dbsubject = $this->getGlobalsetting('emailsubject');
		$subject = !empty($dbsubject) ? $dbsubject : _('Your %brand% Account');
		preg_match_all('/%([\w|\d]*)%/',$subject,$matches);
		foreach($matches[1] as $match) {
			$replacement = !empty($user[$match]) ? $user[$match] : '';
			$subject = str_replace('%'.$match.'%',$replacement,$template);
		}
		$email->subject($subject);
		$email->message($template);
		$email->send();
	}

	private function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	private function getAllInUseExtensions() {
		$sql = 'SELECT default_extension FROM '.$this->userTable;
		$sth = $this->db->prepare($sql);
		$sth->execute();
		$devices = $sth->fetchAll(\PDO::FETCH_ASSOC);
		$used = array();
		foreach($devices as $device) {
			if($device['default_extension'] == 'none') {
				continue;
			}
			$used[] = $device['default_extension'];
		}
		return $used;
	}
}
