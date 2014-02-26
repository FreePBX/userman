<?php
$sqls = array();
$sqls[] = "CREATE TABLE `freepbx_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
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