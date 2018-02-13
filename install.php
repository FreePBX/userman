<?php
global $db;
//Change login type to usermanager if installed.
if(FreePBX::Config()->get('AUTHTYPE') == "database") {
	FreePBX::Config()->update('AUTHTYPE','usermanager');
}

if(!empty($sqls)) {
	try {
		$sth = FreePBX::Database()->prepare("SHOW TABLES LIKE 'freepbx_users'");
		$sth->execute();
		$tables = $sth->fetchAll(\PDO::FETCH_ASSOC);
		if(!empty($tables)) {
			out(_('Unable to upgrade. Old users table still exists. Please report this to http://community.freepbx.org'));
			return false;
		}
	} catch(\Exception $e) {}
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

try {
	$sql = "SELECT count(*) as count FROM userman_directories WHERE driver = 'Msad2'";
	$sth = FreePBX::Database()->prepare($sql);
	$sth->execute();
	$res = $sth->fetch(\PDO::FETCH_ASSOC);
	if(!empty($res['count'])) {
		out(_("!!!WARNING!!!! MSAD2 Directory Groups SID might have changed. Please check your permissions! !!!WARNING!!!!"));
	}
} catch(\Exception $e) {}
