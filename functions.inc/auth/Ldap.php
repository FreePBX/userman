<?php
// vim: set ai ts=4 sw=4 ft=php:
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
namespace FreePBX\modules\Userman\Auth;

class Ldap extends Auth {
	private $ldap = null;
	private $host = '';
	private $port = '';
	private $dn = "";
	private $rdn = "";
	private $domain = "";
	private $user = "";
	private $password = "";
	private $ucache = array(); //cache requests throughout this class
	private $gcache = array(); //cache requests throughout this class

	public function __construct($userman, $freepbx) {
		parent::__construct($userman, $freepbx);
		if(!function_exists('ldap_connect')) {
			throw new \Exception('Unable to use LDAP Connector. It doesnt exist');
		}
		$this->ldap = ldap_connect($this->host,$this->port);
		if($this->ldap === false) {
			throw new \Exception("Unable to Connect");
		}
		ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

		if(!ldap_bind($this->ldap, $this->user."@".$this->domain, $this->password)) {
			throw new \Exception("Unable to Auth");
		}

		if(isset($_REQUEST['refresh'])) {
			$this->updateAllUsers();
			$this->updateAllGroups();
		}
	}

	public function getPermissions() {
		return array(
			"addGroup" => false,
			"addUser" => false,
			"modifyGroup" => false,
			"modifyUser" => false,
			"modifyGroupAttrs" => false,
			"modifyUserAttrs" => false,
			"removeGroup" => false,
			"removeUser" => false,
			"changePassword" => false
		);
	}

	/**
	 * Get All Users
	 *
	 * Get a List of all User Manager users and their data
	 *
	 * @return array
	 */
	public function getAllUsers() {
		return parent::getAllUsers('ldap');
	}

	/**
	* Get All Users
	*
	* Get a List of all User Manager users and their data
	*
	* @return array
	*/
	public function getAllGroups() {
		return parent::getAllGroups('ldap');
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
		return array("status" => false, "type" => "danger", "message" => _("LDAP is in Read Only Mode. Deletion denied"));
	}

	/**
	* Delete a Group by it's ID
	* @param int $gid The group ID
	*/
	public function deleteGroupByGID($gid) {
		return array("status" => false, "type" => "danger", "message" => _("LDAP is in Read Only Mode. Deletion denied"));
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
		return array("status" => false, "type" => "danger", "message" => _("LDAP is in Read Only Mode. Addition denied"));
	}

