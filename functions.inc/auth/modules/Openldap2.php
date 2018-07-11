<?php
// vim: set ai ts=4 sw=4 ft=php:
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
//
//  MSActiveDirectory auth module copied and modified for use with OpenLDAP-Directory
//  Modified by Matthias Frei - www.frei.media
//  2016/01/10
//
//
//  Groups are identified by attribute 'objectClass'='posixGroup'
//  User are identified by attribute 'objectClass'='person'
//
//  Group-Membership:  via attributes 'memberUid' of a posixGroup object
//  Primary-Group of User:	via attribute 'gidNumber' of a person object
//
//  FreePBX User Manager Settings examples:
//  Authentication Engine: 				Microsoft Active Directory
//  Username for LDAP-Auth example:		uid=USERA
//  Password for LDAP-Auth:				[LDAP password of USERA]
//  User-DN for LDAP-Auth example:		ou=people,dc=example,dc=com
//  ((=>Generated LDAP-Auth string:		uid=USERA,ou=people,dc=example,dc=com))
//  Base-DN example:  					dc=example,dc=com
//  Extension Link Attribute example:	telephonenumber 	([needs to be lowercase] => LDAP attribute 'telephoneNumber')


namespace FreePBX\modules\Userman\Auth;

class Openldap2 extends Auth {
	/**
	 * LDAP Object
	 * @var object
	 */
	private $ldap = null;
	/**
	 * Socket Timeout
	 * @var integer
	 */
	private $timeout = 3;
	/**
	 * User cache
	 * cache requests throughout this class
	 * @var array
	 */
	private $ucache = array();
	/**
	 * Group Cache
	 * cache requests throughout this class
	 * @var array
	 */
	private $gcache = array();
	/**
	 * Server Time
	 * @var string
	 */
	private $time = null;

	private static $serverDefaults = array(
		'host' => '',
		'port' => '389',
		'basedn' => '',
		'username' => '',
		'password' => '',
		'connection' => '',
		'localgroups' => 0,
		'createextensions' => '',
		'externalidattr' => 'entryUUID',
		'descriptionattr' => 'description',
		'commonnameattr' => 'cn'
	);

	private static $userDefaults = array(
		'userdn' => '',
		'userobjectclass' => 'posixAccount',
		'userobjectfilter' => '(objectclass=posixAccount)',
		'usernameattr' => 'uid',
		'userfirstnameattr' => 'givenName',
		'userlastnameattr' => 'sn',
		'userdisplaynameattr' => 'displayName',
		'usertitleattr' => '',
		'usercompanyattr' => '',
		'usercellphoneattr' => '',
		'userworkphoneattr' => 'telephoneNumber',
		'userhomephoneattr' => '',
		'userfaxphoneattr' => '',
		'usermailattr' => 'mail',
		'usergroupmemberattr' => 'memberOf',
		'userpasswordattr' => 'userPassword',
		'userprimarygroupattr' => 'gidNumber',
		'la' => ''
	);

	private static $groupDefaults = array(
		'groupdnaddition' => '',
		'groupobjectclass' => 'groupOfUniqueNames',
		'groupobjectfilter' => '(objectclass=posixGroup)',
		'groupmemberattr' => 'memberUid',
		'groupgidnumberattr' => 'gidNumber',
	);

	private $userHooks = array(
		'add' => array(),
		'update' => array(),
		'remove' => array()
	);

	private $groupHooks = array(
		'add' => array(),
		'update' => array(),
		'remove' => array()
	);

	public function __construct($userman, $freepbx, $config) {
		parent::__construct($userman, $freepbx, $config);
		$this->FreePBX = $freepbx;
		$c = $config;
		$validKeys = array_merge(self::$serverDefaults,self::$userDefaults,self::$groupDefaults);
		$this->config = array();
		$this->config['id'] = !empty($config['id']) ? $config['id'] : '';
		foreach($validKeys as $key => $value) {
			if($key != "password") {
				$this->config[$key] = (isset($c[$key])) ? strtolower($c[$key]) : strtolower($value);
			} else {
				$this->config[$key] = (isset($c[$key])) ? $c[$key] : '';
			}
		}
		if(isset($config['userexternalidattr'])) {
			$this->config['externalidattr'] = strtolower($config['userexternalidattr']);
		}
		if(isset($config['userdescriptionattr'])) {
			$this->config['descriptionattr'] = strtolower($config['userdescriptionattr']);
		}
		if(isset($config['groupnameattr'])) {
			$this->config['commonnameattr'] = strtolower($config['groupnameattr']);
		}
		$date = new \DateTime("now",new \DateTimeZone("UTC"));
		$this->time = $date->format('YmdHis\Z');
	}

