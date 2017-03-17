<?php
//Change login type to usermanager if installed.
if(FreePBX::Config()->get('AUTHTYPE') == "database") {
	FreePBX::Config()->update('AUTHTYPE','usermanager');
}

$set = array();
$set['value'] = '';
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = 'userman';
$set['category'] = 'User Management Module';
$set['emptyok'] = 1;
$set['name'] = 'Email "From:" Address';
$set['description'] = 'The From: field for emails when using the user management email feature.';
$set['type'] = CONF_TYPE_TEXT;
FreePBX::Config()->define_conf_setting('AMPUSERMANEMAILFROM',$set,true);

//Quick check to see if we are previously installed
//this lets us know if we need to create a default group
$sql = "SELECT * FROM userman_groups WHERE auth = 'freepbx'";
$sth = FreePBX::Database()->prepare($sql);
try {
	$sth->execute();
	$grps = $sth->fetchAll();
} catch(\Exception $e) {
	$grps = array();
}

if (empty($grps)) {
	$users = array();
	$sql = "SELECT * FROM userman_users WHERE auth = 'freepbx'";
	$sth = FreePBX::Database()->prepare($sql);
	try {
		$sth->execute();
		$us = $sth->fetchAll(PDO::FETCH_ASSOC);
		$users = array();
		foreach($us as $u) {
			if(empty($u['id'])) {
				continue;
			}
			$users[] = $u['id'];
		}
	} catch(\Exception $e) {}

	$sql = "INSERT INTO userman_groups (`groupname`, `description`, `users`) VALUES (?, ?, ?)";
	$sth = FreePBX::Database()->prepare($sql);
	$sth->execute(array(_("All Users"),_("This group was created on install and is automatically assigned to new users. This can be disabled in User Manager Settings"),json_encode($users)));
	$id = FreePBX::Database()->lastInsertId();
	$config = array(
		"default-groups" => array($id)
	);
	FreePBX::Userman()->setConfig("authFREEPBXSettings", $config);
	//Default Group Settings
	FreePBX::Userman()->setModuleSettingByGID($id,'contactmanager','show', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'contactmanager','groups',array($id));
	FreePBX::Userman()->setModuleSettingByGID($id,'fax','enabled',true);
	FreePBX::Userman()->setModuleSettingByGID($id,'fax','attachformat',"pdf");
	FreePBX::Userman()->setModuleSettingByGID($id,'faxpro','localstore',"true");
	FreePBX::Userman()->setModuleSettingByGID($id,'restapi','restapi_token_status', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'restapi','restapi_users',array("self"));
	FreePBX::Userman()->setModuleSettingByGID($id,'restapi','restapi_modules',array("*"));
	FreePBX::Userman()->setModuleSettingByGID($id,'restapi','restapi_rate',"1000");
	FreePBX::Userman()->setModuleSettingByGID($id,'xmpp','enable', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Global','allowLogin',true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Global','originate', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Settings','assigned', array("self"));
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Cdr','enable', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Cdr','assigned', array("self"));
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Cdr','download', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Cdr','playback', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Cel','enable', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Cel','assigned', array("self"));
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Cel','download', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Cel','playback', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Presencestate','enabled',true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Voicemail','enable', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Voicemail','assigned', array("self"));
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Voicemail','download', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Voicemail','playback', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Voicemail','settings', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Voicemail','greetings', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Voicemail','vmxlocater', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Conferencespro','enable', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Endpoint','enable', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Endpoint','assigned', array("self"));
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Conferencespro','assigned', array("linked"));
	FreePBX::Userman()->setModuleSettingByGID($id,'conferencespro','link', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'conferencespro','ivr', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'ucp|Sysadmin','vpn_enable', true);
	$tfsettings = array(
		"login",
		"menuover",
		"conference_enable",
		"queue_enable",
		"timecondition_enable",
		"callflow_enable",
		"contact_enable",
		"voicemail_enable",
		"presence_enable",
		"parking_enable",
		"fmfm_enable",
		"dnd_enable",
		"cf_enable",
		"qa_enable",
		"lilo_enable"
	);
	foreach($tfsettings as $setting) {
		FreePBX::Userman()->setModuleSettingByGID($id,'restapps',$setting, true);
	}
	FreePBX::Userman()->setModuleSettingByGID($id,'restapps','conferences',array('linked'));
	$asettings = array(
		"queues",
		"timeconditions",
		"callflows",
		"contacts"
	);
	foreach($asettings as $setting) {
		FreePBX::Userman()->setModuleSettingByGID($id,'restapps',$setting,array('*'));
	}
	FreePBX::Userman()->setModuleSettingByGID($id,"contactmanager","showingroups",array("*"));
	FreePBX::Userman()->setModuleSettingByGID($id,'contactmanager','groups',array("*"));
	FreePBX::Userman()->setModuleSettingByGID($id,'sysadmin','vpn_link', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'zulu','enable', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'zulu','enable_fax', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'zulu','enable_sms', true);
	FreePBX::Userman()->setModuleSettingByGID($id,'zulu','enable_phone', true);
	FreePBX::Userman()->setConfig("autoGroup", $id);
}
