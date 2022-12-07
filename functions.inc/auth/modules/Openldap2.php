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
use Adldap\Adldap;
use Adldap\Exceptions\Auth\BindException;
class Openldap2 extends Auth {
	private $provider = null;
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

	private $limit = 900;

	private $allowLdapProtocolVersion = array(2,3);

	private static $serverDefaults = array(
		'host' => 'ldap.domain.local',
		'port' => '389',
		'version' => 3,
		'basedn' => 'dc=domain,dc=local',
		'username' => 'cn=manager,dc=domain,dc=local',
		'password' => '',
		'connection' => '',
		'localgroups' => 0,
		'createextensions' => '',
		'externalidattr' => 'entryUUID',
		'descriptionattr' => 'description',
		'commonnameattr' => 'cn'
	);

	private static $userDefaults = array(
		'userdn' => 'ou=People', /** User DN **/
		'userobjectclass' => 'posixAccount', /** Object Class User **/
		'userobjectfilter' => '(objectclass=posixAccount)', /** User Object Filter **/
		'usernameattr' => 'uid', /** Account Name **/
		'userfirstnameattr' => 'givenName', /** First Name **/
		'userlastnameattr' => 'sn', /** Last Name **/
		'userdisplaynameattr' => 'displayName', /** Display Name **/
		'usertitleattr' => 'title', /** Personal Title TODO: MISS **/
		'usercompanyattr' => 'o', /** Company **/
		'userdepartmentattr' => 'ou', /** Department **/
		'usercellphoneattr' => 'mobile', /** Cell Phone **/
		'userworkphoneattr' => 'telephoneNumber', /** Work Phone **/
		'userhomephoneattr' => 'homePhone', /** Home Phone **/
		'userfaxphoneattr' => 'facsimileTelephoneNumber', /** Fax Phone **/
		'usermailattr' => 'mail', /** email **/
		'usergroupmemberattr' => 'memberOf', /** memberOf **/
		'userpasswordattr' => 'userPassword', /** Unicode Password **/
		'userprimarygroupattr' => 'gidNumber', /** primaryGroupId **/
		'la' => 'AstExtension'
	);

