<?php
$sqls = array();
$sqls[] = "CREATE TABLE `freepbx_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `default_extension` varchar(45) NOT NULL DEFAULT 'none',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`)
)";
$sqls[] = "CREATE TABLE `freepbx_users_settings` (
  `uid` int(11) NOT NULL,
  `module` char(65) NOT NULL,
  `key` char(255) NOT NULL,
  `val` longblob NOT NULL,
  `type` char(16) DEFAULT NULL,
  UNIQUE KEY `index4` (`uid`,`module`,`key`),
  KEY `index2` (`uid`,`key`),
  KEY `index6` (`module`,`uid`)
)";
foreach($sqls as $sql) {
	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_freepbx($result->getDebugInfo());
	}
}

if (!$db->getAll('SHOW COLUMNS FROM `freepbx_users` WHERE FIELD = "default_extension"')) {
	out("Adding txgain and rxgain column to digital table");
    $sql = "ALTER TABLE `freepbx_users` ADD COLUMN `default_extension` VARCHAR(45) NOT NULL DEFAULT 'none' AFTER `password`";
    $result = $db->query($sql);
}
