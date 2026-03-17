<?php
// vim: set ai ts=4 sw=4 ft=php:
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2026
//
namespace FreePBX\modules\Userman\Auth;

class Scim extends Freepbx {
	private static $operationalDefaults = array(
		'createextensions' => '',
		'deleteextensiondeprovision' => 0,
		'localgroups' => 0,
		'commonnameattr' => 'displayName',
		'descriptionattr' => 'displayName',
		'externalidattr' => 'externalId'
	);

	private static $userDefaults = array(
		'usernameattr' => 'userName',
		'userfirstnameattr' => 'name.givenName',
		'userlastnameattr' => 'name.familyName',
		'userdisplaynameattr' => 'displayName',
		'usertitleattr' => 'title',
		'usercompanyattr' => 'organization',
		'userdepartmentattr' => 'department',
		'usercellphoneattr' => "phoneNumbers['mobile'].value",
		'userworkphoneattr' => "phoneNumbers['work'].value",
		'userhomephoneattr' => "phoneNumbers['home'].value",
		'userfaxphoneattr' => "phoneNumbers['fax'].value",
		'usermailattr' => "emails['work'].value",
		'usergroupmemberattr' => 'group',
		'la' => 'extension.extension'
	);

	private static $groupDefaults = array(
		'groupnameattr' => 'displayName',
		'groupdescriptionattr' => 'description',
		'groupmemberattr' => 'members',
		'groupgidnumberattr' => 'id'
	);
	/**
	 * Get information about this driver
	 * @param  Object $userman The userman Object
	 * @param  Object $freepbx The freepbx Object
	 * @return array
	 */
	public static function getInfo($userman, $freepbx) {
		if (!\FreePBX::Modules()->checkStatus('pbxsaml')) {
			return array();
		}
		if (!\FreePBX::Pbxsaml()->isLicensed()) {
			return array();
		}
		if (class_exists('\FreePBX\modules\Pbxsaml\licenseCheck')
			&& method_exists('\FreePBX\modules\Pbxsaml\licenseCheck', 'isExpired')
			&& \FreePBX\modules\Pbxsaml\licenseCheck::isExpired()) {
			return array();
		}
		return array(
			"name" => _("SCIM User Provisioning")
		);
	}