	private static $groupDefaults = array(
		'groupdnaddition' => 'ou=Group', /** Group DN **/
		'groupobjectclass' => 'posixGroup', /** Object Class Group **/
		'groupobjectfilter' => '(objectclass=posixGroup)', /** Group Object Filter **/
		'groupmemberattr' => 'memberUid', /** member **/
		'groupgidnumberattr' => 'gidNumber',
		'groupmemberidentifierattr' => 'uid' /** User Member Identificacion **/
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
			if($key != "password" and $key != "userobjectfilter") {
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
	 * @return string          array with the name of the authentication device, and an array
	 * 						   with all the configurations of this authentication device 
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
		array_unshift($techs, array('rawName' => '', 'shortName' => _("Don't Create")));

		$typeauth = self::getShortName();
		$form_data = array(
			array(
				'name'		=> $typeauth.'-host',
				'title'		=> _("Host(s)"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['host'],
				'opts'		=> array(
					'value' => isset($config['host']) ? $config['host'] : '',
				),
				'help'		=> _("The OpenLDAP host(s), comma/space separated"),
			),
			array(
				'name'		=> $typeauth.'-post',
				'title'		=> _("Port"),
				'type'		=> 'number',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['port'],
				'opts'		=> array(
					'min' => "1",
					'max' => "65535",
					'value' => isset($config['port']) ? $config['port'] : $defaults['port'],
				),
				'help'		=> sprintf("The OpenLDAP port, default %s", $defaults['port']),
			),
			array(
				'name'		=> $typeauth.'-version',
				'title'		=> _("Protocol Version"),
				'type'		=> 'list',
				'index'		=> true,
				'list'		=> array(
					array('value' => '3', 'text' => '3'),
					array('value' => '2', 'text' => '2'),
				),
				'value'		=> isset($config['version']) ? $config['version'] : $defaults['version'],
				'keys'		=> array(
					'value' => 'value',
					'text' 	=> 'text',
				),
				'help'		=> _("Version of the connection protocol with the LDAP server"),
			),
			array(
				'name'		=> $typeauth.'-connection',
				'title'		=> _("Secure Connection Type"),
				'type'		=> 'list',
				'index'		=> true,
				'list'		=> array(
					array('value' => '',    'text' => _('None')),
					array('value' => 'tls', 'text' => 'Start TLS'),
					array('value' => 'ssl', 'text' => 'SSL'),
				),
				'value'		=> isset($config['connection']) ? $config['connection'] : $defaults['connection'],
				'keys'		=> array(
					'value' => 'value',
					'text' 	=> 'text',
				),
				'help'		=> _("The OpenLDAP secure connection type"),
			),
			array(
				'name'		=> $typeauth.'-username',
				'title'		=> _("Bind DN or Username"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['username'],
				'opts'		=> array(
					'value' => isset($config['username']) ? $config['username'] : '',
				),
				'help'		=> _("The OpenLDAP username"),
			),
			array(
				'name'		=> $typeauth.'-password',
				'title'		=> _("Password"),
				'type' 		=> 'password',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['password'],
				'opts'		=> array(
					'value' => '',
				),
				'help'		=> _("The OpenLDAP password. Only write the password if we want to modify it. If none is defined, the current password will be kept."),
			),
			array(
				'name'		=> $typeauth.'-basedn',
				'title'		=> _("Base DN"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['basedn'],
				'opts'		=> array(
					'value' => isset($config['basedn']) ? $config['basedn'] : '',
				),
				'help'		=> _("The OpenLDAP Base-DN. Usually in the format of DC=domain,DC=com"),
			),
			array(
				'name'		=> $typeauth.'-status',
				'title'		=> _("Status"),
				'type' 		=> 'raw',
				'index'		=> true,
				'value'		=> sprintf('<div id="%s-status" class="bg-%s conection-status"><i class="fa fa-%s"></i>&nbsp; %s</div>', $typeauth, $status['type'],  ($status['type'] == "success" ? 'check' : 'exclamation')  , $status['message']),
				'value_raw' => $status,
				'help'		=> _("The connection status of the OpenLDAP Server"),
			),


			array('type' => 'fieldset_init', 'legend' => _("Operational Settings")),
			array(
				'name'		=> $typeauth.'-createextensions',
				'title'		=> _("Create Missing Extensions"),
				'type'		=> 'list',
				'index'		=> true,
				'list'		=> $techs,
				'value'		=> isset($config['createextensions']) ? $config['createextensions'] : $defaults['createextensions'],
				'keys'		=> array(
					'value' => 'rawName',
					'text' 	=> 'shortName',
				),
				'help'		=> _("If enabled and the 'User extension Link attribute' is set, a new extension will be created and linked to this user if one does not exist previously"),
			),
			array(
				'name' 		=> $typeauth.'-localgroups',
				'title'		=>  _('Manage groups locally'),
				'type' 		=> 'radioset_yn',
				'value' 	=> isset($config['localgroups']) ? $config['localgroups'] : $defaults['localgroups'],
				'values'	=> array(
					'y'	=> '1',
					'n'	=> '0',
				),
				'index'		=> true,
				'help'		=> _("New groups created in this directory will be local and not saved to the LDAP directory. Groups synchronised from the remote directory will be read-only."),
			),
			array(
				'name'		=> $typeauth.'-commonnameattr',
				'title'		=> _("Common Name attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['commonnameattr'],
				'opts'		=> array(
					'value' => isset($config['commonnameattr']) ? $config['commonnameattr'] : $defaults['commonnameattr'],
				),
				'help'		=> _("The attribute field to use when loading the object's common name."),
			),
			array(
				'name'		=> $typeauth.'-descriptionattr',
				'title'		=> _("Description attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['descriptionattr'],
				'opts'		=> array(
					'value' => isset($config['descriptionattr']) ? $config['descriptionattr'] : $defaults['descriptionattr'],
				),
				'help'		=> _("The attribute field to use when loading the object description."),
			),
			array(
				'name'		=> $typeauth.'-externalidattr',
				'title'		=> _("Unique identifier attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['externalidattr'],
				'opts'		=> array(
					'value' => isset($config['externalidattr']) ? $config['externalidattr'] : $defaults['externalidattr'],
				),
				'help'		=> _("The attribute field to use for tracking user identity across object renames."),
			),
			array( 'type' => 'fieldset_end' ),


			array('type' => 'fieldset_init', 'legend' => _("User configuration")),
			array(
				'name'		=> $typeauth.'-userdn',
				'title'		=> _("User DN"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['userdn'],
				'opts'		=> array(
					'value' => isset($config['userdn']) ? $config['userdn'] : $defaults['userdn'],
				),
				'help'		=> _("This value is used in addition to the base DN when searching and loading users. An example is ou=Users. If no value is supplied, the subtree search will start from the base DN."),
			),
			array(
				'name'		=> $typeauth.'-userobjectclass',
				'title'		=> _("User object class"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['userobjectclass'],
				'opts'		=> array(
					'value' => isset($config['userobjectclass']) ? $config['userobjectclass'] : $defaults['userobjectclass'],
				),
				'help'		=> _("The LDAP user object class type to use when loading users."),
			),
			array(
				'name'		=> $typeauth.'-userobjectfilter',
				'title'		=> _("User object filter"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['userobjectfilter'],
				'opts'		=> array(
					'value' => isset($config['userobjectfilter']) ? $config['userobjectfilter'] : $defaults['userobjectfilter'],
				),
				'help'		=> _("The filter to use when searching user objects."),
			),
			array(
				'name'		=> $typeauth.'-usernameattr',
				'title'		=> _("User name attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['usernameattr'],
				'opts'		=> array(
					'value' => isset($config['usernameattr']) ? $config['usernameattr'] : $defaults['usernameattr'],
				),
				'help'		=> _("The attribute field to use on the user object (eg. uid)"),
			),
			array(
				'name'		=> $typeauth.'-userfirstnameattr',
				'title'		=> _("User first name attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['userfirstnameattr'],
				'opts'		=> array(
					'value' => isset($config['userfirstnameattr']) ? $config['userfirstnameattr'] : $defaults['userfirstnameattr'],
				),
				'help'		=> _("The attribute field to use when loading the user first name."),
			),
			array(
				'name'		=> $typeauth.'-userlastnameattr',
				'title'		=> _("User last name attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['userlastnameattr'],
				'opts'		=> array(
					'value' => isset($config['userlastnameattr']) ? $config['userlastnameattr'] : $defaults['userlastnameattr'],
				),
				'help'		=> _("The attribute field to use when loading the user last name."),
			),
			array(
				'name'		=> $typeauth.'-userdisplaynameattr',
				'title'		=> _("User display name attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['userdisplaynameattr'],
				'opts'		=> array(
					'value' => isset($config['userdisplaynameattr']) ? $config['userdisplaynameattr'] : $defaults['userdisplaynameattr'],
				),
				'help'		=> _("The attribute field to use when loading the user full name."),
			),
			array(
				'name'		=> $typeauth.'-usergroupmemberattr',
				'title'		=> _("User group attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['usergroupmemberattr'],
				'opts'		=> array(
					'value' => isset($config['usergroupmemberattr']) ? $config['usergroupmemberattr'] : $defaults['usergroupmemberattr'],
				),
				'help'		=> _("The attribute field to use when loading the users groups."),
			),
			array(
				'name'		=> $typeauth.'-usermailattr',
				'title'		=> _("User email attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['usermailattr'],
				'opts'		=> array(
					'value' => isset($config['usermailattr']) ? $config['usermailattr'] : $defaults['usermailattr'],
				),
				'help'		=> _("The attribute field to use when loading the user email."),
			),
			array(
				'name'		=> $typeauth.'-usertitleattr',
				'title'		=> _("User Title attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['usertitleattr'],
				'opts'		=> array(
					'value' => isset($config['usertitleattr']) ? $config['usertitleattr'] : $defaults['usertitleattr'],
				),
				'help'		=> _("The attribute field to use when loading the user title."),
			),
			array(
				'name'		=> $typeauth.'-usercompanyattr',
				'title'		=> _("User Company attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['usercompanyattr'],
				'opts'		=> array(
					'value' => isset($config['usercompanyattr']) ? $config['usercompanyattr'] : $defaults['usercompanyattr'],
				),
				'help'		=> _("The attribute field to use when loading the user company."),
			),
			array(
				'name'		=> $typeauth.'-userdepartmentattr',
				'title'		=> _("User Department attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['userdepartmentattr'],
				'opts'		=> array(
					'value' => isset($config['userdepartmentattr']) ? $config['userdepartmentattr'] : $defaults['userdepartmentattr'],
				),
				'help'		=> _("The attribute field to use when loading the user department."),
			),
			array(
				'name'		=> $typeauth.'-userhomephoneattr',
				'title'		=> _("User Home Phone attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['userhomephoneattr'],
				'opts'		=> array(
					'value' => isset($config['userhomephoneattr']) ? $config['userhomephoneattr'] : $defaults['userhomephoneattr'],
				),
				'help'		=> _("The attribute field to use when loading the user home phone."),
			),
			array(
				'name'		=> $typeauth.'-userworkphoneattr',
				'title'		=> _("User Work Phone attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['userworkphoneattr'],
				'opts'		=> array(
					'value' => isset($config['userworkphoneattr']) ? $config['userworkphoneattr'] : $defaults['userworkphoneattr'],
				),
				'help'		=> _("The attribute field to use when loading the user work phone."),
			),
			array(
				'name'		=> $typeauth.'-usercellphoneattr',
				'title'		=> _("User Cell Phone attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['usercellphoneattr'],
				'opts'		=> array(
					'value' => isset($config['usercellphoneattr']) ? $config['usercellphoneattr'] : $defaults['usercellphoneattr'],
				),
				'help'		=> _("The attribute field to use when loading the user cell phone."),
			),
			array(
				'name'		=> $typeauth.'-userfaxphoneattr',
				'title'		=> _("User Fax attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['userfaxphoneattr'],
				'opts'		=> array(
					'value' => isset($config['userfaxphoneattr']) ? $config['userfaxphoneattr'] : $defaults['userfaxphoneattr'],
				),
				'help'		=> _("The attribute field to use when loading the user fax."),
			),
			array(
				'name'		=> $typeauth.'-la',
				'title'		=> _("User extension Link attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['la'],
				'opts'		=> array(
					'value' => isset($config['la']) ? $config['la'] : $defaults['la'],
				),
				'help'		=> _("If this is set then User Manager will use the defined attribute of the user from the OpenLDAP server as the extension link. NOTE: If this field is set it will overwrite any manually linked extensions where this attribute extists!! (Try lowercase if it is not working.)"),
			),
			array( 'type' => 'fieldset_end' ),


			array('type' => 'fieldset_init', 'legend' => _("Group configuration")),
			array(
				'name'		=> $typeauth.'-groupdnaddition',
				'title'		=> _("Group DN"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['groupdnaddition'],
				'opts'		=> array(
					'value' => isset($config['groupdnaddition']) ? $config['groupdnaddition'] : $defaults['groupdnaddition'],
				),
				'help'		=> _("This value is used in addition to the base DN when searching and loading groups. An example is ou=Groups. If no value is supplied, the subtree search will start from the base DN."),
			),
			array(
				'name'		=> $typeauth.'-groupobjectclass',
				'title'		=> _("Group object class"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['groupobjectclass'],
				'opts'		=> array(
					'value' => isset($config['groupobjectclass']) ? $config['groupobjectclass'] : $defaults['groupobjectclass'],
				),
				'help'		=> _("The LDAP user object class type to use when loading groups."),
			),
			array(
				'name'		=> $typeauth.'-groupobjectfilter',
				'title'		=> _("Group object filter"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['groupobjectfilter'],
				'opts'		=> array(
					'value' => isset($config['groupobjectfilter']) ? $config['groupobjectfilter'] : $defaults['groupobjectfilter'],
				),
				'help'		=> _("The filter to use when searching group objects."),
			),
			array(
				'name'		=> $typeauth.'-groupmemberattr',
				'title'		=> _("Group members attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['groupmemberattr'],
				'opts'		=> array(
					'value' => isset($config['groupmemberattr']) ? $config['groupmemberattr'] : $defaults['groupmemberattr'],
				),
				'help'		=> _("The attribute field to use when loading the group members."),
			),
			array(
				'name'		=> $typeauth.'-groupmemberidentifierattr',
				'title'		=> _("Group Member Identifier Attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> true,
				'default'	=> $defaults['groupmemberidentifierattr'],
				'opts'		=> array(
					'value' => isset($config['groupmemberidentifierattr']) ? $config['groupmemberidentifierattr'] : $defaults['groupmemberidentifierattr'],
				),
				'help'		=> _("The attribute field that is used to find the users who are members of a group. "),
			),
			array( 'type' => 'fieldset_end' ),

		);
		return array(
			'auth' => $typeauth,
			'data' => $form_data,
		);
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
		$config = array('authtype' => self::getShortName());
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
		$openldap2->connect();
		return $this->ldap;
	}

	/**
	 * Validates if the protocol that has been specified is valid
	 * @return integer version specified or if it was not correct the default value has been defined
	 */
	private function validateVerProtoLDAP($version) {
		if (! is_numeric($version) || ! in_array($version, $this->allowLdapProtocolVersion) )
		{
			$version = self::$serverDefaults['version'];
		}
		return intval($version);
	}

	/**
	 * Connect to the LDAP server
	 */
	public function connect($reconnect = false) {
		if($reconnect || !$this->ldap) {
			if(!class_exists('App\Schemas\Openldap2',false)) {
				include __DIR__."/openldap2/Openldap2Schema.class.php";
			}
			$mySchema = new \App\Schemas\Openldap2($this->config);
			$config = [
				// Mandatory Configuration Options
				'hosts'				=> preg_split("/[ ,]/", $this->config['host']),
				'base_dn'    		=> $this->config['basedn'],
				'username'      	=> $this->config['username'],
				'password'      	=> $this->config['password'],

				// Optional Configuration Options
				'schema'			=> \App\Schemas\Openldap2::class,
				'port'            	=> $this->config['port'],
				'follow_referrals'  => false,
				'use_ssl'           => ($this->config['connection'] == 'ssl'),
				'use_tls'           => ($this->config['connection'] == 'tls'),
				'timeout'           => $this->timeout,
				'version'          	=> $this->validateVerProtoLDAP($this->config['version']),
			];
			$this->provider = new \Adldap\Connections\Provider($config, $connection = null);
			$this->provider->setSchema($mySchema);
			$ad = new Adldap(array("default" => $config));
			$ad->addProvider($this->provider, 'default');
			try {
				$this->ldap = $ad->connect();
			} catch (BindException $e) {
				throw new \Exception("Unable to Connect to host! Reason: ".$e->getMessage());
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
		$res = $this->provider->auth()->attempt($this->config['usernameattr']."=".$username.",".$userdn, $password);

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
			if(empty($user->getAttribute($this->config['userprimarygroupattr'],0))) {
				$this->out("\tUser ".$u['username']." missing ".$this->config['userprimarygroupattr']." attribute. Cant determine primary group");
				continue;
			}
			$primarygroup = $user->getAttribute($this->config['userprimarygroupattr'],0);
			foreach($groups as $gsid => $group) {
				if(empty($group['cache']->getAttribute($this->config['groupgidnumberattr'],0))) {
					$this->out("\tGroup ".$group['groupname']." missing ".$this->config['groupgidnumberattr']." attribute. Cant determine primary group");
					continue;
				} elseif($primarygroup == $group['cache']->getAttribute($this->config['groupgidnumberattr'],0)) {
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
		$groupdn = !empty($this->config['groupdnaddition']) ? $this->config['groupdnaddition'].",".$this->config['basedn'] : $this->config['basedn'];
		$ldapuri = $this->buildldapuri($this->config['connection'], $this->config['host'], $this->config['port']);
		$this->out("\t".'ldapsearch -w '.$this->config['password'].' -H "'.$ldapuri.'" -D "'.$this->config['username'].'" -b "'.$groupdn.'" -s sub "'.$this->config['groupobjectfilter'].'"');
		$this->out("\tRetrieving all groups...");

		$search = $this->ldap->search();
		$paginator = $search->in($groupdn)->rawFilter("(&".$this->config['groupobjectfilter']."(objectclass=".$this->config['groupobjectclass']."))")->select(["*",$this->config['groupgidnumberattr'],$this->config['descriptionattr'],$this->config['groupnameattr'], $this->config['externalidattr'], $this->config['groupmemberattr']])->paginate($this->limit, 1);
		$results = $paginator->getResults();

		if(count($results) == 0) {
			$this->out("\tNo groups found! Perhaps your query is wrong?");
			return;
		}
		$this->out("\tGot ".count($results). " groups");

		foreach($results as $result) {
			$sid = $result->getObjectGuid();
			if(empty($sid)) {
				$this->out("\t\tERROR Group is missing ".$this->config['externalidattr']." attribute! Cant continue!!");
				continue;
			}
			$groupname = $result->getCommonName();
			if(empty($groupname)) {
				$this->out("\t\tGroupname is blank! Skipping unknown group");
				continue;
			}
			$this->gcache[$sid] = $result;
			$um = $this->linkGroup($groupname, $sid);
			$description = !is_null($result->getDescription()) ? $result->getDescription() : '';
			$members = array();
			$this->out("\tWorking on ".$groupname);

			// The list of users is obtained directly from the class array, since the getMembers function does 
			// not return the users if it is not used with the command "$provider->search()->groups()".
			// Open issues: https://github.com/Adldap2/Adldap2/issues/794
			if ($this->config['groupmemberidentifierattr'] == "uid") {
				$getMembers = $result[$this->config['groupmemberattr']];
			} else {
				$getMembers = $result->getMembers();
			}
			if ($getMembers == null) {
				$getMembers = array();
			}
			foreach($getMembers as $member) {
				if ($member instanceof \Adldap\Models\Group) {
					$m = $this->getUserByUsername($member->getAccountName());
				} else {
					$m = $this->getUserByUsername($member);
				}
				if(!empty($m)) {
					$this->out("\t\t\tAdding ".$m['username']." to group");
					$members[] = $m['id'];
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
			if($group['local']) {
				$this->out("\tSkipping local group '".$group['groupname']."'");
				continue;
			}
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
		$ldapuri = $this->buildldapuri($this->config['connection'], $this->config['host'], $this->config['port']);
		$this->out("\t".'ldapsearch -w '.$this->config['password'].' -H "'.$ldapuri.'" -D "'.$this->config['username'].'" -b "'.$userdn.'" -s sub "'.$this->config['userobjectfilter'].'" "'.$this->config['externalidattr'].'=*" '.$this->config['externalidattr']);
		$this->out("\tRetrieving all users...");

		$search = $this->ldap->search();
		$paginator = $search->in($userdn)->rawFilter("(&".$this->config['userobjectfilter']."(objectclass=".$this->config['userobjectclass']."))")->select(['*',$this->config['externalidattr']])->paginate($this->limit, 1);
		$results = $paginator->getResults();

		if(count($results) == 0) {
			$this->out("\tNo users found! Perhaps your query is wrong?");
			return;
		}

		$this->out("\tGot ".count($results). " users");
		foreach($results as $result) {
			$sid = $result->getObjectGuid();
			if(empty($sid)) {
				$this->out("\t\tERROR User is missing ".$this->config['externalidattr']." attribute! Cant continue!!");
				continue;
			}
			$username = $result->getAccountName();
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
				$data = array(
					"description" => !is_null($result->getDescription()) ? $result->getDescription() : '',
					"primary_group" => !is_null($result->getPrimaryGroupId()) ? $result->getPrimaryGroupId() : '',
					"fname" => !is_null($result->getFirstName()) ? $result->getFirstName() : '',
					"lname" => !is_null($result->getLastName()) ? $result->getLastName() : '',
					"displayname" => !is_null($result->getDisplayName()) ? $result->getDisplayName() : '',
					"company" => !empty($this->config['usercompanyattr']) && !is_null($result->getAttribute($this->config['usercompanyattr'],0)) ? $result->getAttribute($this->config['usercompanyattr'],0) : '',
					"department" => !empty($this->config['userdepartmentattr']) && !is_null($result->getAttribute($this->config['userdepartmentattr'],0)) ? $result->getAttribute($this->config['userdepartmentattr'],0) : '',
					"title" => !is_null($result->getTitle()) ? $result->getTitle() : '',
					"email" => !is_null($result->getEmail()) ? $result->getEmail() : '',
					"cell" => !empty($this->config['usercellphoneattr']) && !is_null($result->getAttribute($this->config['usercellphoneattr'],0)) ? $result->getAttribute($this->config['usercellphoneattr'],0) : '',
					"work" => !empty($this->config['userworkphoneattr']) && !is_null($result->getAttribute($this->config['userworkphoneattr'],0)) ? $result->getAttribute($this->config['userworkphoneattr'],0) : '',
					"fax" => !empty($this->config['userfaxphoneattr']) && !is_null($result->getAttribute($this->config['userfaxphoneattr'],0)) ? $result->getAttribute($this->config['userfaxphoneattr'],0) : '',
					"home" => !empty($this->config['userhomephoneattr']) && !is_null($result->getAttribute($this->config['userhomephoneattr'],0)) ? $result->getAttribute($this->config['userhomephoneattr'],0) : '',
				);
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
				} elseif(!empty($this->config['la']) && empty($result->getAttribute($this->config['la'],0))) {
					$data["default_extension"] = 'none';
				}
				$this->updateUserData($um['id'], $data);
				if($um['new']) {
					$this->userHooks['add'][$um['id']] = array($um['id'], $username, $data['description'], null, false, $data);
				} else {
					$this->userHooks['update'][$um['id']] = array($um['id'], $um['prevUsername'], $username, $data['description'], null, $data);
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

