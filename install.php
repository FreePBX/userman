<?php
global $db;
//Change login type to usermanager if installed.
if(FreePBX::Config()->get('AUTHTYPE') == "database") {
	FreePBX::Config()->update('AUTHTYPE','usermanager');
}

$sqls = array();
if (!$db->getAll('SHOW TABLES LIKE "userman_users"') && $db->getAll('SHOW TABLES LIKE "freepbx_users"')) {
	$sqls[] = "RENAME TABLE freepbx_users TO userman_users";
}

if (!$db->getAll('SHOW TABLES LIKE "userman_users_settings"') && $db->getAll('SHOW TABLES LIKE "freepbx_users_settings"')) {
	$sqls[] = "RENAME TABLE freepbx_users_settings TO userman_users_settings";
}
foreach($sqls as $sql) {
	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_freepbx($result->getDebugInfo());
	}
}

if(!empty($sqls)) {
	try {
		$sth = FreePBX::Database()->prepare('SELECT * FROM userman_users');
		$sth->execute();
	} catch(\Exception $e) {
		out(_("Database rename not completed"));
		return false;
	}
	try {
		$sth = FreePBX::Database()->prepare('SELECT * FROM userman_users_settings');
		$sth->execute();
	} catch(\Exception $e) {
		out(_("Database rename not completed"));
		return false;
	}
}

