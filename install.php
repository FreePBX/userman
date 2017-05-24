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

$auth = FreePBX::Userman()->getConfig('auth');

$check = array(
	"authFREEPBXSettings" => "freepbx",
	"authMSADSettings" => 'msad',
	"authOpenLDAPSettings" => 'openldap',
	"authVoicemailSettings" => 'voicemail'
);

foreach($check as $key => $driver) {
	$settings = FreePBX::Userman()->getConfig($key);
	if(!empty($settings)) {
		$id = FreePBX::Userman()->addDirectory(ucfirst($driver), sprintf(_('Imported %s directory'),$driver), (strtolower($auth) == $driver), $settings);
		if(!empty($id)) {
			$sql = "UPDATE userman_users SET auth = ? WHERE LOWER(auth) = '".$driver."'";
			$sth = FreePBX::Database()->prepare($sql);
			$sth->execute(array($id));
			$sql = "UPDATE userman_groups SET auth = ? WHERE LOWER(auth) = '".$driver."'";
			$sth = FreePBX::Database()->prepare($sql);
			$sth->execute(array($id));
			if(strtolower($auth) == $driver) {
				FreePBX::Userman()->setDefaultDirectory($id);
			}
		}
		FreePBX::Userman()->setConfig($key,false);
	}
}
if(!empty($auth)) {
	FreePBX::Userman()->setConfig('auth',false);
}

$directories = FreePBX::Userman()->getAllDirectories();
if(empty($directories)) {
	$id = FreePBX::Userman()->addDirectory('Freepbx', _("PBX Internal Directory"), true, array());
	FreePBX::Userman()->setDefaultDirectory($id);
}

$dir = FreePBX::Userman()->getDefaultDirectory();
if($dir['driver'] == 'Freepbx') {
	FreePBX::Userman()->addDefaultGroupToDirectory($dir['id']);
}
