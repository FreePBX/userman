<?php
// vim: set ai ts=4 sw=4 ft=php:
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
namespace FreePBX\modules\Userman\Auth;
use PDO;
use Exception;
use Hautelook\Phpass\PasswordHash;

class Freepbx extends Auth {

	public function __construct($userman, $freepbx, $config) {
		parent::__construct($userman, $freepbx, $config);
	}

	/**
	 * Get information about this driver
	 * @param  Object $userman The userman Object
	 * @param  Object $freepbx The freepbx Object
	 * @return array          array of information
	 */
	public static function getInfo($userman, $freepbx) {
		$brand = \FreePBX::Config()->get("DASHBOARD_FREEPBX_BRAND");
		return ["name" => sprintf(_("%s Internal Directory"),$brand)];
	}

	public function getDefaultGroups() {
		return $this->config['default-groups']??'';
	}

	/**
	 * Get configuration for this driver
	 * @param  Object $userman The userman Object
	 * @param  Object $freepbx The freepbx Object
	 * @return string          array with the name of the authentication device, and an array
	 * 						   with all the configurations of this authentication device 
	 */
	public static function getConfig($userman, $freepbx, $config) {
		$sql = "SELECT * FROM userman_groups WHERE auth = ? ORDER BY priority";
		$sth = $freepbx->Database->prepare($sql);
		$sth->execute([$config['id'] ?? '']);
		$groups = $sth->fetchAll(PDO::FETCH_ASSOC);

		$typeauth = self::getShortName();
		$form_data = [['name'		=> $typeauth.'-default-groups', 'title'		=> _('Default Groups'), 'type' 		=> 'list_multiple', 'index'		=> true, 'list'		=> $groups, 'value'		=> (! empty($config['default-groups']) ? $config['default-groups'] : []), 'keys'		=> ['value' => 'id', 'text' 	=> 'groupname'], 'help'		=> _("Select which groups new users are added to when they are created")]];
		return ['auth' => $typeauth, 'data' => $form_data];
	}

	/**
	 * Save Configuration from auth config page
	 * @param  Object $userman The userman Object
	 * @param  Object $freepbx The freepbx Object
	 */
	public static function saveConfig($userman, $freepbx) {
		$typeauth = self::getShortName();
		$config = ['authtype' => $typeauth, "default-groups" => $_REQUEST[$typeauth.'-default-groups'] ?? ''];
		return $config;
	}

	/**
	* Get All Users
	*
	* Get a List of all User Manager users and their data
	*
	* @return array
	*/
	public function getAllUsers() {
		return parent::getAllUsers();
	}

