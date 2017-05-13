<?php
// vim: set ai ts=4 sw=4 ft=php:
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
//	https://msdn.microsoft.com/en-us/library/windows/desktop/ms677605(v=vs.85).aspx
//
namespace FreePBX\modules\Userman\Auth;

class Msad extends Auth {
	/**
	 * LDAP Object
	 * @var object
	 */
	private $ldap = null;
	/**
	 * LDAP Host
	 * @var string
	 */
	private $host = '';
	/**
	 * LDAP Port
	 * @var integer
	 */
	private $port = 389;
	/**
	 * LDAP Base DN
	 * @var string
	 */
	private $dn = "";
	/**
	 * LDAP Domain
	 * @var string
	 */
	private $domain = "";
	/**
	 * LDAP User
	 * @var string
	 */
	private $user = "";
	/**
	 * LDAP Password
	 * @var string
	 */
	private $password = "";
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

	private $active = 0;

	private static $serverDefaults = array(
		'host' => '',
		'port' => '389',
		'dn' => '',
		'username' => '',
		'domain' => '',
		'password' => ''
	);

	private static $userDefaults = array(
		'userdn' => '',
		'userobjectclass' => 'user',
		'userobjectfilter' => '(&(objectCategory=Person)(sAMAccountName=*))',
		'usernameattr' => 'sAMAccountName',
		'usernamerdnattr' => 'cn',
		'userfirstnameattr' => 'givenName',
		'userlastnameattr' => 'sn',
		'userdisplaynameattr' => 'displayName',
		'userdescriptionattr' => 'description',
		'usertitleattr' => '',
		'usercompanyattr' => '',
		'usercellphoneattr' => 'mobile',
		'userworkphoneattr' => 'telephoneNumber',
		'userhomephoneattr' => 'homephone',
		'userfaxphoneattr' => 'facsimileTelephoneNumber',
		'usermailattr' => 'mail',
		'usergroupmemberattr' => 'memberOf',
		'userpasswordattr' => 'unicodePwd',
		'userexternalidattr' => 'objectGUID',
		'userprimarygroupattr' => 'primarygroupid',
		'usermodifytimestampattr' => 'modifyTimestamp',
		'la' => 'ipphone'
	);

