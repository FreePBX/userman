<?php
// vim: set ai ts=4 sw=4 ft=php:
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
//	https://msdn.microsoft.com/en-us/library/windows/desktop/ms677605(v=vs.85).aspx
//
namespace FreePBX\modules\Userman\Auth;
use Exception;
use LdapRecord\Auth\BindException;
use LdapRecord\Connection;
use LdapRecord\Models\ActiveDirectory\Group as AdGroup;
use LdapRecord\Models\ActiveDirectory\User as AdUser;
class Msad extends Auth {
	/**
	 * LDAP connection
	 */
	private ?Connection $connection = null;
	/**
	 * @deprecated Kept for callers expecting getLDAPObject()
	 */
	private ?Connection $ldap = null;
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
  */
 private array $ucache = [];
	/**
  * Group Cache
  * cache requests throughout this class
  */
 private array $gcache = [];

	private int $active = 0;

	/**
  * Private Group Cache
  * cache requests throughout this class
  */
 private array $pucache = [];

	/**
  * Results Limit.
  * Everything is paginated but we have to define a limit
  */
 private int $limit = 900;

	/**
	 * The account suffix taken from configuration
	 * @var string
	 */
	private $account_suffix;

	/**
	 * Use or not startTLS
	 * @var boolean
	 */
	private $use_tls;

	private array $userHooks = ['add' => [], 'update' => [], 'remove' => []];

	private array $groupHooks = ['add' => [], 'update' => [], 'remove' => []];

	public function __construct($userman, $freepbx, $config=[]) {
		parent::__construct($userman, $freepbx, $config);
		$this->FreePBX = $freepbx;
		$this->host = $config['host'];
		$this->port = !empty($config['port']) ? $config['port'] : 389;
		$this->dn = $config['dn'];
		$this->domain = $config['domain'];
		$this->user = $config['username'];
		$this->password = $config['password'];
		$this->linkAttr = isset($config['la']) ? strtolower((string) $config['la']) : '';
		$this->account_suffix = !empty($config['account_suffix']) ? $config['account_suffix'] : $config['domain'];
		$this->use_tls = $config['use_tls'] ?? false;
		$this->output = null;
	}

	/**
	* Get information about this authentication driver
	* @param  object $userman The userman object
	* @param  object $freepbx The FreePBX BMO object
	* @return array          Array of information about this driver
	*/
	public static function getInfo($userman, $freepbx) {
		if(!function_exists('ldap_connect')) {
			return [];
		}
		return ["name" => _("Microsoft Active Directory (Legacy)")];
	}

