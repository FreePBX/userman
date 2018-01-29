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
/* Databases
 * Only needed before 14. After 14 this is handled in the XML.
 */
$table = FreePBX::Database()->migrate("userman_users");
$cols = array (
	'id' => array (
		'type' => 'integer',
		'primaryKey' => true,
		'autoincrement' => true,
	),
	'auth' => array (
		'type' => 'string',
		'length' => '150',
		'notnull' => false,
		'default' => 'freepbx',
	),
	'authid' => array (
		'type' => 'string',
		'length' => '750',
		'notnull' => false,
		'default' => 'freepbx',
	),
	'username' => array (
		'type' => 'string',
		'length' => '150',
		'notnull' => false,
	),
	'description' => array (
		'type' => 'string',
		'length' => '255',
		'notnull' => false,
	),
	'password' => array (
		'type' => 'string',
		'length' => '255',
		'notnull' => false,
	),
	'default_extension' => array (
		'type' => 'string',
		'length' => '45',
		'default' => 'none',
	),
	'primary_group' => array (
		'type' => 'integer',
		'notnull' => false,
	),
	'permissions' => array (
		'type' => 'blob',
		'notnull' => false,
	),
	'fname' => array (
		'type' => 'string',
		'length' => '100',
		'notnull' => false,
	),
	'lname' => array (
		'type' => 'string',
		'length' => '100',
		'notnull' => false,
	),
	'displayname' => array (
		'type' => 'string',
		'length' => '200',
		'notnull' => false,
	),
	'title' => array (
		'type' => 'string',
		'length' => '100',
		'notnull' => false,
	),
	'company' => array (
		'type' => 'string',
		'length' => '100',
		'notnull' => false,
	),
	'department' => array (
		'type' => 'string',
		'length' => '100',
		'notnull' => false,
	),
	'email' => array (
		'type' => 'text',
		'length' => '',
		'notnull' => false,
	),
	'cell' => array (
		'type' => 'string',
		'length' => '100',
		'notnull' => false,
	),
	'work' => array (
		'type' => 'string',
		'length' => '100',
		'notnull' => false,
	),
	'home' => array (
		'type' => 'string',
		'length' => '100',
		'notnull' => false,
	),
	'fax' => array (
		'type' => 'string',
		'length' => '100',
		'notnull' => false,
	),
);

$indexes = array (
	'username_UNIQUE' => array (
		'type' => 'unique',
		'cols' => array (
			0 => 'username',
			1 => 'auth',
		),
	),
);
$table->modify($cols, $indexes);
unset($table);

$table = FreePBX::Database()->migrate("userman_users_settings");
$cols = array (
	'uid' => array (
		'type' => 'integer',
	),
	'module' => array (
		'type' => 'string',
		'length' => 65,
	),
	'key' => array (
		'type' => 'string',
		'length' => 190,
	),
	'val' => array (
		'type' => 'blob',
	),
	'type' => array (
		'type' => 'string',
		'length' => 16,
		'notnull' => false,
	),
);

$indexes = array (
	'index4' => array (
		'type' => 'unique',
		'cols' => array (
			0 => 'uid',
			1 => 'module',
			2 => 'key',
		),
	),
	'index2' => array (
		'type' => 'index',
		'cols' => array (
			0 => 'uid',
			1 => 'key',
		),
	),
	'index6' => array (
		'type' => 'index',
		'cols' => array (
			0 => 'module',
			1 => 'uid',
		),
	),
);
$table->modify($cols, $indexes);
unset($table);


$table = FreePBX::Database()->migrate("userman_groups");
$cols = array (
	'id' => array (
		'type' => 'integer',
		'primaryKey' => true,
		'autoincrement' => true,
	),
	'auth' => array (
		'type' => 'string',
		'length' => '150',
		'notnull' => false,
		'default' => 'freepbx',
	),
	'authid' => array (
		'type' => 'string',
		'length' => '750',
		'notnull' => false,
		'default' => 'freepbx',
	),
	'groupname' => array (
		'type' => 'string',
		'length' => '150',
		'notnull' => false,
	),
	'description' => array (
		'type' => 'string',
		'length' => '255',
		'notnull' => false,
	),
	'priority' => array (
		'type' => 'integer',
		'default' => '5',
	),
	'users' => array (
		'type' => 'blob',
		'notnull' => false,
	),
	'permissions' => array (
		'type' => 'blob',
		'notnull' => false,
	),
	'local' => array (
		'type' => 'boolean',
		'default' => '0',
	),
);

$indexes = array (
	'groupname_UNIQUE' => array (
		'type' => 'unique',
		'cols' => array (
			0 => 'groupname',
			1 => 'auth',
		),
	),
);
$table->modify($cols, $indexes);
unset($table);

$table = FreePBX::Database()->migrate("userman_groups_settings");
$cols = array (
	'gid' => array (
		'type' => 'integer',
	),
	'module' => array (
		'type' => 'string',
		'length' => 65,
	),
	'key' => array (
		'type' => 'string',
		'length' => 190,
	),
	'val' => array (
		'type' => 'blob',
	),
	'type' => array (
		'type' => 'string',
		'length' => 16,
		'notnull' => false,
	),
);

$indexes = array (
	'index4' => array (
		'type' => 'unique',
		'cols' => array (
			0 => 'gid',
			1 => 'module',
			2 => 'key',
		),
	),
	'index2' => array (
		'type' => 'index',
		'cols' => array (
			0 => 'gid',
			1 => 'key',
		),
	),
	'index6' => array (
		'type' => 'index',
		'cols' => array (
			0 => 'module',
			1 => 'gid',
		),
	),
);
$table->modify($cols, $indexes);
unset($table);

$table = FreePBX::Database()->migrate("userman_directories");
$cols = array (
	'id' => array (
		'type' => 'integer',
		'primaryKey' => true,
		'autoincrement' => true,
	),
	'name' => array (
		'type' => 'string',
		'length' => '250',
		'notnull' => false,
	),
	'driver' => array (
		'type' => 'string',
		'length' => '150',
		'default' => '',
	),
	'active' => array (
		'type' => 'boolean',
		'default' => '0',
	),
	'order' => array (
		'type' => 'integer',
		'default' => '5',
	),
	'default' => array (
		'type' => 'boolean',
		'default' => '0',
	),
	'locked' => array (
		'type' => 'boolean',
		'default' => '0',
	),
);

$indexes = array ();
$table->modify($cols, $indexes);
unset($table);


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

try {
	$sql = "SELECT count(*) as count FROM userman_directories WHERE driver = 'Msad2'";
	$sth = FreePBX::Database()->prepare($sql);
	$sth->execute();
	$res = $sth->fetch(\PDO::FETCH_ASSOC);
	if(!empty($res['count'])) {
		out(_("!!!WARNING!!!! MSAD2 Directory Groups SID might have changed. Please check your permissions! !!!WARNING!!!!"));
	}
} catch(\Exception $e) {}