	private static $groupDefaults = array(
		'groupdnaddition' => '',
		'groupobjectclass' => 'group',
		'groupobjectfilter' => '(objectCategory=Group)',
		'groupnameattr' => 'cn',
		'groupdescriptionattr' => 'description',
		'groupmemberattr' => 'member',
		'groupgidnumberattr' => 'gidNumber',
		'groupexternalidattr' => 'objectGUID',
		'groupmodifytimestampattr' => 'modifyTimestamp',
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

	public function __construct($userman, $freepbx) {
		parent::__construct($userman, $freepbx);
		$this->FreePBX = $freepbx;
		$config = $userman->getConfig("authMSADSettings");
		$this->output = null;

		$validKeys = array_merge(self::$serverDefaults,self::$userDefaults,self::$groupDefaults);
		$this->config = array();
		foreach($validKeys as $key => $value) {
			if($key != "password") {
				$this->config[$key] = (isset($config[$key])) ? strtolower($config[$key]) : strtolower($value);
			} else {
				$this->config[$key] = (isset($config[$key])) ? $config[$key] : '';
			}
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
			"name" => _("Microsoft Active Directory")
		);
	}

	/**
	 * Get the configuration display of the authentication driver
	 * @param  object $userman The userman object
	 * @param  object $freepbx The FreePBX BMO object
	 * @return string          html display data
	 */
	public static function getConfig($userman, $freepbx) {
		$config = $userman->getConfig("authMSADSettings");
		$status = array(
			"connected" => false,
			"type" => "info",
			"message" => _("Not Connected")
		);
		if(!empty($config['host']) && !empty($config['username']) && !empty($config['password']) && !empty($config['domain'])) {
			$msad = new static($userman, $freepbx);
			try {
				$msad->connect();
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
		} elseif(!empty($config['host']) || !empty($config['username']) || !empty($config['password']) || !empty($config['domain'])) {
			$status = array(
				"connected" => false,
				"type" => "warning",
				"message" => _("Not all of the connection parameters have been filled out")
			);
		}
		$defaults = array_merge(self::$serverDefaults,self::$userDefaults,self::$groupDefaults);
		return load_view(dirname(dirname(dirname(__DIR__)))."/views/msad.php", array("config" => $config, "status" => $status, "defaults" => $defaults));
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
			if(isset($_POST['msad-'.$key])) {
				$config[$key] = $_POST['msad-'.$key];
			}
		}
		$userman->setConfig("authMSADSettings", $config);
		return true;
	}

	/**
	 * Return the LDAP object after connect
	 * @return object The LDAP object
	 */
	public function getLDAPObject() {
		$msad->connect();
		return $this->ldap;
	}

	/**
	 * Connect to the LDAP server
	 */
	public function connect($reconnect = false) {
		if($reconnect || !$this->ldap) {
			$this->ldap = ldap_connect('ldap://'.$this->config['host'].":".$this->config['port']);
			if($this->ldap === false) {
				$this->ldap = null;
				throw new \Exception("Unable to Connect");
			}
			ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

			if(!@ldap_bind($this->ldap, $this->config['username']."@".$this->config['domain'], $this->config['password'])) {
				$this->ldap = null;
				throw new \Exception("Unable to Auth");
			}
			//$resp = ldap_read($this->ldap, '', 'objectclass=*');
			//print_r(ldap_get_entries($this->ldap, $resp));
			//die();
		}
	}

	/**
	 * Sync users and groups to the local database
	 */
	public function sync($output=null) {
		if(php_sapi_name() !== 'cli') {
			$path = $this->FreePBX->Config->get("AMPSBIN");
			exec($path."/fwconsole userman sync");
			return;
		}

		$ASTRUNDIR = \FreePBX::Config()->get("ASTRUNDIR");
		$lock = $ASTRUNDIR."/userman.lock";

		$continue = true;
		if(file_exists($lock)) {
			$pid = file_get_contents($lock);
			if(posix_getpgid($pid) !== false) {
				$continue = false;
			} else {
				unlink($lock);
			}
		}
		if($continue) {
			$pid = getmypid();
			file_put_contents($lock,$pid);
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
			unlink($lock);
		} else {
			print_r("User Manager is already syncing (Process: ".$pid.")");
		}

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
			$this->out("\tUpdating User ".$user[1]."...",false);
			call_user_func_array(array($this,"updateUserHook"),$user);
			$this->out("done");
		}
		foreach($this->userHooks['remove'] as $user) {
			$this->out("\tRemoving User ".$user[1]."...",false);
			call_user_func_array(array($this,"delUserHook"),$user);
			$this->out("done");
		}
		foreach($this->groupHooks['add'] as $group) {
			$this->out("\tAdding Group ".$group[1]."...",false);
			call_user_func_array(array($this,"addGroupHook"),$group);
			$this->out("done");
		}
		foreach($this->groupHooks['update'] as $group) {
			$this->out("\tUpdating Group ".$group[1]."...",false);
			call_user_func_array(array($this,"updateGroupHook"),$group);
			$this->out("done");
		}
		foreach($this->groupHooks['remove'] as $group) {
			$this->out("\tRemoving Group ".$group[1]."...",false);
			call_user_func_array(array($this,"delGroupHook"),$group);
			$this->out("done");
		}
	}