	/**
	* Get All Groups
	*
	* Get a List of all User Manager users and their data
	*
	* @return array
	*/
	public function getAllGroups() {
		return parent::getAllGroups();
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
	* @param bool $encrypt Whether to encrypt the password or not. If this is false the system will still assume its hashed, so this is only useful if importing accounts with previous hashed passwords
	* @return array
	*/
	public function addUser($username, $password, $default='none', $description=null, $extraData=[], $encrypt = true) {
		$request = $_REQUEST;
		$display = !empty($request['display']) ? $request['display'] : "";
		$description = !empty($description) ? $description : null;
		if(empty($username) || empty($password)) {
			return ["status" => false, "type" => "danger", "message" => _("Username/Password Can Not Be Blank!")];
		}
		if($this->getUserByUsername($username)) {
			return ["status" => false, "type" => "danger", "message" => sprintf(_("User '%s' Already Exists"),$username)];
		}
		$sql = "INSERT INTO ".$this->userTable." (`username`,`auth`,`password`,`description`,`default_extension`) VALUES (:username,:directory,:password,:description,:default_extension)";
		$sth = $this->db->prepare($sql);
		if($encrypt) {
			$passwordHasher = new PasswordHash(8,false);
			$pw = $passwordHasher->HashPassword($password);
		} else {
			$pw = $password;
		}
		try {
			$sth->execute([':directory' => $this->config['id'], ':username' => $username, ':password' => $pw, ':description' => $description, ':default_extension' => $default]);
		} catch (Exception $e) {
			return ["status" => false, "type" => "danger", "message" => $e->getMessage()];
		}

		$id = $this->db->lastInsertId();
		$this->updateUserData($id,$extraData);
		$this->addUserHook($id, $username, $description, $password, $encrypt, $extraData);
		return ["status" => true, "type" => "success", "message" => _("User Successfully Added"), "id" => $id];
	}

	public function addGroup($groupname, $description=null, $users=[], $extraData=[]) {
		$sql = "INSERT INTO ".$this->groupTable." (`groupname`,`description`,`users`, `auth`) VALUES (:groupname,:description,:users,:directory)";
		$sth = $this->db->prepare($sql);
		try {
			$sth->execute([':directory' => $this->config['id'], ':groupname' => $groupname, ':description' => $description, ':users' => json_encode($users, JSON_THROW_ON_ERROR)]);
		} catch (Exception $e) {
			return ["status" => false, "type" => "danger", "message" => $e->getMessage()];
		}

		$id = $this->db->lastInsertId();
		$this->updateGroupData($id,$extraData);
		$this->addGroupHook($id, $groupname, $description, $users);
		return ["status" => true, "type" => "success", "message" => _("Group Successfully Added"), "id" => $id];
	}

	/**
	* Update a User in User Manager
	*
	* This Updates a User in User Manager
	*
	* @param int $uid The User ID
	* @param string $username The username
	* @param string $password The user Password
	* @param string $default The default user extension, there is an integrity constraint here so there can't be duplicates
	* @param string $description a short description of this account
	* @param array $extraData A hash of extra data to provide about this account (work, email, telephone, etc)
	* @param string $password The updated password, if null then password isn't updated
	* @return array
	*/
	public function updateUser($uid, $prevUsername, $username, $default='none', $description=null, $extraData=[], $password=null, $nodisplay = false) {
		$request = $_REQUEST;
		$display = !empty($request['display']) ? $request['display'] : "";
		$description = !empty($description) ? $description : null;
		$user = $this->getUserByUsername($prevUsername);
		if(!$user || empty($user)) {
			return ["status" => false, "type" => "danger", "message" => sprintf(_("User '%s' Does Not Exist"),$prevUsername)];
		}

		if(isset($password)) {
			$sql = "UPDATE ".$this->userTable." SET `username` = :username, `password` = :password, `description` = :description, `default_extension` = :default_extension WHERE `id` = :uid";
			$sth = $this->db->prepare($sql);
			try {
				$passwordHasher = new PasswordHash(8,false);
				$sth->execute([':username' => $username, ':uid' => $uid, ':description' => $description, ':password' => $passwordHasher->HashPassword($password), ':default_extension' => $default]);
			} catch (Exception $e) {
				return ["status" => false, "type" => "danger", "message" => $e->getMessage()];
			}
		} elseif(($prevUsername != $username) || ($user['description'] != $description) || $user['default_extension'] != $default) {
			if(($prevUsername != $username) && $this->getUserByUsername($username)) {
				return ["status" => false, "type" => "danger", "message" => sprintf(_("User '%s' Already Exists"),$username)];
			}
			$sql = "UPDATE ".$this->userTable." SET `username` = :username, `description` = :description, `default_extension` = :default_extension WHERE `id` = :uid";
			$sth = $this->db->prepare($sql);
			try {
				$sth->execute([':username' => $username, ':uid' => $uid, ':description' => $description, ':default_extension' => $default]);
			} catch (Exception $e) {
				return ["status" => false, "type" => "danger", "message" => $e->getMessage()];
			}
		}
		$message = _("Updated User");

		if(!$this->updateUserData($user['id'],$extraData)) {
			return ["status" => false, "type" => "danger", "message" => _("An Unknown error occured while trying to update user data")];
		}
		$this->updateUserHook($uid, $prevUsername, $username, $description, $password, $extraData, $nodisplay);
		return ["status" => true, "type" => "success", "message" => $message, "id" => $user['id']];
	}

	/**
	* Update Group
	* @param string $prevGroupname The group's previous name
	* @param string $groupname     The Groupname
	* @param string $description   The group description
	* @param array  $users         Array of users in this Group
	*/
	public function updateGroup($gid, $prevGroupname, $groupname, $description=null, $users=[], $nodisplay = false, $extraData=[]) {
		$group = $this->getGroupByUsername($prevGroupname);
		if(!$group || empty($group)) {
			return ["status" => false, "type" => "danger", "message" => sprintf(_("Group '%s' Does Not Exist"),$group)];
		}
		$sql = "UPDATE ".$this->groupTable." SET `groupname` = :groupname, `description` = :description, `users` = :users WHERE `id` = :gid";
		$sth = $this->db->prepare($sql);
		try {
			$sth->execute([':groupname' => $groupname, ':gid' => $gid, ':description' => $description, ':users' => json_encode($users, JSON_THROW_ON_ERROR)]);
		} catch (Exception $e) {
			return ["status" => false, "type" => "danger", "message" => $e->getMessage()];
		}
		if(!$this->updateGroupData($gid,$extraData)) {
			return ["status" => false, "type" => "danger", "message" => _("An Unknown error occured while trying to update user data")];
		}
		$message = _("Updated Group");
		$this->updateGroupHook($gid, $prevGroupname, $groupname, $description, $users, $nodisplay);
		return ["status" => true, "type" => "success", "message" => $message, "id" => $gid];
	}

	/**
	* Check Credentials against username with a passworded sha
	* @param {string} $username      The username
	* @param {string} $password_sha1 The sha
	*/
	public function checkCredentials($username, $password) {
		$sql = "SELECT id, password FROM ".$this->userTable." WHERE username = :username AND auth = :directory";
		$sth = $this->db->prepare($sql);
		$sth->execute([':username' => $username, ':directory' => $this->config['id']]);
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		$passwordHasher = new PasswordHash(8,false);

		if(!empty($result) && (strlen((string) $result['password']) === 40) && (sha1((string) $password) === $result['password'])) {
			$hash = $passwordHasher->HashPassword($password);
			$sql = "UPDATE ".$this->userTable." SET password = :password WHERE username = :username";
			$sth = $this->db->prepare($sql);
			$sth->execute([':password' => $hash, ':username' => $username]);
			return $result['id'];
		} elseif(!empty($result)) {
			$passwordMatch = $passwordHasher->CheckPassword($password, $result['password']);
			if($passwordMatch) {
				return $result['id'];
			}
		}

		return false;
	}
}
