<?php
// vim: set ai ts=4 sw=4 ft=php:
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
//	https://msdn.microsoft.com/en-us/library/windows/desktop/ms677605(v=vs.85).aspx
//
namespace FreePBX\modules\Userman\Auth;
use Adldap\Connections\Provider;
use Adldap\Connections\ProviderInterface;
use Exception;
use Adldap\Adldap;
use Adldap\Exceptions\Auth\BindException;
class Msad2 extends Auth {
	private ?Provider $provider = null;
	/**
  * LDAP Object
  */
 private ?ProviderInterface $ldap = null;
	/**
  * Socket Timeout
  */
 private int $timeout = 3;
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
	/**
  * Server Time
  */
 private ?int $currentTime = null;
	/**
  * Results Limit.
  * Everything is paginated but we have to define a limit
  */
 private int $limit = 900;
	/**
  * Server Defaults
  */
 private static array $serverDefaults = ['host' => 'dc.domain.local', 'port' => '389', 'dn' => 'dc=domain,dc=local', 'username' => '', 'domain' => 'domain.local', 'password' => '', 'connection' => '', 'localgroups' => 0, 'createextensions' => '', 'externalidattr' => 'objectGUID', 'descriptionattr' => 'description', 'commonnameattr' => 'cn'];
	/**
  * User Defaults
  */
 private static array $userDefaults = ['userdn' => '', 'userobjectclass' => 'user', 'userobjectfilter' => '(&(objectCategory=Person)(sAMAccountName=*))', 'usernameattr' => 'sAMAccountName', 'userfirstnameattr' => 'givenName', 'userlastnameattr' => 'sn', 'userdisplaynameattr' => 'displayName', 'usertitleattr' => 'title', 'usercompanyattr' => 'company', 'userdepartmentattr' => 'department', 'usercellphoneattr' => 'mobile', 'userworkphoneattr' => 'telephoneNumber', 'userhomephoneattr' => 'homephone', 'userfaxphoneattr' => 'facsimileTelephoneNumber', 'usermailattr' => 'mail', 'usergroupmemberattr' => 'memberOf', 'userpasswordattr' => 'unicodePwd', 'userprimarygroupattr' => 'primarygroupid', 'la' => 'ipphone'];
	/**
  * Group Defaults
  */
 private static array $groupDefaults = ['groupdnaddition' => '', 'groupobjectclass' => 'group', 'groupobjectfilter' => '(objectCategory=Group)', 'groupdescriptionattr' => 'description', 'groupmemberattr' => 'member', 'groupgidnumberattr' => 'primaryGroupToken'];

	private array $userHooks = ['add' => [], 'update' => [], 'remove' => []];

	private array $groupHooks = ['add' => [], 'update' => [], 'remove' => []];


	public function __construct($userman, $freepbx, $config=[]) {
		parent::__construct($userman, $freepbx, $config);
		$this->FreePBX = $freepbx;
		$this->output = null;

		$validKeys = array_merge(self::$serverDefaults,self::$userDefaults,self::$groupDefaults);
		$this->config = [];
		$this->config['id'] = !empty($config['id']) ? $config['id'] : '';
		foreach($validKeys as $key => $value) {
			if($key != "password") {
				$this->config[$key] = (isset($config[$key])) ? strtolower((string) $config[$key]) : strtolower((string) $value);
			} else {
				$this->config[$key] = $config[$key] ?? '';
			}
		}
		if(isset($config['userexternalidattr'])) {
			$this->config['externalidattr'] = strtolower((string) $config['userexternalidattr']);
		}
		if(isset($config['userdescriptionattr'])) {
			$this->config['descriptionattr'] = strtolower((string) $config['userdescriptionattr']);
		}
		if(isset($config['groupnameattr'])) {
			$this->config['commonnameattr'] = strtolower((string) $config['groupnameattr']);
		}
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
		return ["name" => _("Microsoft Active Directory")];
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
			$msad2 = new static($userman, $freepbx, $config);
			try {
				$msad2->connect();
				$status = ["connected" => true, "type" => "success", "message" => _("Connected")];
			} catch(Exception $e) {
				$status = ["connected" => false, "type" => "danger", "message" => $e->getMessage()];
			}
		} elseif(!empty($config['host']) || !empty($config['username']) || !empty($config['password']) || !empty($config['domain'])) {
			$status = ["connected" => false, "type" => "warning", "message" => _("Not all of the connection parameters have been filled out")];
		}
		$defaults = array_merge(self::$serverDefaults,self::$userDefaults,self::$groupDefaults);
		$techs = $freepbx->Core->getAllDriversInfo();
		array_unshift($techs, ['rawName' => '', 'shortName' => _("Don't Create")]);