	public function addGroup($groupname, $description=null, $users=array()) {
		return array("status" => false, "type" => "danger", "message" => _("LDAP is in Read Only Mode. Addition denied"));
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
	public function updateUser($uid, $prevUsername, $username, $default='none', $description=null, $extraData=array(), $password=null) {
		$sql = "UPDATE ".$this->userTable." SET `default_extension` = :default_extension WHERE `id` = :uid";
		$sth = $this->db->prepare($sql);
		try {
			$sth->execute(array(':default_extension' => $default, ':uid' => $uid));
		} catch (\Exception $e) {
			return array("status" => false, "type" => "danger", "message" => $e->getMessage());
		}
		return array("status" => true, "type" => "success", "message" => _("User updated"), "id" => $uid);
	}

	/**
	* Update Group
	* @param string $prevGroupname The group's previous name
	* @param string $groupname     The Groupname
	* @param string $description   The group description
	* @param array  $users         Array of users in this Group
	*/
	public function updateGroup($gid, $prevGroupname, $groupname, $description=null, $users=array()) {
		return array("status" => true, "type" => "success", "message" => _("Group updated"), "id" => $gid);
	}

	/**
	* Check Credentials against username with a passworded sha
	* @param {string} $username      The username
	* @param {string} $password_sha1 The sha
	*/
	public function checkCredentials($username, $password) {
		$ldap = ldap_connect($this->host,$this->port);
		if($ldap === false) {
			return false;
		}
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

		return ldap_bind($ldap, $username, $password);
	}

	/**
	* Update All Groups
	*/
	private function updateAllGroups() {
		if(!empty($this->gcache)) {
			return true;
		}
		$sr = ldap_search($this->ldap, $this->dn, "(objectCategory=Group)");
		$groups = ldap_get_entries($this->ldap, $sr);
		unset($groups['count']);
		foreach($groups as $group) {
			//Now get the users for this group
			$members = array();
			$gs = ldap_search($this->ldap, $this->dn, "(&(objectCategory=Person)(sAMAccountName=*)(memberof=".$group['distinguishedname'][0]."))");
			$users = ldap_get_entries($this->ldap, $gs);
			unset($users['count']);
			foreach($users as $user) {
				$usid = $this->binToStrSid($user['objectsid'][0]);
				$u = $this->getUserByAuthID($usid);
				$members[] = $u['id'];
			}
			$sid = $this->binToStrSid($group['objectsid'][0]);
			$this->gcache[$sid] = $group;
			$um = $this->linkGroup($group['cn'][0], 'ldap', $sid);
			if($um['status']) {
				$this->updateGroupData($um['id'], array(
					"description" => !empty($group['description'][0]) ? $group['description'][0] : '',
					"users" => $members
				));
			}
		}
	}

	/**
	* Update Single User
	* @param array $user The user data from usermanager
	*/
	private function updateSingleGroup($user) {
		if(!empty($this->ucache[$user['authid']])) {
			return true;
		}
		$sr = ldap_search($this->ldap, $this->dn, "(&(objectCategory=Group)(objectSID=".$user['authid']."))");
		$group = ldap_get_entries($this->ldap, $sr);
		if(empty($group[0])) {
			return false;
		}
		$sid = $this->binToStrSid($group[0]['objectsid'][0]);
		$this->gcache[$sid] = $group[0];
		//Now get the users for this group
		$members = array();
		$gs = ldap_search($this->ldap, $this->dn, "(&(objectCategory=Person)(sAMAccountName=*)(memberof=".$group[0]['distinguishedname'][0]."))");
		$users = ldap_get_entries($this->ldap, $gs);
		unset($users['count']);
		foreach($users as $user) {
			$usid = $this->binToStrSid($user['objectsid'][0]);
			$u = $this->getUserByAuthID($usid);
			$members[] = $u['id'];
		}
		$sid = $this->binToStrSid($group[0]['objectsid'][0]);
		$this->gcache[$sid] = $group[0];
		$um = $this->linkGroup($group[0]['cn'][0], 'ldap', $sid);
		if($um['status']) {
			$this->updateGroupData($um['id'], array(
				"description" => !empty($group['description'][0]) ? $group['description'][0] : '',
				"users" => $members
			));
		}
		return true;
	}

	/**
	 * Update All Users
	 */
	private function updateAllUsers() {
		if(!empty($this->ucache)) {
			return true;
		}
		$sr = ldap_search($this->ldap, $this->dn, "(&(objectCategory=Person)(sAMAccountName=*))");
		$users = ldap_get_entries($this->ldap, $sr);
		unset($users['count']);
		foreach($users as $user) {
			$sid = $this->binToStrSid($user['objectsid'][0]);
			$this->ucache[$sid] = $user;
			$um = $this->linkUser($user['cn'][0], 'ldap', $sid);
			if($um['status']) {
				$this->updateUserData($um['id'], array(
					"description" => !empty($user['description'][0]) ? $user['description'][0] : '',
					"primary_group" => !empty($user['primarygroupid'][0]) ? $user['primarygroupid'][0] : '',
					"fname" => !empty($user['givenname'][0]) ? $user['givenname'][0] : '',
					"lname" => !empty($user['sn'][0]) ? $user['sn'][0] : '',
					"displayname" => !empty($user['displayname'][0]) ?$user['displayname'][0] : '',
					"department" => !empty($user['department'][0]) ? $user['department'][0] : '',
					"email" => !empty($user['mail'][0]) ? $user['mail'][0] : '',
					"cell" => !empty($user['mobile'][0]) ? $user['mobile'][0] : '',
					"work" => !empty($user['telephonenumber'][0]) ? $user['telephonenumber'][0] : '',
				));
			}
		}
	}

	/**
	 * Update Single User
	 * @param array $user The user data from usermanager
	 */
	private function updateSingleUser($user) {
		if(!empty($this->ucache[$user['authid']])) {
			return true;
		}
		$sr = ldap_search($this->ldap, $this->dn, "(&(objectCategory=Person)(sAMAccountName=*)(objectSID=".$user['authid']."))");
		$user = ldap_get_entries($this->ldap, $sr);
		if(empty($user[0])) {
			return false;
		}
		$sid = $this->binToStrSid($user[0]['objectsid'][0]);
		$this->ucache[$sid] = $user[0];
		$um = $this->linkUser($user[0]['cn'][0], 'ldap', $this->binToStrSid($user[0]['objectsid'][0]));
		if($um['status']) {
			$this->updateUserData($um['id'], array(
				"description" => !empty($user[0]['description'][0]) ? $user[0]['description'][0] : '',
				"primary_group" => $user[0]['primarygroupid'][0],
				"fname" => $user[0]['givenname'][0],
				"lname" => $user[0]['sn'][0],
				"displayname" => $user[0]['displayname'][0],
				"department" => !empty($user[0]['department'][0]) ? $user[0]['department'][0] : '',
				"email" => !empty($user[0]['mail'][0]) ? $user[0]['mail'][0] : '',
				"cell" => !empty($user[0]['mobile'][0]) ? $user[0]['mobile'][0] : '',
				"work" => !empty($user[0]['telephonenumber'][0]) ? $user[0]['telephonenumber'][0] : '',
			));
		}
		return true;
	}

	/**
	 * Turn LDAP binary strings into HEX
	 * @param string $binary_guid The binary data
	 */
	private function GUIDtoStr($binary_guid) {
		$hex_guid = unpack("H*hex", $binary_guid);
		$hex = $hex_guid["hex"];

		$hex1 = substr($hex, -26, 2) . substr($hex, -28, 2) . substr($hex, -30, 2) . substr($hex, -32, 2);
		$hex2 = substr($hex, -22, 2) . substr($hex, -24, 2);
		$hex3 = substr($hex, -18, 2) . substr($hex, -20, 2);
		$hex4 = substr($hex, -16, 4);
		$hex5 = substr($hex, -12, 12);

		$guid_str = $hex1 . "-" . $hex2 . "-" . $hex3 . "-" . $hex4 . "-" . $hex5;

		return $guid_str;
	}


	/**
	 * Turns a binary SID into a String
	 * @param  string $binsid The binary string
	 */
	public function binToStrSid($binsid) {
		$hex_sid = bin2hex($binsid);
		$rev = hexdec(substr($hex_sid, 0, 2));
		$subcount = hexdec(substr($hex_sid, 2, 2));
		$auth = hexdec(substr($hex_sid, 4, 12));
		$result    = "$rev-$auth";

		for ($x=0;$x < $subcount; $x++) {
			$subauth[$x] =
			hexdec($this->littleEndian(substr($hex_sid, 16 + ($x * 8), 8)));
			$result .= "-" . $subauth[$x];
		}

		// Cheat by tacking on the S-
		return 'S-' . $result;
	}

	/**
	 * Converts a little-endian hex-number to one, that 'hexdec' can convert
	 * @param  string $hex hex string
	 */
	public function littleEndian($hex) {
		$result = "";

		for ($x = strlen($hex) - 2; $x >= 0; $x = $x - 2) {
			$result .= substr($hex, $x, 2);
		}
		return $result;
	}


	/**
	 * This function will convert a binary value guid into a valid string
	 * @param  string $object_guid guid binary string
	 */
	public function binToStrGuid($object_guid) {
		$hex_guid = bin2hex($object_guid);
		$hex_guid_to_guid_str = '';
		for($k = 1; $k <= 4; ++$k) {
			$hex_guid_to_guid_str .= substr($hex_guid, 8 - 2 * $k, 2);
		}
		$hex_guid_to_guid_str .= '-';
		for($k = 1; $k <= 2; ++$k) {
			$hex_guid_to_guid_str .= substr($hex_guid, 12 - 2 * $k, 2);
		}
		$hex_guid_to_guid_str .= '-';
		for($k = 1; $k <= 2; ++$k) {
			$hex_guid_to_guid_str .= substr($hex_guid, 16 - 2 * $k, 2);
		}
		$hex_guid_to_guid_str .= '-' . substr($hex_guid, 16, 4);
		$hex_guid_to_guid_str .= '-' . substr($hex_guid, 20);

		return strtoupper($hex_guid_to_guid_str);
	}
}