	/**
	 * Get configuration for this driver
	 * @param  Object $userman The userman Object
	 * @param  Object $freepbx The freepbx Object
	 * @param  array  $config  Existing config
	 * @return array
	 */
	public static function getConfig($userman, $freepbx, $config) {
		$typeauth = self::getShortName();
		$defaults = array_merge(self::$operationalDefaults, self::$userDefaults, self::$groupDefaults);
		$techs = $freepbx->Core->getAllDriversInfo();
		array_unshift($techs, array('rawName' => '', 'shortName' => _("Don't Create")));
		$form_data = array(
			array(
				'name'		=> $typeauth.'-base-url',
				'title'		=> _("SCIM Base URL"),
				'type' 		=> 'text',
				'index'		=> true,
				'opts'		=> array(
					'value' => isset($config['base_url']) ? $config['base_url'] : '',
					'readonly' => 'readonly',
				),
				'help'		=> _("SCIM provisioning base URL"),
			),
			array(
				'name'		=> $typeauth.'-token',
				'title'		=> _("SCIM Token"),
				'type' 		=> 'raw',
				'value'		=> sprintf(
					'<textarea id="%1$s-token" class="form-control" name="%1$s-token" rows="2" readonly></textarea>
					<div class="scim-token-actions" style="margin-top: 8px;">
						<button class="btn btn-warning" type="button" id="%1$s-rotate-token"><i class="fa fa-refresh"></i> %2$s</button>
					</div>
					<div id="%1$s-token-message" class="alert alert-info hidden" style="margin-top: 10px;"></div>',
					$typeauth,
					_("Refresh Token")
				),
				'help'		=> _("Use this token for SCIM provisioning"),
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
				'name' 		=> $typeauth.'-deleteextensiondeprovision',
				'title'		=> _('Delete extension after deprovisioning'),
				'type' 		=> 'radioset_yn',
				'value' 	=> isset($config['deleteextensiondeprovision']) ? $config['deleteextensiondeprovision'] : $defaults['deleteextensiondeprovision'],
				'values'	=> array(
					'y'	=> '1',
					'n'	=> '0',
				),
				'index'		=> true,
				'help'		=> _("When set to Yes, the linked extension will be deleted from the PBX when a user is removed from this directory (deprovisioned)."),
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
				'help'		=> _("New groups created in this directory will be local and not saved to the SCIM provider. Groups synchronised from the remote directory will be read-only."),
			),
			array(
				'name'		=> $typeauth.'-commonnameattr',
				'title'		=> _("Common Name attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['commonnameattr'],
				'opts'		=> array(
					'value' => isset($config['commonnameattr']) ? $config['commonnameattr'] : $defaults['commonnameattr'],
				),
				'help'		=> _("SCIM attribute path for common name (Okta default: displayName)."),
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
				'help'		=> _("SCIM attribute path for description (Okta default: title)."),
			),
			array(
				'name'		=> $typeauth.'-externalidattr',
				'title'		=> _("Unique identifier attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['externalidattr'],
				'opts'		=> array(
					'value' => isset($config['externalidattr']) ? $config['externalidattr'] : $defaults['externalidattr'],
				),
				'help'		=> _("SCIM attribute path for unique identifier (Okta default: externalId)."),
			),
			array('type' => 'fieldset_end'),
			array('type' => 'fieldset_init', 'legend' => _("User configuration")),
			array(
				'name'		=> $typeauth.'-usernameattr',
				'title'		=> _("User name attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['usernameattr'],
				'opts'		=> array(
					'value' => isset($config['usernameattr']) ? $config['usernameattr'] : $defaults['usernameattr'],
				),
				'help'		=> _("SCIM attribute path for username (Okta default: userName)."),
			),
			array(
				'name'		=> $typeauth.'-userfirstnameattr',
				'title'		=> _("User First Name attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['userfirstnameattr'],
				'opts'		=> array(
					'value' => isset($config['userfirstnameattr']) ? $config['userfirstnameattr'] : $defaults['userfirstnameattr'],
				),
				'help'		=> _("SCIM attribute path for first name (Okta default: name.givenName)."),
			),
			array(
				'name'		=> $typeauth.'-userlastnameattr',
				'title'		=> _("User Last Name attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['userlastnameattr'],
				'opts'		=> array(
					'value' => isset($config['userlastnameattr']) ? $config['userlastnameattr'] : $defaults['userlastnameattr'],
				),
				'help'		=> _("SCIM attribute path for last name (Okta default: name.familyName)."),
			),
			array(
				'name'		=> $typeauth.'-userdisplaynameattr',
				'title'		=> _("User Display Name attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['userdisplaynameattr'],
				'opts'		=> array(
					'value' => isset($config['userdisplaynameattr']) ? $config['userdisplaynameattr'] : $defaults['userdisplaynameattr'],
				),
				'help'		=> _("SCIM attribute path for display name (Okta default: displayName)."),
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
				'help'		=> _("SCIM attribute path for title (Okta default: title)."),
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
				'help'		=> _("SCIM attribute path for company (Okta default: organization)."),
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
				'help'		=> _("SCIM attribute path for department (Okta default: department)."),
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
				'help'		=> _("SCIM attribute path for work phone (Okta default: phoneNumbers[type eq \"work\"].value)."),
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
				'help'		=> _("SCIM attribute path for mobile phone (Okta default: phoneNumbers[type eq \"mobile\"].value)."),
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
				'help'		=> _("SCIM attribute path for home phone (Okta default: phoneNumbers[type eq \"home\"].value)."),
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
				'help'		=> _("SCIM attribute path for fax (Okta default: phoneNumbers[type eq \"fax\"].value)."),
			),
			array(
				'name'		=> $typeauth.'-usermailattr',
				'title'		=> _("User Email attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['usermailattr'],
				'opts'		=> array(
					'value' => isset($config['usermailattr']) ? $config['usermailattr'] : $defaults['usermailattr'],
				),
				'help'		=> _("SCIM attribute path for email (Okta default: emails[type eq \"work\"].value)."),
			),
			array(
				'name'		=> $typeauth.'-usergroupmemberattr',
				'title'		=> _("User Group attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['usergroupmemberattr'],
				'opts'		=> array(
					'value' => isset($config['usergroupmemberattr']) ? $config['usergroupmemberattr'] : $defaults['usergroupmemberattr'],
				),
				'help'		=> _("SCIM attribute path for groups (Okta default: groups)."),
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
				'help'		=> _("SCIM attribute path for extension (Okta default: pbxsaml extension schema)."),
			),
			array('type' => 'fieldset_end'),
			array('type' => 'fieldset_init', 'legend' => _("Group configuration")),
			array(
				'name'		=> $typeauth.'-groupnameattr',
				'title'		=> _("Group Name attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['groupnameattr'],
				'opts'		=> array(
					'value' => isset($config['groupnameattr']) ? $config['groupnameattr'] : $defaults['groupnameattr'],
				),
				'help'		=> _("SCIM attribute path for group name (Okta default: displayName)."),
			),
			array(
				'name'		=> $typeauth.'-groupdescriptionattr',
				'title'		=> _("Group Description attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['groupdescriptionattr'],
				'opts'		=> array(
					'value' => isset($config['groupdescriptionattr']) ? $config['groupdescriptionattr'] : $defaults['groupdescriptionattr'],
				),
				'help'		=> _("SCIM attribute path for group description (Okta default: description)."),
			),
			array(
				'name'		=> $typeauth.'-groupmemberattr',
				'title'		=> _("Group Member attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['groupmemberattr'],
				'opts'		=> array(
					'value' => isset($config['groupmemberattr']) ? $config['groupmemberattr'] : $defaults['groupmemberattr'],
				),
				'help'		=> _("SCIM attribute path for group members (Okta default: members)."),
			),
			array(
				'name'		=> $typeauth.'-groupgidnumberattr',
				'title'		=> _("Group ID attribute"),
				'type' 		=> 'text',
				'index'		=> true,
				'required'	=> false,
				'default'	=> $defaults['groupgidnumberattr'],
				'opts'		=> array(
					'value' => isset($config['groupgidnumberattr']) ? $config['groupgidnumberattr'] : $defaults['groupgidnumberattr'],
				),
				'help'		=> _("SCIM attribute path for group ID (Okta default: id)."),
			),
			array('type' => 'fieldset_end'),
		);
		return array(
			'auth' => $typeauth,
			'data' => $form_data,
		);
	}

	/**
	 * Save Configuration from auth config page
	 * @param  Object $userman The userman Object
	 * @param  Object $freepbx The freepbx Object
	 * @return array
	 */
	public static function saveConfig($userman, $freepbx) {
		$typeauth = self::getShortName();
		$token = isset($_REQUEST[$typeauth.'-token']) ? $_REQUEST[$typeauth.'-token'] : '';
		if (empty($token)) {
			$token = bin2hex(random_bytes(32));
		}
		$defaults = array_merge(self::$operationalDefaults, self::$userDefaults, self::$groupDefaults);
		$config = array(
			'authtype' => $typeauth,
			'base_url' => isset($_REQUEST[$typeauth.'-base-url']) ? $_REQUEST[$typeauth.'-base-url'] : '',
			'token' => $token
		);
		foreach ($defaults as $key => $value) {
			$reqKey = $typeauth.'-'.$key;
			$config[$key] = isset($_REQUEST[$reqKey]) ? $_REQUEST[$reqKey] : $value;
		}
		return $config;
	}

	/**
	 * Return an array of permissions for this adaptor
	 */
	public function getPermissions() {
		return array(
			"addGroup" => (!empty($this->config['localgroups']) ? true : false),
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
}
