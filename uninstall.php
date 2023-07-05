<?php
/**
 * disable FOREIGN_KEY_CHECKS
 */
$disfkey = "SET FOREIGN_KEY_CHECKS=0";
$db->query($disfkey);

out('Remove all User Management tables');
$tables = ['userman_users', 'userman_users_settings', 'userman_groups', 'userman_groups_settings'];
$error_mysql = "";
foreach ($tables as $table) {
	$sql = "DROP TABLE IF EXISTS {$table}";
	$result = $db->query($sql);
	if (DB::IsError($result)) {
		$error_mysql= $result->getDebugInfo();
		break;
	}
	unset($result);
}

/**
 * enable FOREIGN_KEY_CHECKS
 */
$enablefkey = "SET FOREIGN_KEY_CHECKS=1";
$db->query($enablefkey);

if ($error_mysql != "") {
	die_freepbx($error_mysql);
}