	/**
	* Get information about this authentication driver
	* @param  object $userman The userman object
	* @param  object $freepbx The FreePBX BMO object
	* @return array          Array of information about this driver
	*/
	public static function getInfo($userman, $freepbx) {
		if(!function_exists('ldap_connect')) {
			return array();
		}
		return array(
			"name" => _("OpenLDAP Directory")
		);
	}

	/**
	 * Get the configuration display of the authentication driver
	 * @param  object $userman The userman object
	 * @param  object $freepbx The FreePBX BMO object
	 * @return string          html display data
	 */
	public static function getConfig($userman, $freepbx, $config) {
		$status = array(
			"connected" => false,
			"type" => "info",
			"message" => _("Not Connected")
		);
		if(!empty($config['host']) && !empty($config['username']) && !empty($config['password']) && !empty($config['basedn'])) {
			$openldap2 = new static($userman, $freepbx, $config);
			try {
				$openldap2->connect();
				$status = array(
					"connected" => true,
					"type" => "success",
					"message" => _("Connected")
				);
			} catch(\Exception $e) {
				$status = array(
					"connected" => false,
					"type" => "danger",
					"message" => $e->getMessage()
				);
			}
		} elseif(!empty($config['host']) || !empty($config['username']) || !empty($config['password']) || !empty($config['basedn'])) {
			$status = array(
				"connected" => false,
				"type" => "warning",
				"message" => _("Not all of the connection parameters have been filled out")
			);
		}
		$defaults = array_merge(self::$serverDefaults,self::$userDefaults,self::$groupDefaults);
		$techs = $freepbx->Core->getAllDriversInfo();
		return load_view(dirname(dirname(dirname(__DIR__)))."/views/openldap2.php", array("techs" => $techs, "config" => $config, "status" => $status, "defaults" => $defaults));
	}

	/**
	 * Save the configuration about the authentication driver
	 * @param  object $userman The userman object
	 * @param  object $freepbx The FreePBX BMO object
	 * @return mixed          Return true if valid. Otherwise return error string
	 */
	public static function saveConfig($userman, $freepbx) {
		$validKeys = array();
		$validKeys = array_merge($validKeys,array_keys(self::$serverDefaults),array_keys(self::$userDefaults),array_keys(self::$groupDefaults));
		$config = array();
		foreach($validKeys as $key) {
			if(isset($_POST['openldap2-'.$key])) {
				$config[$key] = $_POST['openldap2-'.$key];
			}
		}
		return $config;
	}

	/**
	 * Return the LDAP object after connect
	 * @return object The LDAP object
	 */
	public function getLDAPObject() {
		$openldap2->connect();
		return $this->ldap;
	}