	/**
	 * Return an array of permissions for this adaptor
	 */
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
		return parent::getAllUsers('msad');
	}

	/**
	* Get All Users
	*
	* Get a List of all User Manager users and their data
	*
	* @return array
	*/
	public function getAllGroups() {
		return parent::getAllGroups('msad');
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
		$ldap = ldap_connect($this->host,$this->port);
		if($ldap === false) {
			return false;
		}
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

		if(strpos($username,"@") === false) {
			$res = @ldap_bind($ldap, $username."@".$this->domain, $password);
		} else {
			$res = @ldap_bind($ldap, $username, $password);
		}
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
			foreach($groups as $gsid => $group) {
				if(!empty($group) && !empty($u) && ($group['cache']['primarygrouptoken'][0] == $user['primarygroupid'][0])) {
					if(!in_array($u['id'], $group['users'])) {
						$this->out("\tAdding ".$u['username']." to ".$group['groupname']."...",false);
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
						$this->out("Done");
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

	public function sig_handler($signo) {
		switch ($signo) {
			case SIGCLD:
				while(($pid = pcntl_wait($signo, WNOHANG)) > 0){
					$signal = pcntl_wexitstatus ($signo);
					$this->active -= 1;
					echo "Fork ".$signal." has finished\n";
				}
			break;
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
		$userdn = !empty($this->config['userdn']) ? $this->config['userdn'].",".$this->config['dn'] : $this->config['dn'];
		$groupdn = !empty($this->config['groupdnaddition']) ? $this->config['groupdnaddition'].",".$this->config['dn'] : $this->config['dn'];
		$this->out("\t".'ldapsearch -w '.$this->config['password'].' -h '.$this->config['host'].' -p '.$this->config['port'].'  "'.$this->config['username'].'" -b "'.$groupdn.'" -s sub "'.$this->config['groupobjectfilter'].'"');
		$this->out("\tRetrieving all groups...");
		//(".$this->config['usermodifytimestampattr'].">=20010301000000Z)
		$sr = ldap_search($this->ldap, $groupdn, "(&".$this->config['groupobjectfilter']."(objectclass=".$this->config['groupobjectclass']."))", array("*","distinguishedname","primarygrouptoken","objectsid","description","cn",$this->config['groupgidnumberattr'],$this->config['groupdescriptionattr'],$this->config['groupnameattr'], $this->config['groupexternalidattr'], $this->config['groupmemberattr']));
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
			if(!isset($group[$this->config['groupexternalidattr']])) {
				$this->out("\t\tERROR group is missing ".$this->config['groupexternalidattr']." attribute! Cant continue!!");
				continue;
			}
			$sid = $this->binToStrSid($group[$this->config['groupexternalidattr']][0]);
			$this->gcache[$sid] = $group;
			$groupname = $group[$this->config['groupnameattr']][0];
			$um = $this->linkGroup($groupname, 'msad', $sid);
			$description = (!empty($this->config['groupdescriptionattr']) && !empty($group[$this->config['groupdescriptionattr']][0])) ? $group[$this->config['groupdescriptionattr']][0] : '';
			$members = array();
			$this->out("\tWorking on ".$groupname);
			foreach($this->ucache as $usid => $user) {
				if(!empty($user['memberof'])) {
					unset($user['memberof']['count']);
					foreach($user['memberof'] as $gdn) {
						if($gdn == $group['dn']) {
							$m = $this->getUserByid($user['userman'][0]);
							$this->out("\t\t\tAdding ".$m['username']." to group");
							$members[] = $user['userman'][0];
						}
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

		$userdn = !empty($this->config['userdn']) ? $this->config['userdn'].",".$this->config['dn'] : $this->config['dn'];
		$this->out("\t".'ldapsearch -w '.$this->config['password'].' -h '.$this->config['host'].' -p '.$this->config['port'].'  "'.$this->config['username'].'@'.$this->config['domain'].'" -b "'.$userdn.'" -s sub "'.$this->config['userobjectfilter'].'" "'.$this->config['userexternalidattr'].'=*" '.$this->config['userexternalidattr']);
		$this->out("\tRetrieving all users...");

		$sr = ldap_search($this->ldap, $userdn, "(&".$this->config['userobjectfilter']."(objectclass=".$this->config['userobjectclass']."))", array('*',$this->config['userexternalidattr'],$this->config['userprimarygroupattr'],$this->config['usergroupmemberattr']));
		$users = ldap_get_entries($this->ldap, $sr);

		if($users['count'] == 0) {
			$this->out("\tNo users found! Perhaps your query is wrong?");
			return;
		}

		$this->out("\tGot ".$users['count']. " users");

		unset($users['count']);
		//add and update users
		foreach($users as $user) {
			if(!isset($user[$this->config['userexternalidattr']])) {
				$this->out("\t\tERROR user is missing ".$this->config['userexternalidattr']." attribute! Cant continue!!");
				continue;
			}
			$sid = $this->binToStrSid($user[$this->config['userexternalidattr']][0]);
			$username = $user[$this->config['usernameattr']][0];
			$this->ucache[$sid] = $user;
			$um = $this->linkUser($username, 'msad', $sid);
			if($um['status']) {
				$data = array(
					"description" => (!empty($this->config['userdescriptionattr']) && !empty($user[$this->config['userdescriptionattr']][0])) ? $user['description'][0] : '',
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
					$la = $user[$this->config['la']][0];
					$d = $this->FreePBX->Core->getUser($la);
					if(!empty($d)) {
						$data["default_extension"] = $la;
					} else {
						//TODO: Technically we could create an extension here..
						dbug("Extension ". $la . " does not exist, skipping link");
					}
				} elseif(!empty($this->config['la']) && empty($user[$this->config['la']][0])) {
					$data["default_extension"] = 'none';
				}
				$this->updateUserData($um['id'], $data);
				if($um['new']) {
					$this->out("\t\tAdding ".$username);
					$this->userHooks['add'][$um['id']] = array($um['id'], $username, $data['description'], null, false, $data);
				} else {
					$this->out("\t\tUpdating ".$username);
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
}
