<?php
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

$sqls = array();
$sqls[] = "CREATE TABLE IF NOT EXISTS `userman_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auth` varchar(255) DEFAULT 'freepbx',
  `authid` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
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
  `email` varchar(100) DEFAULT NULL,
  `cell` varchar(100) DEFAULT NULL,
  `work` varchar(100) DEFAULT NULL,
  `home` varchar(100) DEFAULT NULL,
  `fax` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`)
)";
$sqls[] = "CREATE TABLE IF NOT EXISTS `userman_users_settings` (
  `uid` int(11) NOT NULL,
  `module` char(65) NOT NULL,
  `key` char(255) NOT NULL,
  `val` longblob NOT NULL,
  `type` char(16) DEFAULT NULL,
  UNIQUE KEY `index4` (`uid`,`module`,`key`),
  KEY `index2` (`uid`,`key`),
  KEY `index6` (`module`,`uid`)
)";
$sqls[] = "CREATE TABLE IF NOT EXISTS `userman_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auth` varchar(255) DEFAULT 'freepbx',
  `authid` varchar(255) DEFAULT NULL,
  `groupname` varchar(250) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT 5,
  `users` BLOB,
  `permissions` BLOB,
  PRIMARY KEY (`id`)
)";
$sqls[] = "CREATE TABLE IF NOT EXISTS `userman_groups_settings` (
  `gid` int(11) NOT NULL,
  `module` char(65) NOT NULL,
  `key` char(255) NOT NULL,
  `val` longblob NOT NULL,
  `type` char(16) DEFAULT NULL,
  UNIQUE KEY `index4` (`gid`,`module`,`key`),
  KEY `index2` (`gid`,`key`),
  KEY `index6` (`module`,`gid`)
)";
foreach($sqls as $sql) {
	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_freepbx($result->getDebugInfo());
	}
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_users` WHERE FIELD = "auth"')) {
	out("Adding default extension column");
    $sql = "ALTER TABLE `userman_users` ADD COLUMN `auth` varchar(255) DEFAULT 'freepbx' AFTER `id`";
    $result = $db->query($sql);
    $sql = "ALTER TABLE `userman_users` ADD COLUMN `authid` varchar(255) DEFAULT NULL AFTER `auth`";
    $result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `userman_groups` WHERE FIELD = "auth"')) {
	out("Adding default extension column");
    $sql = "ALTER TABLE `userman_groups` ADD COLUMN `auth` varchar(255) DEFAULT 'freepbx' AFTER `id`";
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



$freepbx_conf =& freepbx_conf::create();

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
$freepbx_conf->define_conf_setting('AMPUSERMANEMAILFROM',$set,true);