	/**
	 * Connect to the LDAP server
	 */
	public function connect($reconnect = false) {
		if($reconnect || !$this->ldap) {
			if(!$this->serviceping($this->config['host'], $this->config['port'], $this->timeout)) {
				throw new \Exception("Unable to Connect to ".$this->config['host']."!");
			}
			$protocol = ($this->config['connection'] == 'ssl') ? 'ldaps' : 'ldap';
			$this->ldap = ldap_connect(buildldapuri($this->config['connection'],
 $this->config['host'], $this->config['port']));
			if($this->ldap === false) {
				$this->ldap = null;
				throw new \Exception("Unable to Connect");
			}
			if($this->config['connection'] == 'tls') {
				ldap_start_tls($this->ldap);
			}

			ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);

			if(!@ldap_bind($this->ldap, $this->config['username'], $this->config['password'])) {
				$this->ldap = null;
				throw new \Exception("Unable to Auth");
			}
		}
	}

	/**
	 * Sync users and groups to the local database
	 */
	public function sync($output=null) {
		if(php_sapi_name() !== 'cli') {
			$path = $this->FreePBX->Config->get("AMPSBIN");
			exec($path."/fwconsole userman --sync ".escapeshellarg($this->config['id'])." --force");
			return;
		}

		$this->connect();
		$this->output = $output;
		$this->out("");
		$this->out("Updating All Users");
		$this->updateAllUsers();
		$this->out("Updating All Groups");
		$this->updateAllGroups();
		$this->out("Updating Primary Groups");
		$this->updatePrimaryGroups();
		$this->out("Executing User Manager Hooks");
		$this->executeHooks();
	}

	/**
	 * Execute all User Manager hooks. After processing
	 */
	public function executeHooks() {
		foreach($this->userHooks['add'] as $user) {
			$this->out("\tAdding User ".$user[1]."...",false);
			call_user_func_array(array($this,"addUserHook"),$user);
			$this->out("done");
		}
		foreach($this->userHooks['update'] as $user) {
			$this->out("\tUpdating User ".$user[2]."...",false);
			call_user_func_array(array($this,"updateUserHook"),$user);
			$this->out("done");
		}
		foreach($this->userHooks['remove'] as $user) {
			$this->out("\tRemoving User ".$user[1]['username']."...",false);
			call_user_func_array(array($this,"delUserHook"),$user);
			$this->out("done");
		}
		foreach($this->groupHooks['add'] as $group) {
			$this->out("\tAdding Group ".$group[1]."...",false);
			call_user_func_array(array($this,"addGroupHook"),$group);
			$this->out("done");
		}
		foreach($this->groupHooks['update'] as $group) {
			$this->out("\tUpdating Group ".$group[2]."...",false);
			call_user_func_array(array($this,"updateGroupHook"),$group);
			$this->out("done");
		}
		foreach($this->groupHooks['remove'] as $group) {
			$this->out("\tRemoving Group ".$group[1]['groupname']."...",false);
			call_user_func_array(array($this,"delGroupHook"),$group);
			$this->out("done");
		}
	}

	/**
	 * Return an array of permissions for this adaptor
	 */
	public function getPermissions() {
		return array(
			"addGroup" => ($this->config['localgroups'] ? true : false),
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
		return parent::getAllUsers();
	}

	/**
	* Get All Users
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
	* @param bool $encrypt Whether to encrypt the password or not. If this is false the system will still assume its hashed as sha1, so this is only useful if importing accounts with previous sha1 passwords
	* @return array
	*/
	public function addUser($username, $password, $default='none', $description=null, $extraData=array(), $encrypt = true) {
		return array("status" => false, "type" => "danger", "message" => _("LDAP is in Read Only Mode. Addition denied"));
	}

	/**
	 * Add a group to User Manager
	 *
	 * This adds a new group to User Manager
	 *
	 * @param string $groupname   The group Name
	 * @param string $description The group description
	 * @param array  $users       users to add to said group (by ID)
	 */
	public function addGroup($groupname, $description=null, $users=array()) {
		if($this->config['localgroups']) {
			$sql = "INSERT INTO ".$this->groupTable." (`groupname`,`description`,`users`, `auth`, `local`) VALUES (:groupname,:description,:users,:directory,1)";
			$sth = $this->db->prepare($sql);
			try {
				$sth->execute(array(':directory' => $this->config['id'],':groupname' => $groupname, ':description' => $description, ':users' => json_encode($users)));
			} catch (\Exception $e) {
				return array("status" => false, "type" => "danger", "message" => $e->getMessage());
			}

			$id = $this->db->lastInsertId();
			$this->addGroupHook($id, $groupname, $description, $users);
			return array("status" => true, "type" => "success", "message" => _("Group Successfully Added"), "id" => $id);
		} else {
			return array("status" => false, "type" => "danger", "message" => _("LDAP is in Read Only Mode. Addition denied"));
		}
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
	public function updateUser($uid, $prevUsername, $username, $default='none', $description=null, $extraData=array(), $password=null, $nodisplay=false) {
		$sql = "UPDATE ".$this->userTable." SET `default_extension` = :default_extension WHERE `id` = :uid";
		$sth = $this->db->prepare($sql);
		try {
			$sth->execute(array(':default_extension' => $default, ':uid' => $uid));
		} catch (\Exception $e) {
			return array("status" => false, "type" => "danger", "message" => $e->getMessage());
		}
		$this->updateUserHook($uid, $prevUsername, $username, $description, $password, $extraData, $nodisplay);
		return array("status" => true, "type" => "success", "message" => _("User updated"), "id" => $uid);
	}

	/**
	 * Update Group
	 * @param string $prevGroupname The group's previous name
	 * @param string $groupname     The Groupname
	 * @param string $description   The group description
	 * @param array  $users         Array of users in this Group
	 */
	public function updateGroup($gid, $prevGroupname, $groupname, $description=null, $users=array(), $nodisplay=false) {
		$group = $this->getGroupByUsername($prevGroupname);
		if($this->config['localgroups'] && $group['local']) {
			$sql = "UPDATE ".$this->groupTable." SET `groupname` = :groupname, `description` = :description, `users` = :users WHERE `id` = :gid";
			$sth = $this->db->prepare($sql);
			try {
				$sth->execute(array(':groupname' => $groupname, ':gid' => $gid, ':description' => $description, ':users' => json_encode($users)));
			} catch (\Exception $e) {
				return array("status" => false, "type" => "danger", "message" => $e->getMessage());
			}
		}
		$this->updateGroupHook($gid, $prevGroupname, $groupname, $description, $group['users'],$nodisplay);
		return array("status" => true, "type" => "success", "message" => _("Group updated"), "id" => $gid);
	}

	/**
	 * Check Credentials against username with a passworded sha
	 * @param {string} $username      The username
	 * @param {string} $password_sha1 The sha
	 */
	public function checkCredentials($username, $password) {
		$this->connect();

		$userdn = !empty($this->config['userdn']) ? $this->config['userdn'].",".$this->config['basedn'] : $this->config['basedn'];
		$res = @ldap_bind($this->ldap, $this->config['usernameattr']."=".$username.",".$userdn, $password);

		if($res) {
			$user = $this->getUserByUsername($username);
		}
		return !empty($user['id']) ? $user['id'] : false;
	}

	/**
	 * Lookup and find all primary group memberships
	 * This should be run after updating groups and users
	 */
	private function updatePrimaryGroups() {
		if(empty($this->ucache) || empty($this->gcache)) {
			$this->updateAllUsers();
			$this->updateAllGroups();
		}

		$groups = array();
		foreach($this->gcache as $gsid => $group) {
			$groups[$gsid] = $this->getGroupByAuthID($gsid);
			$groups[$gsid]['cache'] = $group;
		}
		$process = array();
		foreach($this->ucache as $usid => $user) {
			$u = $this->getUserByAuthID($usid);
			if(empty($user[$this->config['userprimarygroupattr']][0])) {
				$this->out("\tUser ".$u['username']." missing ".$this->config['userprimarygroupattr']." attribute. Cant determine primary group");
				continue;
			}
			$primarygroup = $user[$this->config['userprimarygroupattr']][0];
			foreach($groups as $gsid => $group) {
				if(empty($group['cache'][$this->config['groupgidnumberattr']][0])) {
					$this->out("\tGroup ".$group['groupname']." missing ".$this->config['groupgidnumberattr']." attribute. Cant determine primary group");
					continue;
				} elseif($primarygroup == $group['cache'][$this->config['groupgidnumberattr']][0]) {
					$this->out("\tUser ".$u['username']." primary group is ".$group['groupname']);
					if(empty($process[$group['id']])) {
						$process[$group['id']] = array(
							"id" => $group['id'],
							"description" => $group['description'],
							"users" => $group['users'],
							"name" => $group['groupname']
						);
					}
					if(!in_array($u['id'],$process[$group['id']]['users'])) {
						$process[$group['id']]['users'][] = $u['id'];
					}
				}
			}
		}
		foreach($process as $id => $g) {
			$this->updateGroupData($g['id'], array(
				"description" => $g['description'],
				"users" => $g['users']
			));
			if(isset($this->groupHooks['update'][$g['id']])) {
				$this->groupHooks['update'][$g['id']] = array($g['id'], $this->groupHooks['update'][$g['id']][2], $g['name'], $g['description'], $g['users']);
			} else {
				$this->groupHooks['update'][$g['id']] = array($g['id'], $g['name'], $g['name'], $g['description'], $g['users']);
			}
		}
	}

	/**
	 * Update All Groups
	 * Runs through the directory to update all settings (users and naming)
	 */
	private function updateAllGroups() {
		if(!empty($this->gcache)) {
			return true;
		}
		if(php_sapi_name() !== 'cli') {
			throw new \Exception("Can only update groups over CLI");
		}
		$this->connect();
		$userdn = !empty($this->config['userdn']) ? $this->config['userdn'].",".$this->config['basedn'] : $this->config['basedn'];
		$groupdn = !empty($this->config['groupdnaddition']) ? $this->config['groupdnaddition'].",".$this->config['basedn'] : $this->config['basedn'];
        $ldapuri = buildldapuri($this->config['connection'], $this->config['host'], $this->config['port']));
		$this->out("\t".'ldapsearch -w '.$this->config['password'].' -H "'.$ldapuri.'" -D "'.$this->config['username'].'" -b "'.$groupdn.'" -s sub "'.$this->config['groupobjectfilter'].'"');
		$this->out("\tRetrieving all groups...");
		//(".$this->config['usermodifytimestampattr'].">=20010301000000Z)
		$sr = ldap_search($this->ldap, $groupdn, "(&".$this->config['groupobjectfilter']."(objectclass=".$this->config['groupobjectclass']."))", array("*",$this->config['groupgidnumberattr'],$this->config['descriptionattr'],$this->config['commonnameattr'], $this->config['externalidattr'], $this->config['groupmemberattr']));
		if($sr === false) {
			return false;
		}
		$groups = ldap_get_entries($this->ldap, $sr);
		if($groups['count'] == 0) {
			$this->out("\tNo groups found! Perhaps your query is wrong?");
			return;
		}
		$this->out("\tGot ".$groups['count']. " groups");
		unset($groups['count']);

		foreach($groups as $group) {
			if(!isset($group[$this->config['externalidattr']])) {
				$this->out("\t\tERROR group is missing ".$this->config['externalidattr']." attribute! Cant continue!!");
				continue;
			}
			$sid = $group[$this->config['externalidattr']][0];
			$this->gcache[$sid] = $group;
			$groupname = $group[$this->config['commonnameattr']][0];
			$um = $this->linkGroup($groupname, $sid);
			$description = (!empty($this->config['descriptionattr']) && !empty($group[$this->config['descriptionattr']][0])) ? $group[$this->config['descriptionattr']][0] : '';
			$members = array();
			$this->out("\tWorking on ".$groupname);
			if(empty($group[$this->config['groupmemberattr']])) {
				$this->out("\t\tGroup Attribute ".$this->config['groupmemberattr']." is missing! Cant find members!");
			} else {
				$this->out("\t\tFound ".$group[$this->config['groupmemberattr']]['count']." members");
				unset($group[$this->config['groupmemberattr']]['count']);
				foreach ($group[$this->config['groupmemberattr']] as $member) {
					if(preg_match("/uid=([^,]+),/",$member,$matches)) {
						$member = $matches[1];
					}
					$m = $this->getUserByUsername($member);
					if(!empty($m)) {
						$this->out("\t\t\tAdding ".$m['username']." to group");
						$members[] = $m['id'];
					} else {
						$this->out("\t\t\tUnknown ".$member.". Cant add to group");
					}
				}
			}
			if($um['status']) {
				$this->updateGroupData($um['id'], array(
					"description" => $description,
					"users" => $members
				));
				if($um['new']) {
					$this->out("\t\tAdding ".$groupname);
					$this->groupHooks['add'][$um['id']] = array($um['id'], $groupname, $description, $members);
				} else {
					$this->out("\t\tUpdating ".$groupname);
					$this->groupHooks['update'][$um['id']] = array($um['id'], $um['prevGroupname'], $groupname, $description, $members);
				}
			}
		}
		//remove users
		$fgroups = $this->getAllGroups();
		foreach($fgroups as $group) {
			if(!isset($this->gcache[$group['authid']])) {
				$this->out("\t\tDeleting ".$group['groupname']);
				$this->deleteGroupByGID($group['id'], false);
				$this->groupHooks['remove'][$group['id']] = array($group['id'], $group);
			}
		}
		$this->out("Finished adding users from non-primary groups");
	}

	/**
	 * Update All Users
	 */
	private function updateAllUsers() {
		if(!empty($this->ucache)) {
			return true;
		}
		$this->connect();

		$userdn = !empty($this->config['userdn']) ? $this->config['userdn'].",".$this->config['basedn'] : $this->config['basedn'];
		$ldapuri = buildldapuri($this->config['connection'], $this->config['host'], $this->config['port']));
		$this->out("\t".'ldapsearch -w '.$this->config['password'].' -H "'.$ldapuri.'" -D "'.$this->config['username'].'" -b "'.$userdn.'" -s sub "'.$this->config['userobjectfilter'].'" "'.$this->config['externalidattr'].'=*" '.$this->config['externalidattr']);
		$this->out("\tRetrieving all users...");
		//(".$this->config['groupmodifytimestampattr'].">=20010301000000Z)
		$sr = ldap_search($this->ldap, $userdn, "(&".$this->config['userobjectfilter']."(objectclass=".$this->config['userobjectclass']."))", array('*',$this->config['externalidattr']));
		$users = ldap_get_entries($this->ldap, $sr);

		if($users['count'] == 0) {
			$this->out("\tNo users found! Perhaps your query is wrong?");
			return;
		}

		$this->out("\tGot ".$users['count']. " users");

		unset($users['count']);
		//add and update users
		foreach($users as $user) {
			if(!isset($user[$this->config['externalidattr']])) {
				$this->out("\t\tERROR user is missing ".$this->config['externalidattr']." attribute! Cant continue!!");
				continue;
			}
			$sid = $user[$this->config['externalidattr']][0];
			$username = $user[$this->config['usernameattr']][0];
			$this->ucache[$sid] = $user;
			$um = $this->linkUser($username, $sid);

			if($um['status']) {
				if($um['new']) {
					$this->out("\t\tAdding ".$username);
				} else {
					$this->out("\t\tUpdating ".$username);
				}
				$data = array(
					"description" => (!empty($this->config['descriptionattr']) && !empty($user[$this->config['descriptionattr']][0])) ? $user[$this->config['descriptionattr']][0] : '',
					"primary_group" => (!empty($this->config['userprimarygroupattr']) && !empty($user[$this->config['userprimarygroupattr']][0])) ? $user[$this->config['userprimarygroupattr']][0] : '',
					"fname" => (!empty($this->config['userfirstnameattr']) && !empty($user[$this->config['userfirstnameattr']][0])) ? $user[$this->config['userfirstnameattr']][0] : '',
					"lname" => (!empty($this->config['userlastnameattr']) && !empty($user[$this->config['userlastnameattr']][0])) ? $user[$this->config['userlastnameattr']][0] : '',
					"displayname" => (!empty($this->config['userdisplaynameattr']) && !empty($user[$this->config['userdisplaynameattr']][0])) ? $user[$this->config['userdisplaynameattr']][0] : '',
					"department" => (!empty($this->config['userdepartmentattr']) && !empty($user[$this->config['userdepartmentattr']][0])) ? $user[$this->config['userdepartmentattr']][0] : '',
					"title" => (!empty($this->config['usertitleattr']) && !empty($user[$this->config['usertitleattr']][0])) ? $user[$this->config['usertitleattr']][0] : '',
					"email" => (!empty($this->config['usermailattr']) && !empty($user[$this->config['usermailattr']][0])) ? $user[$this->config['usermailattr']][0] : '',
					"cell" => (!empty($this->config['usercellphoneattr']) && !empty($user[$this->config['usercellphoneattr']][0])) ? $user[$this->config['usercellphoneattr']][0] : '',
					"work" => (!empty($this->config['userworkphoneattr']) && !empty($user[$this->config['userworkphoneattr']][0])) ? $user[$this->config['userworkphoneattr']][0] : '',
					"fax" => (!empty($this->config['userfaxphoneattr']) && !empty($user[$this->config['userfaxphoneattr']][0])) ? $user[$this->config['userfaxphoneattr']][0] : '',
					"home" => (!empty($this->config['userhomephoneattr']) && !empty($user[$this->config['userhomephoneattr']][0])) ? $user[$this->config['userhomephoneattr']][0] : '',
				);
				if(!empty($this->config['la']) && !empty($user[$this->config['la']][0])) {
					$extension = $user[$this->config['la']][0];
					$d = $this->FreePBX->Core->getUser($extension);
					if(!empty($d)) {
						$this->out("\t\t\tLinking Extension ".$extension." to ".$username);
						$data["default_extension"] = $extension;
					} else {
						$dn = !empty($data['displayname']) ? $data['displayname'] : $data['fname'] ." ".$data['lname'];
						if(!empty($this->config['createextensions'])) {
							$tech = $this->config['createextensions'];
							$this->out("\t\t\tCreating ".$tech." Extension ".$extension);
							$settings = $this->FreePBX->Core->generateDefaultDeviceSettings($tech,$extension,$dn);
							if($this->FreePBX->Core->addDevice($extension,$tech,$settings)) {
								$settings = $this->FreePBX->Core->generateDefaultUserSettings($tech,$dn);
								$settings['outboundcid'] = $data['outboundcid'];
								try {
									if(!$this->FreePBX->Core->addUser($extension, $settings)) {
										//cleanup
										$this->FreePBX->Core->delDevice($extension);
										$this->out("\t\t\tThere was an unknown error creating this extension");
									}
									$this->out("\t\t\tLinking Extension ".$extension." to ".$username);
									$data["default_extension"] = $extension;
								} catch(\Exception $e) {
									//cleanup
									$this->delDevice($extension);
								}
							} else {
								$this->out("\t\t\tDevice ".$extension." was not added!");
							}
						} else {
							$this->out("\t\t\tExtension ". $extension . " does not exist, skipping link");
						}
					}
				} elseif(!empty($this->config['la']) && empty($user[$this->config['la']][0])) {
					$data["default_extension"] = 'none';
				}
				$this->updateUserData($um['id'], $data);
				if($um['new']) {
					$this->userHooks['add'][$um['id']] = array($um['id'], $username, $data['description'], null, false, $data);
				} else {
					$this->userHooks['update'][$um['id']] = array($um['id'], $um['prevUsername'], $username, $data['description'], null, $data);
				}
				$this->ucache[$sid]['userman'][0] = $um['id'];
			}
		}
		//remove users
		$fusers = $this->getAllUsers();
		foreach($fusers as $user) {
			if(!isset($this->ucache[$user['authid']])) {
				$this->out("\t\tDeleting ".$user['username']);
				$this->deleteUserByID($user['id'], false);
				$this->userHooks['remove'][$user['id']] = array($user['id'],$user);
			}
		}
	}

	/**
	 * Debug messages
	 * @param  string $message The message
	 * @param  boolean $nl      New line or not
	 */
	private function out($message,$nl=true) {
		if(is_object($this->output) && $this->output->isVerbose()) {
			if($nl) {
				$this->output->writeln($message);
			} else {
				$this->output->write($message);
			}
		} elseif(!is_object($this->output)) {
			dbug($message);
		}
	}

	private function serviceping($hosts, $port=389, $timeout=1) {
		foreach (preg_split("/[ ,]/", $hosts) as $host) {
			$op = @fsockopen($host, $port, $errno, $errstr, $timeout);
			if ($op) {
				fclose($op); //explicitly close open socket connection
				return 1; //DC is up & running, 
						  //we can safely connect with ldap_connect
			}
		}
		return 0; //DC is N/A 
	}

	private function buildldapuri($connection, $hosts, $port) {
	    $securearray = array("ldaps", "ssl");
		$uriarray = array();
		if (in_array($connection, $securearray)) {
			$proto = 'ldaps';
		} else {
			$proto = 'ldap';
		}
		foreach (preg_split("/[ ,]/", $hosts) as $host) {
			array_push($uriarray, $proto . "://" . $host . ":" . $port);
		}
		return implode(" ", $uriarray);
	}
}