$sqls = array();
$sqls[] = "CREATE TABLE IF NOT EXISTS `userman_users` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`auth` varchar(150) DEFAULT 'freepbx',
	`authid` varchar(255) DEFAULT NULL,
	`username` varchar(150) DEFAULT NULL,
	`description` varchar(255) DEFAULT NULL,
	`password` varchar(255) DEFAULT NULL,
	`default_extension` varchar(45) NOT NULL DEFAULT 'none',
	`primary_group` int(11) DEFAULT NULL,
	`permissions` BLOB,
	`fname` varchar(100) DEFAULT NULL,
	`lname` varchar(100) DEFAULT NULL,
	`displayname` varchar(200) DEFAULT NULL,
	`title` varchar(100) DEFAULT NULL,
	`company` varchar(100) DEFAULT NULL,
	`department` varchar(100) DEFAULT NULL,
	`language` varchar(100) DEFAULT NULL,
	`timezone` varchar(100) DEFAULT NULL,
	`dateformat` varchar(100) DEFAULT NULL,
	`timeformat` varchar(100) DEFAULT NULL,
	`datetimeformat` varchar(100) DEFAULT NULL,
	`email` text DEFAULT NULL,
	`cell` varchar(100) DEFAULT NULL,
	`work` varchar(100) DEFAULT NULL,
	`home` varchar(100) DEFAULT NULL,
	`fax` varchar(100) DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `username_UNIQUE` (`username`,`auth`)
)";
$sqls[] = "CREATE TABLE IF NOT EXISTS `userman_users_settings` (
	`uid` int(11) NOT NULL,
	`module` char(65) NOT NULL,
	`key` char(190) NOT NULL,
	`val` longblob NOT NULL,
	`type` char(16) DEFAULT NULL,
	UNIQUE KEY `index4` (`uid`,`module`,`key`),
	KEY `index2` (`uid`,`key`),
	KEY `index6` (`module`,`uid`)
)";
$sqls[] = "CREATE TABLE IF NOT EXISTS `userman_groups` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`auth` varchar(150) DEFAULT 'freepbx',
	`authid` varchar(255) DEFAULT NULL,
	`groupname` varchar(150) DEFAULT NULL,
	`description` varchar(255) DEFAULT NULL,
	`language` varchar(100) DEFAULT NULL,
	`timezone` varchar(100) DEFAULT NULL,
	`dateformat` varchar(100) DEFAULT NULL,
	`timeformat` varchar(100) DEFAULT NULL,
	`datetimeformat` varchar(100) DEFAULT NULL,
	`priority` int(11) NOT NULL DEFAULT 5,
	`users` BLOB,
	`permissions` BLOB,
	PRIMARY KEY (`id`),
	UNIQUE KEY `groupname_UNIQUE` (`groupname`,`auth`)
)";
$sqls[] = "CREATE TABLE IF NOT EXISTS `userman_groups_settings` (
	`gid` int(11) NOT NULL,
	`module` char(65) NOT NULL,
	`key` char(190) NOT NULL,
	`val` longblob NOT NULL,
	`type` char(16) DEFAULT NULL,
	UNIQUE KEY `index4` (`gid`,`module`,`key`),
	KEY `index2` (`gid`,`key`),
	KEY `index6` (`module`,`gid`)
)";
foreach($sqls as $sql) {
	$result = $db->query($sql);
	if (\DB::IsError($result)) {
		die_freepbx($result->getDebugInfo());
	}
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_users` WHERE FIELD = "auth"')) {
	out("Adding default extension column");
		$sql = "ALTER TABLE `userman_users` ADD COLUMN `auth` varchar(150) DEFAULT 'freepbx' AFTER `id`";
		$result = $db->query($sql);
		$sql = "ALTER TABLE `userman_users` ADD COLUMN `authid` varchar(255) DEFAULT NULL AFTER `auth`";
		$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_groups` WHERE FIELD = "auth"')) {
	out("Adding default extension column");
		$sql = "ALTER TABLE `userman_groups` ADD COLUMN `auth` varchar(150) DEFAULT 'freepbx' AFTER `id`";
		$result = $db->query($sql);
		$sql = "ALTER TABLE `userman_groups` ADD COLUMN `authid` varchar(255) DEFAULT NULL AFTER `auth`";
		$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_groups` WHERE FIELD = "priority"')) {
	out("Adding default extension column");
		$sql = "ALTER TABLE `userman_groups` ADD COLUMN `priority` int(11) NOT NULL DEFAULT 5 AFTER `description`";
		$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_users` WHERE FIELD = "default_extension"')) {
	out("Adding default extension column");
		$sql = "ALTER TABLE `userman_users` ADD COLUMN `default_extension` VARCHAR(45) NOT NULL DEFAULT 'none' AFTER `password`";
		$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_users` WHERE FIELD = "primary_group"')) {
	//TODO: need to do migration here as well
	out("Adding groups column");
		$sql = "ALTER TABLE `userman_users` ADD COLUMN `primary_group` varchar(10) DEFAULT NULL AFTER `default_extension`";
		$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_users` WHERE FIELD = "displayname"')) {
		out("Adding additional field displayname");
		$sql = "ALTER TABLE `userman_users` ADD COLUMN `displayname` VARCHAR(200) NULL DEFAULT NULL AFTER `lname`";
		$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_users` WHERE FIELD = "fname"')) {
		out("Adding additional fields");
		$sql = "ALTER TABLE `userman_users` ADD COLUMN `fname` VARCHAR(100) NULL DEFAULT NULL AFTER `default_extension`, ADD COLUMN `lname` VARCHAR(100) NULL DEFAULT NULL AFTER `fname`, ADD COLUMN `title` VARCHAR(100) NULL DEFAULT NULL AFTER `lname`, ADD COLUMN `department` VARCHAR(100) NULL DEFAULT NULL AFTER `title`, ADD COLUMN `email` VARCHAR(100) NULL DEFAULT NULL AFTER `department`, ADD COLUMN `cell` VARCHAR(100) NULL DEFAULT NULL AFTER `email`, ADD COLUMN `work` VARCHAR(100) NULL DEFAULT NULL AFTER `cell`, ADD COLUMN `home` VARCHAR(100) NULL DEFAULT NULL AFTER `work`";
		$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_users` WHERE FIELD = "company"')) {
	out("Adding additional field company");
	$sql = "ALTER TABLE `userman_users` ADD COLUMN `company` VARCHAR(100) NULL DEFAULT NULL AFTER `title`";
	$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_users` WHERE FIELD = "fax"')) {
	out("Adding additional field fax");
	$sql = "ALTER TABLE `userman_users` ADD COLUMN `fax` VARCHAR(100) NULL DEFAULT NULL AFTER `home`";
	$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_users` WHERE FIELD = "language"')) {
	out("Adding additional field language");
	$sql = "ALTER TABLE `userman_users` ADD COLUMN `language` VARCHAR(100) NULL DEFAULT NULL AFTER `department`";
	$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_users` WHERE FIELD = "timezone"')) {
	out("Adding additional field timezone");
	$sql = "ALTER TABLE `userman_users` ADD COLUMN `timezone` VARCHAR(100) NULL DEFAULT NULL AFTER `language`";
	$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_users` WHERE FIELD = "dateformat"')) {
	out("Adding additional field dateformat");
	$sql = "ALTER TABLE `userman_users` ADD COLUMN `dateformat` VARCHAR(100) NULL DEFAULT NULL AFTER `timezone`";
	$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_users` WHERE FIELD = "timeformat"')) {
	out("Adding additional field timeformat");
	$sql = "ALTER TABLE `userman_users` ADD COLUMN `timeformat` VARCHAR(100) NULL DEFAULT NULL AFTER `dateformat`";
	$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_users` WHERE FIELD = "datetimeformat"')) {
	out("Adding additional field datetimeformat");
	$sql = "ALTER TABLE `userman_users` ADD COLUMN `datetimeformat` VARCHAR(100) NULL DEFAULT NULL AFTER `timeformat`";
	$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_groups` WHERE FIELD = "language"')) {
	out("Adding additional field language");
	$sql = "ALTER TABLE `userman_groups` ADD COLUMN `language` VARCHAR(100) NULL DEFAULT NULL AFTER `description`";
	$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_groups` WHERE FIELD = "timezone"')) {
	out("Adding additional field timezone");
	$sql = "ALTER TABLE `userman_groups` ADD COLUMN `timezone` VARCHAR(100) NULL DEFAULT NULL AFTER `language`";
	$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_groups` WHERE FIELD = "dateformat"')) {
	out("Adding additional field dateformat");
	$sql = "ALTER TABLE `userman_groups` ADD COLUMN `dateformat` VARCHAR(100) NULL DEFAULT NULL AFTER `timezone`";
	$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_groups` WHERE FIELD = "timeformat"')) {
	out("Adding additional field timeformat");
	$sql = "ALTER TABLE `userman_groups` ADD COLUMN `timeformat` VARCHAR(100) NULL DEFAULT NULL AFTER `dateformat`";
	$result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_groups` WHERE FIELD = "datetimeformat"')) {
	out("Adding additional field datetimeformat");
	$sql = "ALTER TABLE `userman_groups` ADD COLUMN `datetimeformat` VARCHAR(100) NULL DEFAULT NULL AFTER `timeformat`";
	$result = $db->query($sql);
}

$sql = 'SHOW COLUMNS FROM userman_users WHERE FIELD = "email"';
$sth = FreePBX::Database()->prepare($sql);
$sth->execute();
$res = $sth->fetch(\PDO::FETCH_ASSOC);
if($res['Type'] != "text") {
	$sql = "ALTER TABLE userman_users
	CHANGE COLUMN `email` `email` text NULL DEFAULT NULL";
	$sth = FreePBX::Database()->prepare($sql);
	$sth->execute();
}

$sql = 'SHOW COLUMNS FROM userman_users WHERE FIELD = "auth"';
$sth = FreePBX::Database()->prepare($sql);
$sth->execute();
$res = $sth->fetch(\PDO::FETCH_ASSOC);
if($res['Type'] != "varchar(150)") {
	$sql = "ALTER TABLE userman_users
	CHANGE COLUMN `auth` `auth` VARCHAR(150) NULL DEFAULT 'freepbx' ,
	CHANGE COLUMN `username` `username` VARCHAR(150) NULL DEFAULT NULL ,
	DROP INDEX `username_UNIQUE` ,
	ADD UNIQUE INDEX `username_UNIQUE` (`username` ASC, `auth` ASC)";
	$sth = FreePBX::Database()->prepare($sql);
	$sth->execute();
}

$sql = 'SHOW COLUMNS FROM userman_groups WHERE FIELD = "auth"';
$sth = FreePBX::Database()->prepare($sql);
$sth->execute();
$res = $sth->fetch(\PDO::FETCH_ASSOC);
if($res['Type'] != "varchar(150)") {
	$sql = "ALTER TABLE `userman_groups`
	CHANGE COLUMN `auth` `auth` VARCHAR(150) NULL DEFAULT 'freepbx' ,
	CHANGE COLUMN `groupname` `groupname` VARCHAR(150) NULL DEFAULT NULL ,
	ADD UNIQUE INDEX `groupname_UNIQUE` (`auth` ASC, `groupname` ASC);";
	$sth = FreePBX::Database()->prepare($sql);
	$sth->execute();
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