	/**
	 * Get the configuration display of the authentication driver
	 * @param  object $userman The userman object
	 * @param  object $freepbx The FreePBX BMO object
	 * @return string          array with the name of the authentication device, and an array
	 * 						   with all the configurations of this authentication device 
	 */
	public static function getConfig($userman, $freepbx, $config) {
		$status = ["connected" => false, "type" => "info", "message" => _("Not Connected")];
		if(!empty($config['host']) && !empty($config['username']) && !empty($config['password']) && !empty($config['domain'])) {
			$msad = new static($userman, $freepbx, $config);
			try {
				$msad->connect();
				$status = ["connected" => true, "type" => "success", "message" => _("Connected")];
			} catch(Exception $e) {
				$status = ["connected" => false, "type" => "danger", "message" => $e->getMessage()];
			}
		} elseif(!empty($config['host']) || !empty($config['username']) || !empty($config['password']) || !empty($config['domain'])) {
			$status = ["connected" => false, "type" => "warning", "message" => _("Not all of the connection parameters have been filled out")];
		}

		$typeauth = self::getShortName();
		$form_data = [['name'		=> $typeauth.'-host', 'title'		=> _("Host"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> 'dc.domain.local', 'opts'		=> ['value' => $config['host'] ?? ''], 'help'		=> _("The active directory host")], ['name'		=> $typeauth.'-port', 'title'		=> _("Port"), 'type'		=> 'number', 'index'		=> true, 'required'	=> true, 'default'	=> 389, 'opts'		=> ['min' => "1", 'max' => "65535", 'value' => $config['port'] ?? '389'], 'help'		=> sprintf("The active directory port, default 389")], ['name'		=> $typeauth.'-username', 'title'		=> _("Username"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'opts'		=> ['value' => $config['username'] ?? ''], 'help'		=> _("The active directory username")], ['name'		=> $typeauth.'-password', 'title'		=> _("Password"), 'type' 		=> 'password', 'index'		=> true, 'required'	=> false, 'opts'		=> ['value' => ''], 'help'		=> _("The active directory password. Only write the password if we want to modify it. If none is defined, the current password will be kept.")], ['name'		=> $typeauth.'-domain', 'title'		=> _("Domain"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> 'domain.local', 'opts'		=> ['value' => $config['domain'] ?? ''], 'help'		=> _("The active directory domain")], ['name'		=> $typeauth.'-dn', 'title'		=> _("Base DN"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> 'cn=Users,dc=domain,dc=local', 'opts'		=> ['value' => $config['dn'] ?? ''], 'help'		=> _("The base DN. Usually in the format of CN=Users,DC=domain,DC=local")], ['name'		=> $typeauth.'-la', 'title'		=> _("Extension Link Attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> false, 'opts'		=> ['value' => $config['la'] ?? ''], 'help'		=> _("If this is set then User Manager will use the defined attribute of the user from the Active Directory server as the extension link. NOTE: If this field is set it will overwrite any manually linked extensions where this attribute extists!!")], ['name'		=> $typeauth.'-status', 'title'		=> _("Status"), 'type' 		=> 'raw', 'index'		=> true, 'value'		=> sprintf('<div id="%s-status" class="bg-%s conection-status"><i class="fa fa-%s"></i>&nbsp; %s</div>', $typeauth, $status['type'],  ($status['type'] == "success" ? 'check' : 'exclamation')  , $status['message']), 'value_raw' => $status, 'help'		=> _("The connection status of the Active Directory Server")]];
		return ['auth' => $typeauth, 'data' => $form_data];
	}

	/**
	 * Save the configuration about the authentication driver
	 * @param  object $userman The userman object
	 * @param  object $freepbx The FreePBX BMO object
	 * @return mixed          Return true if valid. Otherwise return error string
	 */
	public static function saveConfig($userman, $freepbx) {
		$typeauth = self::getShortName();
		$config = ['authtype' => $typeauth, "host" => $_REQUEST[$typeauth.'-host'], "port" => $_REQUEST[$typeauth.'-port'], "username" => $_REQUEST[$typeauth.'-username'], "password" => $_REQUEST[$typeauth.'-password'], "domain" => $_REQUEST[$typeauth.'-domain'], "dn" => $_REQUEST[$typeauth.'-dn'], "la" => $_REQUEST[$typeauth.'-la'], "sync" => $_REQUEST['sync']];
		return $config;
	}

	/**
	 * Return the LDAP connection after connect
	 * @return Connection The LDAP connection
	 */
	public function getLDAPObject() {
		$this->connect();
		return $this->connection;
	}

	/**
	 * Connect to the LDAP server
	 */
	public function connect($reconnect = false) {
		if($reconnect || !$this->connection) {
			$username = $this->user;
			if (!str_contains((string) $username, '@') && !str_contains((string) $username, '\\') && !str_contains((string) $username, '=')) {
				$suffix = ltrim((string) $this->account_suffix, '@');
				$username = $username.'@'.$suffix;
			}
			$config = [
				'hosts'            => [$this->host],
				'base_dn'          => $this->dn,
				'username'         => $username,
				'password'         => $this->password,
				'port'             => (int) $this->port,
				'use_tls'          => (bool) $this->use_tls,
				'follow_referrals' => false,
			];

			$this->connection = new Connection($config);
			try {
				$this->connection->connect();
			} catch (BindException $e) {
				throw new Exception("Unable to Connect to host! Reason: ".$e->getMessage());
			}
			$this->ldap = $this->connection;
		}
	}

	/**
	 * First attribute value helper
	 */
	private function firstAttr($entry, $attribute) {
		if (empty($attribute) || !$entry) {
			return null;
		}
		return $entry->getFirstAttribute($attribute);
	}

	/**
	 * Sync users and groups to the local database
	 */
	public function sync($output=null) {

		if(php_sapi_name() !== 'cli') {
			$path = $this->FreePBX->Config->get("AMPSBIN");
			exec($path."/fwconsole userman --sync ".escapeshellarg((string) $this->config['id'])." --force");
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
			call_user_func_array($this->addUserHook(...),$user);
			$this->out("done");
		}
		foreach($this->userHooks['update'] as $user) {
			$this->out("\tUpdating User ".$user[1]."...",false);
			call_user_func_array($this->updateUserHook(...),$user);
			$this->out("done");
		}
		foreach($this->userHooks['remove'] as $user) {
			$this->out("\tRemoving User ".$user[1]."...",false);
			call_user_func_array($this->delUserHook(...),$user);
			$this->out("done");
		}
		foreach($this->groupHooks['add'] as $group) {
			$this->out("\tAdding Group ".$group[1]."...",false);
			call_user_func_array($this->addGroupHook(...),$group);
			$this->out("done");
		}
		foreach($this->groupHooks['update'] as $group) {
			$this->out("\tUpdating Group ".$group[1]."...",false);
			call_user_func_array($this->updateGroupHook(...),$group);
			$this->out("done");
		}
		foreach($this->groupHooks['remove'] as $group) {
			$this->out("\tRemoving Group ".$group[1]."...",false);
			call_user_func_array($this->delGroupHook(...),$group);
			$this->out("done");
		}
	}

	/**
	 * Return an array of permissions for this adaptor
	 */
	public function getPermissions() {
		return ["addGroup" => false, "addUser" => false, "modifyGroup" => false, "modifyUser" => false, "modifyGroupAttrs" => false, "modifyUserAttrs" => false, "removeGroup" => false, "removeUser" => false, "changePassword" => false];
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
	public function addUser($username, $password, $default='none', $description=null, $extraData=[], $encrypt = true) {
		return ["status" => false, "type" => "danger", "message" => _("LDAP is in Read Only Mode. Addition denied")];
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
	public function addGroup($groupname, $description=null, $users=[]) {
		return ["status" => false, "type" => "danger", "message" => _("LDAP is in Read Only Mode. Addition denied")];
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
	public function updateUser($uid, $prevUsername, $username, $default='none', $description=null, $extraData=[], $password=null, $nodisplay=false) {
		$sql = "UPDATE ".$this->userTable." SET `default_extension` = :default_extension WHERE `id` = :uid";
		$sth = $this->db->prepare($sql);
		try {
			$sth->execute([':default_extension' => $default, ':uid' => $uid]);
		} catch (Exception $e) {
			return ["status" => false, "type" => "danger", "message" => $e->getMessage()];
		}
		$this->updateUserHook($uid, $prevUsername, $username, $description, $password, $extraData, $nodisplay);
		return ["status" => true, "type" => "success", "message" => _("User updated"), "id" => $uid];
	}

	/**
	 * Update Group
	 * @param string $prevGroupname The group's previous name
	 * @param string $groupname     The Groupname
	 * @param string $description   The group description
	 * @param array  $users         Array of users in this Group
	 */
	public function updateGroup($gid, $prevGroupname, $groupname, $description=null, $users=[], $nodisplay=false, $extraData=[]) {
		$group = $this->getGroupByUsername($prevGroupname);
		$this->updateGroupData($gid, $extraData);
		$this->updateGroupHook($gid, $prevGroupname, $groupname, $description, $group['users'],$nodisplay);
		return ["status" => true, "type" => "success", "message" => _("Group updated"), "id" => $gid];
	}

	/**
	 * Check Credentials against username with a passworded sha
	 * @param {string} $username      The username
	 * @param {string} $password_sha1 The sha
	 */
	public function checkCredentials($username, $password) {
		$user = [];
  $this->connect();
		$ldap = ldap_connect($this->host,$this->port);
		if($ldap === false) {
			return false;
		}
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

		if(!str_contains((string) $username,"@")) {
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

		$groups = [];
		foreach($this->gcache as $gsid => $group) {
			$groups[$gsid] = $this->getGroupByAuthID($gsid);
		}

		$process = [];
		foreach($this->pucache as $usid => $gsid) {
			$u = $this->getUserByAuthID($usid);
			$gsid = $this->limitString($gsid);
			if(!empty($u) && !empty($groups[$gsid])) {
				$group = $groups[$gsid];
				$this->out("\tAdding ".$u['username']." to ".$group['groupname']."...",false);
				if(empty($process[$group['id']])) {
					$process[$group['id']] = ["id" => $group['id'], "description" => $group['description'], "users" => $group['users'], "name" => $group['groupname']];
				}
				if(!in_array($u['id'],$process[$group['id']]['users'])) {
					$process[$group['id']]['users'][] = $u['id'];
				}
				$this->out("Done");
			}
		}

		foreach($process as $id => $g) {
			$this->updateGroupData($g['id'], ["description" => $g['description'], "users" => $g['users']]);
			if(isset($this->groupHooks['update'][$g['id']])) {
				$this->groupHooks['update'][$g['id']] = [$g['id'], $this->groupHooks['update'][$g['id']][2], $g['name'], $g['description'], $g['users']];
			} else {
				$this->groupHooks['update'][$g['id']] = [$g['id'], $g['name'], $g['name'], $g['description'], $g['users']];
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
		$this->connect();

		$results = $this->connection->query()->model(new AdGroup)->paginate($this->limit);
		$this->out("Found ".(is_countable($results) ? count($results) : 0). " groups");

		$dnToAuthId = [];
		foreach ($this->ucache as $usid => $userEntry) {
			$dn = $userEntry->getDn();
			if (!empty($dn)) {
				$dnToAuthId[strtolower($dn)] = $usid;
			}
		}

		$this->out("Adding Users from non-primary groups...");
		foreach($results as $group) {
			$sid = $this->limitString($group->getConvertedSid());
			if (empty($sid)) {
				continue;
			}
			$groupname = $this->firstAttr($group, 'cn') ?? $this->firstAttr($group, 'samaccountname');
			if (empty($groupname)) {
				continue;
			}
			$members = [];
			$memberDns = $group->getAttribute('member') ?? [];
			foreach ($memberDns as $memberDn) {
				$authId = $dnToAuthId[strtolower((string) $memberDn)] ?? null;
				if ($authId === null) {
					continue;
				}
				$u = $this->getUserByAuthID($authId);
				if (!empty($u)) {
					$members[] = $u['id'];
				}
			}
			$this->gcache[$sid] = $group;
			$um = $this->linkGroup($groupname, $sid);
			if($um['status']) {
				$this->out("\t".$groupname. ": ".$um['message']);
				$this->out("\t\tFound ".(is_countable($members) ? count($members) : 0). " users in ".$groupname);
				$description = $this->firstAttr($group, 'description') ?? '';
				$this->updateGroupData($um['id'], ["description" => $description, "users" => $members]);
				if($um['new']) {
					$this->groupHooks['add'][$um['id']] = [$um['id'], $groupname, $description, $members];
				} else {
					$this->groupHooks['update'][$um['id']] = [$um['id'], $um['prevGroupname'], $groupname, $description, $members];
				}
			}
		}

		//remove groups
		$fgroups = $this->getAllGroups();
		foreach($fgroups as $group) {
			if(!isset($this->gcache[$group['authid']])) {
				$this->deleteGroupByGID($group['id'], false);
				$this->groupHooks['remove'][$group['id']] = [$group['id'], $group];
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

		$results = $this->connection->query()->model(new AdUser)->paginate($this->limit);
		$this->out("Found ".(is_countable($results) ? count($results) : 0). " users");

		foreach($results as $result) {
			$sid = $this->limitString($result->getConvertedSid());
			if (empty($sid)) {
				continue;
			}
			$this->ucache[$sid] = $result; //store object

			$primaryGroupId = $this->firstAttr($result, 'primarygroupid');
			$primaryGroupSid = null;
			if (!empty($primaryGroupId)) {
				$primaryGroupSid = preg_replace('/\d+$/', (string) $primaryGroupId, (string) $result->getConvertedSid());
			}
			$this->pucache[$sid] = $primaryGroupSid;

			$username = $this->firstAttr($result, 'samaccountname');
			if (empty($username)) {
				continue;
			}
			$um = $this->linkUser($username, $sid);
			if($um['status']) {
				$this->out("\t".$username. ": ".$um['message']);
				$data = ["description" => $this->firstAttr($result, 'description') ?? '', "primary_group" => $primaryGroupId ?? '', "fname" => $this->firstAttr($result, 'givenname') ?? '', "lname" => $this->firstAttr($result, 'sn') ?? '', "displayname" => $this->firstAttr($result, 'displayname') ?? '', "department" => $this->firstAttr($result, 'department') ?? '', "email" => $this->firstAttr($result, 'mail') ?? '', "cell" => $this->firstAttr($result, 'mobile') ?? '', "work" => $this->firstAttr($result, 'telephonenumber') ?? ''];
				//automatically assign Extension to this User
				if(!empty($this->linkAttr) && !empty($this->firstAttr($result, $this->linkAttr))) {
					$ext = $this->firstAttr($result, $this->linkAttr);
					$d = $this->FreePBX->Core->getUser($ext);
					if(!empty($d)) {
						$data["default_extension"] = !empty($ext) ? $ext : '';
					} else {
						//TODO: Technically we could create an extension here..
						dbug("Extension ".$ext . " does not exist, skipping link");
					}
				} elseif(!empty($this->linkAttr) && empty($this->firstAttr($result, $this->linkAttr))) {
					$data["default_extension"] = 'none';
				}
				$this->updateUserData($um['id'], $data);
				if($um['new']) {
					$this->userHooks['add'][$um['id']] = [$um['id'], $username, $data['description'], null, false, $data];
				} else {
					$this->userHooks['update'][$um['id']] = [$um['id'], $um['prevUsername'], $username, $data['description'], null, $data];
				}
			}
		}
		//remove users
		$fusers = $this->getAllUsers();
		foreach($fusers as $user) {
			if(!isset($this->ucache[$user['authid']])) {
				$this->deleteUserByID($user['id'], false);
				$this->userHooks['remove'][$user['id']] = [$user['id'], $user];
			}
		}
	}

	/**
	 * Turns a binary SID into a String
	 * @param  string $binsid The binary string
	 */
	public function binToStrSid($binsid) {
		$subauth = [];
  $hex_sid = bin2hex($binsid);
		$rev = hexdec(substr($hex_sid, 0, 2));
		$subcount = hexdec(substr($hex_sid, 2, 2));
		$auth = hexdec(substr($hex_sid, 4, 12));
		$result    = "$rev-$auth";

		for ($x=0;$x < $subcount; $x++) {
			$subauth[$x] =
			hexdec((string) $this->littleEndian(substr($hex_sid, 16 + ($x * 8), 8)));
			$result .= "-" . $subauth[$x];
		}

		// Cheat by tacking on the S-
		$string = 'S-' . $result;
		$string = (strlen($string) > 255) ? substr($string,0,255) : $string;
		return $string;
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

	public function sig_handler($signo) {
		switch($signo) {
			case SIGCLD:
				while (($pid = pcntl_wait($signo, WNOHANG)) > 0) {
					$signal = pcntl_wexitstatus($signo);
					$this->active -= 1;
				}

				break;
		}
	}

	/**
	 * Debug messages
	 * @param  string $message The message
	 * @param  boolean $nl      New line or not
	 */
	private function out($message,$nl=true) {
		$date = date("Y-m-d_H:i:s");
		if(is_object($this->output) && $this->output->isVerbose()) {
			if($nl) {
				$this->output->writeln($date.' -'.$message);
			} else {
				$this->output->write($date.' -'.$message);
			}
		} elseif(!is_object($this->output)) {
			dbug($message);
		}
	}

	private function limitString($string) {
		return (strlen((string) $string) > 255) ? substr((string) $string,0,255) : $string;
	}
}