		$typeauth = self::getShortName();
		$form_data = [
      ['name'		=> $typeauth.'-connection', 'title'		=> _("Secure Connection Type"), 'type'		=> 'list', 'index'		=> true, 'list'		=> [['value' => '', 'text' => _('None')], ['value' => 'tls', 'text' => 'Start TLS'], ['value' => 'ssl', 'text' => 'SSL']], 'value'		=> $config['connection'] ?? $defaults['connection'], 'keys'		=> ['value' => 'value', 'text' 	=> 'text'], 'help'		=> _("The Active Directory secure connection type")],
      ['name'		=> $typeauth.'-host', 'title'		=> _("Host(s)"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['host'], 'opts'		=> ['value' => $config['host'] ?? ''], 'help'		=> _("The active directory host(s), comma/space separated")],
      ['name'		=> $typeauth.'-port', 'title'		=> _("Port"), 'type'		=> 'number', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['port'], 'opts'		=> ['min' => "1", 'max' => "65535", 'value' => $config['port'] ?? $defaults['port']], 'help'		=> sprintf(_("The active directory port, default %s"), $defaults['port'])],
      ['name'		=> $typeauth.'-username', 'title'		=> _("Username"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['username'], 'opts'		=> ['value' => $config['username'] ?? $defaults['username']], 'help'		=> _("The active directory username")],
      ['name'		=> $typeauth.'-password', 'title'		=> _("Password"), 'type' 		=> 'password', 'index'		=> true, 'required'	=> false, 'default'	=> $defaults['password'], 'opts'		=> ['value' => ''], 'help'		=> _("The active directory password. Only write the password if we want to modify it. If none is defined, the current password will be kept.")],
      ['name'		=> $typeauth.'-domain', 'title'		=> _("Domain"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['domain'], 'opts'		=> ['value' => $config['domain'] ?? ''], 'help'		=> _("The active directory domain")],
      ['name'		=> $typeauth.'-dn', 'title'		=> _("Base DN"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['dn'], 'opts'		=> ['value' => $config['dn'] ?? ''], 'help'		=> _("The base DN. Usually in the format of CN=Users,DC=domain,DC=local")],
      ['name'		=> $typeauth.'-status', 'title'		=> _("Status"), 'type' 		=> 'raw', 'index'		=> true, 'value'		=> sprintf('<div id="%s-status" class="bg-%s conection-status"><i class="fa fa-%s"></i>&nbsp; %s</div>', $typeauth, $status['type'],  ($status['type'] == "success" ? 'check' : 'exclamation')  , $status['message']), 'value_raw' => $status, 'help'		=> _("The connection status of the Active Directory Server")],
      ['type' => 'fieldset_init', 'legend' => _("Operational Settings")],
      ['name'		=> $typeauth.'-createextensions', 'title'		=> _("Create Missing Extensions"), 'type'		=> 'list', 'index'		=> true, 'list'		=> $techs, 'value'		=> $config['createextensions'] ?? $defaults['createextensions'], 'keys'		=> ['value' => 'rawName', 'text' 	=> 'shortName'], 'help'		=> _("If enabled and the 'User extension Link attribute' is set, a new extension will be created and linked to this user if one does not exist previously")],
      ['name' 		=> $typeauth.'-localgroups', 'title'		=>  _('Manage groups locally'), 'type' 		=> 'radioset_yn', 'value' 	=> $config['localgroups'] ?? $defaults['localgroups'], 'values'	=> ['y'	=> '1', 'n'	=> '0'], 'index'		=> true, 'help'		=> _("New groups created in this directory will be local and not saved to the Active Directory. Groups synchronised from the remote directory will be read-only.")],
      ['name'		=> $typeauth.'-commonnameattr', 'title'		=> _("Common Name attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['commonnameattr'], 'opts'		=> ['value' => $config['commonnameattr'] ?? $defaults['commonnameattr']], 'help'		=> _("The attribute field to use when loading the object's common name.")],
      ['name'		=> $typeauth.'-descriptionattr', 'title'		=> _("Description attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> false, 'default'	=> $defaults['descriptionattr'], 'opts'		=> ['value' => $config['descriptionattr'] ?? $defaults['descriptionattr']], 'help'		=> _("The attribute field to use when loading the object description.")],
      ['name'		=> $typeauth.'-externalidattr', 'title'		=> _("Unique identifier attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['externalidattr'], 'opts'		=> ['value' => $config['externalidattr'] ?? $defaults['externalidattr']], 'help'		=> _("The attribute field to use for tracking user identity across object renames.")],
      ['type' => 'fieldset_end'],
      ['type' => 'fieldset_init', 'legend' => _("User configuration")],
      ['name'		=> $typeauth.'-userdn', 'title'		=> _("User DN"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> false, 'default'	=> $defaults['userdn'], 'opts'		=> ['value' => $config['userdn'] ?? $defaults['userdn']], 'help'		=> _("This value is used in addition to the base DN when searching and loading users. An example is ou=Users. If no value is supplied, the subtree search will start from the base DN.")],
      ['name'		=> $typeauth.'-userobjectclass', 'title'		=> _("User object class"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['userobjectclass'], 'opts'		=> ['value' => $config['userobjectclass'] ?? $defaults['userobjectclass']], 'help'		=> _("The Active Directoy user object class type to use when loading users.")],
      ['name'		=> $typeauth.'-userobjectfilter', 'title'		=> _("User object filter"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['userobjectfilter'], 'opts'		=> ['value' => $config['userobjectfilter'] ?? $defaults['userobjectfilter']], 'help'		=> _("The filter to use when searching user objects.")],
      ['name'		=> $typeauth.'-usernameattr', 'title'		=> _("User name attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['usernameattr'], 'opts'		=> ['value' => $config['usernameattr'] ?? $defaults['usernameattr']], 'help'		=> _("The attribute field to use on the user object (eg. cn, sAMAccountName)")],
      // array(
      // 	'name'		=> $typeauth.'-usernamerdnattr',
      // 	'title'		=> _("User name RDN attribute"),
      // 	'type' 		=> 'text',
      // 	'index'		=> true,
      // 	'required'	=> true,
      // 	'default'	=> $defaults['usernamerdnattr'],
      // 	'opts'		=> array(
      // 		'value' => isset($config['usernamerdnattr']) ? $config['usernamerdnattr'] : $defaults['usernamerdnattr'],
      // 	),
      // 	'help'		=> _("The RDN to use when loading the user username (eg. cn)."),
      // ),
      ['name'		=> $typeauth.'-userfirstnameattr', 'title'		=> _("User first name attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['userfirstnameattr'], 'opts'		=> ['value' => $config['userfirstnameattr'] ?? $defaults['userfirstnameattr']], 'help'		=> _("The attribute field to use when loading the user first name.")],
      ['name'		=> $typeauth.'-userlastnameattr', 'title'		=> _("User last name attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['userlastnameattr'], 'opts'		=> ['value' => $config['userlastnameattr'] ?? $defaults['userlastnameattr']], 'help'		=> _("The attribute field to use when loading the user last name.")],
      ['name'		=> $typeauth.'-userdisplaynameattr', 'title'		=> _("User display name attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['userdisplaynameattr'], 'opts'		=> ['value' => $config['userdisplaynameattr'] ?? $defaults['userdisplaynameattr']], 'help'		=> _("The attribute field to use when loading the user full name.")],
      ['name'		=> $typeauth.'-usergroupmemberattr', 'title'		=> _("User group attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['usergroupmemberattr'], 'opts'		=> ['value' => $config['usergroupmemberattr'] ?? $defaults['usergroupmemberattr']], 'help'		=> _("The attribute field to use when loading the users groups.")],
      ['name'		=> $typeauth.'-usermailattr', 'title'		=> _("User email attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> false, 'default'	=> $defaults['usermailattr'], 'opts'		=> ['value' => $config['usermailattr'] ?? $defaults['usermailattr']], 'help'		=> _("The attribute field to use when loading the user email.")],
      ['name'		=> $typeauth.'-usertitleattr', 'title'		=> _("User Title attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> false, 'default'	=> $defaults['usertitleattr'], 'opts'		=> ['value' => $config['usertitleattr'] ?? $defaults['usertitleattr']], 'help'		=> _("The attribute field to use when loading the user title.")],
      ['name'		=> $typeauth.'-usercompanyattr', 'title'		=> _("User Company attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> false, 'default'	=> $defaults['usercompanyattr'], 'opts'		=> ['value' => $config['usercompanyattr'] ?? $defaults['usercompanyattr']], 'help'		=> _("The attribute field to use when loading the user company.")],
      ['name'		=> $typeauth.'-userdepartmentattr', 'title'		=> _("User Department attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> false, 'default'	=> $defaults['userdepartmentattr'], 'opts'		=> ['value' => $config['userdepartmentattr'] ?? $defaults['userdepartmentattr']], 'help'		=> _("The attribute field to use when loading the user department.")],
      ['name'		=> $typeauth.'-userhomephoneattr', 'title'		=> _("User Home Phone attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> false, 'default'	=> $defaults['userhomephoneattr'], 'opts'		=> ['value' => $config['userhomephoneattr'] ?? $defaults['userhomephoneattr']], 'help'		=> _("The attribute field to use when loading the user home phone.")],
      ['name'		=> $typeauth.'-userworkphoneattr', 'title'		=> _("User Work Phone attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> false, 'default'	=> $defaults['userworkphoneattr'], 'opts'		=> ['value' => $config['userworkphoneattr'] ?? $defaults['userworkphoneattr']], 'help'		=> _("The attribute field to use when loading the user work phone.")],
      ['name'		=> $typeauth.'-usercellphoneattr', 'title'		=> _("User Cell Phone attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> false, 'default'	=> $defaults['usercellphoneattr'], 'opts'		=> ['value' => $config['usercellphoneattr'] ?? $defaults['usercellphoneattr']], 'help'		=> _("The attribute field to use when loading the user cell phone.")],
      ['name'		=> $typeauth.'-userfaxphoneattr', 'title'		=> _("User Fax attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> false, 'default'	=> $defaults['userfaxphoneattr'], 'opts'		=> ['value' => $config['userfaxphoneattr'] ?? $defaults['userfaxphoneattr']], 'help'		=> _("The attribute field to use when loading the user fax.")],
      ['name'		=> $typeauth.'-la', 'title'		=> _("User extension Link attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> false, 'default'	=> $defaults['la'], 'opts'		=> ['value' => $config['la'] ?? $defaults['la']], 'help'		=> _("If this is set then User Manager will use the defined attribute of the user from the Active Directory server as the extension link. NOTE: If this field is set it will overwrite any manually linked extensions where this attribute extists!! (Try lowercase if it is not working.)")],
      ['type' => 'fieldset_end'],
      ['type' => 'fieldset_init', 'legend' => _("Group configuration")],
      ['name'		=> $typeauth.'-groupdnaddition', 'title'		=> _("Group DN"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> false, 'default'	=> $defaults['groupdnaddition'], 'opts'		=> ['value' => $config['groupdnaddition'] ?? $defaults['groupdnaddition']], 'help'		=> _("This value is used in addition to the base DN when searching and loading groups. An example is ou=Groups. If no value is supplied, the subtree search will start from the base DN.")],
      ['name'		=> $typeauth.'-groupobjectclass', 'title'		=> _("Group object class"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['groupobjectclass'], 'opts'		=> ['value' => $config['groupobjectclass'] ?? $defaults['groupobjectclass']], 'help'		=> _("The Active Directoy user object class type to use when loading groups.")],
      ['name'		=> $typeauth.'-groupobjectfilter', 'title'		=> _("Group object filter"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['groupobjectfilter'], 'opts'		=> ['value' => $config['groupobjectfilter'] ?? $defaults['groupobjectfilter']], 'help'		=> _("The filter to use when searching group objects.")],
      ['name'		=> $typeauth.'-groupmemberattr', 'title'		=> _("Group members attribute"), 'type' 		=> 'text', 'index'		=> true, 'required'	=> true, 'default'	=> $defaults['groupmemberattr'], 'opts'		=> ['value' => $config['groupmemberattr'] ?? $defaults['groupmemberattr']], 'help'		=> _("The attribute field to use when loading the group members.")],
      ['type' => 'fieldset_end'],
  ];
		return ['auth' => $typeauth, 'data' => $form_data];
	}

	/**
	 * Save the configuration about the authentication driver
	 * @param  object $userman The userman object
	 * @param  object $freepbx The FreePBX BMO object
	 * @return mixed          Return true if valid. Otherwise return error string
	 */
	public static function saveConfig($userman, $freepbx) {
		$validKeys = [];
		$validKeys = array_merge($validKeys,array_keys(self::$serverDefaults),array_keys(self::$userDefaults),array_keys(self::$groupDefaults));
		$config = ['authtype' => self::getShortName()];
		foreach($validKeys as $key) {
			$post_key = $config['authtype'].'-'.$key;
			if(isset($_POST[$post_key])) {
				$config[$key] = $_POST[$post_key];
			}
		}
		return $config;
	}

	/**
	 * Return the LDAP object after connect
	 * @return object The LDAP object
	 */
	public function getLDAPObject() {
		$msad2 = null;
  $msad2->connect();
		return $this->ldap;
	}

	/**
	 * Connect to the LDAP server
	 */
	public function connect($reconnect = false) {
		if($reconnect || !$this->ldap) {
			if(!class_exists(\App\Schemas\Msad2::class,false)) {
				include __DIR__."/msad2/Msad2Schema.class.php";
			}
			$mySchema = new \App\Schemas\Msad2($this->config);
			$config = [
				// Mandatory Configuration Options
				'hosts'    		  => preg_split("/[ ,]/", (string) $this->config['host']),
				'base_dn'         => $this->config['dn'],
				'username'        => (preg_match('/^(([^,=\\+<>#;\"\\n]+)=([^,=\\+<>#;\"\\n]+),)*([^,=\\+<>#;\"\\n]+)=([^,=\\+<>#;\"\\n]+)$/', $this->config['username'])===1) ? $this->config['username'] : $this->config['username'].'@'.$this->config['domain'],
				'password'        => $this->config['password'],

				// Optional Configuration Options
				'schema'				=> \App\Schemas\Msad2::class,
				'account_suffix'        => (preg_match('/^(([^,=\\+<>#;\"\\n]+)=([^,=\\+<>#;\"\\n]+),)*([^,=\\+<>#;\"\\n]+)=([^,=\\+<>#;\"\\n]+)$/', $this->config['username'])===1) ? $this->config['domain'] : '@'.$this->config['domain'],
				'port'                  => $this->config['port'],
				'follow_referrals'      => false,
				'use_ssl'               => ($this->config['connection'] == 'ssl'),
				'use_tls'               => ($this->config['connection'] == 'tls'),
				'timeout'               => $this->timeout
			];
			$this->provider = new Provider($config, $connection = null);
			$this->provider->setSchema($mySchema);
			$ad = new Adldap(["default" => $config]);
			$ad->addProvider($this->provider, 'default');
			try {
				$this->ldap = $ad->connect();
			} catch (BindException $e) {
				throw new Exception("Unable to Connect to host! Reason: ".$e->getMessage());
			}
			$rootDse = $this->ldap->search()->getRootDse();
			$this->currentTime = $rootDse->getCurrentTime();
		}
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
 			$this->out("\tUpdating User ".$user[2]."...",false);
 			call_user_func_array($this->updateUserHook(...),$user);
 			$this->out("done");
 		}
 		foreach($this->userHooks['remove'] as $user) {
 			$this->out("\tRemoving User ".$user[1]['username']."...",false);
 			call_user_func_array($this->delUserHook(...),$user);
 			$this->out("done");
 		}
 		foreach($this->groupHooks['add'] as $group) {
 			$this->out("\tAdding Group ".$group[1]."...",false);
 			call_user_func_array($this->addGroupHook(...),$group);
 			$this->out("done");
 		}
 		foreach($this->groupHooks['update'] as $group) {
 			$this->out("\tUpdating Group ".$group[2]."...",false);
 			call_user_func_array($this->updateGroupHook(...),$group);
 			$this->out("done");
 		}
 		foreach($this->groupHooks['remove'] as $group) {
 			$this->out("\tRemoving Group ".$group[1]['groupname']."...",false);
 			call_user_func_array($this->delGroupHook(...),$group);
 			$this->out("done");
 		}
 	}

	/**
	 * Return an array of permissions for this adaptor
	 */
	public function getPermissions() {
		return ["addGroup" => ($this->config['localgroups'] ? true : false), "addUser" => false, "modifyGroup" => false, "modifyUser" => false, "modifyGroupAttrs" => false, "modifyUserAttrs" => false, "removeGroup" => false, "removeUser" => false, "changePassword" => false];
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
		if($this->config['localgroups']) {
			$sql = "INSERT INTO ".$this->groupTable." (`groupname`,`description`,`users`, `auth`, `local`) VALUES (:groupname,:description,:users,:directory,1)";
			$sth = $this->db->prepare($sql);
			try {
				$sth->execute([':directory' => $this->config['id'], ':groupname' => $groupname, ':description' => $description, ':users' => json_encode($users, JSON_THROW_ON_ERROR)]);
			} catch (Exception $e) {
				return ["status" => false, "type" => "danger", "message" => $e->getMessage()];
			}

			$id = $this->db->lastInsertId();
			$this->addGroupHook($id, $groupname, $description, $users);
			return ["status" => true, "type" => "success", "message" => _("Group Successfully Added"), "id" => $id];
		} else {
			return ["status" => false, "type" => "danger", "message" => _("LDAP is in Read Only Mode. Addition denied")];
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
	public function updateGroup($gid, $prevGroupname, $groupname, $description = null, $users = [], $nodisplay = false, $extraData = []){
		$group = $this->getGroupByUsername($prevGroupname);
		if($this->config['localgroups'] && $group['local']) {
			$sql = "UPDATE ".$this->groupTable." SET `groupname` = :groupname, `description` = :description, `users` = :users WHERE `id` = :gid";
			$sth = $this->db->prepare($sql);
			try {
				$sth->execute([':groupname' => $groupname, ':gid' => $gid, ':description' => $description, ':users' => json_encode($users, JSON_THROW_ON_ERROR)]);
			} catch (Exception $e) {
				return ["status" => false, "type" => "danger", "message" => $e->getMessage()];
			}
		}
		$this->updateGroupData($gid, $extraData);
		$this->updateGroupHook($gid, $prevGroupname, $groupname, $description, $group['users'], $nodisplay);
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
		$res = false;
		if (isset($this->config['usernameattr']) && strtolower($this->config['usernameattr']) =='mail') {
			$res = $this->attemptLogin($username, $password, 'mail');
		}
		else if (isset($this->config['usernameattr']) && strtolower($this->config['usernameattr']) =='userprinicipalname') {
			$res = $this->attemptLogin($username, $password, 'userPrincipalName');
		} else {
			$res = $this->provider->auth()->attempt($username, $password);
		}
		if($res) {
			$user = $this->getUserByUsername($username);
		}
		return !empty($user['id']) ? $user['id'] : false;
	}

	private function attemptLogin($username, $password, $attribute) {
		$user = $this->provider->search()->where($attribute, '=', $username)->first();
		if ($user) {
			$username = $user->getFirstAttribute('sAMAccountName');
			return $this->provider->auth()->attempt($username, $password);
		} else {
			return false;
		}
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
			$groups[$gsid]['cache'] = $group;
		}
		$process = [];
		foreach($this->ucache as $usid => $user) {
			$u = $this->getUserByAuthID($usid);
			$pg = $user->getPrimaryGroup();
			if(empty($pg)) {
				$groupSid = preg_replace('/\d+$/', (string) $user->getPrimaryGroupId(), (string) $user->getConvertedSid());
				$this->out("\tUnable to find ".$u['username']."'s primary group");
				$this->out("\t\tGroup ID: ".$user->getPrimaryGroupId());
				$this->out("\t\tUser SID: ".$user->getConvertedSid());
				$this->out("\t\tGroup SID: ".$groupSid);
				continue;
			}
			$pgsid = $pg->getConvertedGuid();
			if(isset($groups[$pgsid]) && !in_array($u['id'], $groups[$pgsid]['users'])) {
				$gid = $groups[$pgsid]['id'];
				$this->out("\tAdding ".$u['username']." to ".$groups[$pgsid]['groupname']."...",false);
				if(empty($process[$gid])) {
					$process[$gid] = ["id" => $gid, "description" => $groups[$pgsid]['description'], "users" => $groups[$pgsid]['users'], "name" => $groups[$pgsid]['groupname']];
				}
				if(!in_array($u['id'],$process[$gid]['users'])) {
					$process[$gid]['users'][] = $u['id'];
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
		if(php_sapi_name() !== 'cli') {
			throw new Exception("Can only update groups over CLI");
		}
		$this->connect();
		$userdn = !empty($this->config['userdn']) ? $this->config['userdn'].",".$this->config['dn'] : $this->config['dn'];
		$groupdn = !empty($this->config['groupdnaddition']) ? $this->config['groupdnaddition'].",".$this->config['dn'] : $this->config['dn'];
		$ldapuri = $this->buildldapuri($this->config['connection'], $this->config['host'], $this->config['port']);
		$this->out("\t".'ldapsearch -w '.$this->config['password'].' -H "'.$ldapuri.'" -D "'.$this->config['username'].'@'.$this->config['domain'].'" -b "'.$groupdn.'" -s sub "(&'.$this->config['groupobjectfilter'].'(objectclass='.$this->config['groupobjectclass'].'))"');
		$this->out("\tRetrieving all groups...");

		$search = $this->ldap->search();
		//(".$this->config['usermodifytimestampattr'].">=20010301000000Z)
		$paginator = $search->in($groupdn)->rawFilter("(&".$this->config['groupobjectfilter']."(objectclass=".$this->config['groupobjectclass']."))")->paginate($this->limit, 1);
		$results = $paginator->getResults();

		if((is_countable($results) ? count($results) : 0) == 0) {
			$this->out("\tNo groups found! Perhaps your query is wrong?");
			return;
		}
		$this->out("\tGot ".(is_countable($results) ? count($results) : 0). " groups");

		foreach($results as $result) {
			$sid = $result->getConvertedGuid();
			if(empty($sid)) {
				$this->out("\t\tERROR Group is missing ".$this->config['externalidattr']." attribute! Cant continue!!");
				continue;
			}
			$this->gcache[$sid] = $result;
			$groupname = $result->getCommonName();
			$um = $this->linkGroup($groupname, $sid);
			$description = !is_null($result->getDescription()) ? $result->getDescription() : '';
			$members = [];
			$this->out("\tWorking on ".$groupname);
			foreach($result->getMembers() as $member) {
				$m = $this->getUserByAuthID($member->getConvertedGuid());
				if(!empty($m)) {
					$this->out("\t\t\tAdding ".$m['username']." to group");
					$members[] = $m['id'];
				}
			}
			if($um['status']) {
				$this->updateGroupData($um['id'], ["description" => $description, "users" => $members]);
				if($um['new']) {
					$this->out("\t\tAdding ".$groupname);
					$this->groupHooks['add'][$um['id']] = [$um['id'], $groupname, $description, $members];
				} else {
					$this->out("\t\tUpdating ".$groupname);
					$this->groupHooks['update'][$um['id']] = [$um['id'], $um['prevGroupname'], $groupname, $description, $members];
				}
			}
		}

		//remove users
		$fgroups = $this->getAllGroups();
		foreach($fgroups as $group) {
			if($group['local']) {
				$this->out("\tSkipping local group '".$group['groupname']."'");
				continue;
			}
			if(!isset($this->gcache[$group['authid']])) {
				$this->out("\t\tDeleting ".$group['groupname']);
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

		$userdn = !empty($this->config['userdn']) ? $this->config['userdn'].",".$this->config['dn'] : $this->config['dn'];
		$ldapuri = $this->buildldapuri($this->config['connection'], $this->config['host'], $this->config['port']);
		$this->out("\t".'ldapsearch -w '.$this->config['password'].' -H "'.$ldapuri.'" -D "'.$this->config['username'].'@'.$this->config['domain'].'" -b "'.$userdn.'" -s sub "(&'.$this->config['userobjectfilter'].'(objectclass='.$this->config['userobjectclass'].'))"');
		$this->out("\tRetrieving all users...");

		$search = $this->ldap->search();
		$paginator = $search->in($userdn)->rawFilter("(&".$this->config['userobjectfilter']."(objectclass=".$this->config['userobjectclass']."))")->paginate($this->limit, 1);
		$results = $paginator->getResults();

		if((is_countable($results) ? count($results) : 0) == 0) {
			$this->out("\tNo users found! Perhaps your query is wrong?");
			return;
		}

		$this->out("\tGot ".(is_countable($results) ? count($results) : 0). " users");

		foreach($results as $result) {
			$sid = $result->getConvertedGuid();
			if(empty($sid)) {
				$this->out("\t\tERROR User is missing ".$this->config['externalidattr']." attribute! Cant continue!!");
				continue;
			}
			$username = $result->getAccountName();
			if ($result->hasAttribute('userprincipalname') && empty($username)) {
				$userPrincipalName = $result->getAttribute('userprincipalname');
				$username = isset($userPrincipalName[0]) ? $userPrincipalName[0] :'';
			}
			if(empty($username)) {
				$this->out("\t\tUsername is blank! Skipping unknown user");
				continue;
			}
			$this->ucache[$sid] = $result;
			$um = $this->linkUser($username, $sid);
			if($um['status']) {
				if($um['new']) {
					$this->out("\t\tAdding ".$username);
				} else {
					$this->out("\t\tUpdating ".$username);
				}
				$data = ["description" => !is_null($result->getDescription()) ? $result->getDescription() : '', "primary_group" => !is_null($result->getPrimaryGroupId()) ? $result->getPrimaryGroupId() : '', "fname" => !is_null($result->getFirstName()) ? $result->getFirstName() : '', "lname" => !is_null($result->getLastName()) ? $result->getLastName() : '', "displayname" => !is_null($result->getDisplayName()) ? $result->getDisplayName() : '', "department" => !empty($this->config['userdepartmentattr']) && !is_null($result->getAttribute($this->config['userdepartmentattr'],0)) ? $result->getAttribute($this->config['userdepartmentattr'],0) : '', "title" => !is_null($result->getTitle()) ? $result->getTitle() : '', "email" => !is_null($result->getEmail()) ? $result->getEmail() : '', "company" => !empty($this->config['usercompanyattr']) && !is_null($result->getAttribute($this->config['usercompanyattr'],0)) ? $result->getAttribute($this->config['usercompanyattr'],0) : '', "cell" => !empty($this->config['usercellphoneattr']) && !is_null($result->getAttribute($this->config['usercellphoneattr'],0)) ? $result->getAttribute($this->config['usercellphoneattr'],0) : '', "work" => !empty($this->config['userworkphoneattr']) && !is_null($result->getAttribute($this->config['userworkphoneattr'],0)) ? $result->getAttribute($this->config['userworkphoneattr'],0) : '', "fax" => !empty($this->config['userfaxphoneattr']) && !is_null($result->getAttribute($this->config['userfaxphoneattr'],0)) ? $result->getAttribute($this->config['userfaxphoneattr'],0) : '', "home" => !empty($this->config['userhomephoneattr']) && !is_null($result->getAttribute($this->config['userhomephoneattr'],0)) ? $result->getAttribute($this->config['userhomephoneattr'],0) : ''];
				if(!empty($this->config['la']) && !is_null($result->getAttribute($this->config['la'],0))) {
					$extension = $result->getAttribute($this->config['la'],0);
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
								$settings['outboundcid'] = $data['outboundcid'] ?? '';
								try {
									if(!$this->FreePBX->Core->addUser($extension, $settings)) {
										//cleanup
										$this->FreePBX->Core->delDevice($extension);
										$this->out("\t\t\tThere was an unknown error creating this extension");
									}
									$this->out("\t\t\tLinking Extension ".$extension." to ".$username);
									$data["default_extension"] = $extension;
								} catch(Exception) {
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
				} elseif(!empty($this->config['la']) && empty($result->getAttribute($this->config['la'],0))) {
					$data["default_extension"] = 'none';
				}
				$this->updateUserData($um['id'], $data);
				if($um['new']) {
					$this->userHooks['add'][$um['id']] = [$um['id'], $username, $data['description'], null, false, $data];
				} else {
					$this->userHooks['update'][$um['id']] = [$um['id'], $um['prevUsername'], $username, $data['description'], null, $data];
				}
				$this->ucache[$sid]['userman'][0] = $um['id'];
			} else {
				$this->out("\t\t\tThere was an error linking '".$username."'. Error was '".$um['message']."'");
			}
		}
		//remove users
		$fusers = $this->getAllUsers();
		foreach($fusers as $user) {
			if(!isset($this->ucache[$user['authid']])) {
				$this->out("\t\tDeleting ".$user['username']);
				$this->deleteUserByID($user['id'], false);
				$this->userHooks['remove'][$user['id']] = [$user['id'], $user];
			}
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

	private function serviceping($hosts, $port=389, $timeout=1) {
		foreach (preg_split("/[ ,]/", (string) $hosts) as $host) {
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
		$securearray = ["ldaps", "ssl", "tls"];
		$uriarray = [];
		if (in_array($connection, $securearray)) {
			$proto = 'ldaps';
		} else {
			$proto = 'ldap';
		}
		foreach (preg_split("/[ ,]/", (string) $hosts) as $host) {
			array_push($uriarray, $proto . "://" . $host . ":" . $port);
		}
		return implode(" ", $uriarray);
	}
}

