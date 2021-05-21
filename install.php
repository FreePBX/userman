<?php

$freepbx = \FreePBX::Create();

//Change login type to usermanager if installed.
if($freepbx->Config->get('AUTHTYPE') == "database") {
	$freepbx->Config->update('AUTHTYPE','usermanager');
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
$freepbx->Config->define_conf_setting('AMPUSERMANEMAILFROM',$set,true);

//Set existing empty /AMPUSER/{ext}/accountcode entries to userId
$um = $freepbx->Userman;
if (!$um->getConfig('setAmpuserAcctCodeToUserIdComplete')) {
	$astman = $freepbx->astman;
	$sql = "SELECT id,default_extension FROM userman_users";
	$sth = \FreePBX::Database()->prepare($sql);
	$sth->execute();
	$rows = $sth->fetchAll(PDO::FETCH_ASSOC);

	foreach($rows as $user) {
		$uId = $user['id'];
		$dExt = $user['default_extension'];
		$ampuAcctCode = $astman->database_get('AMPUSER', $dExt . '/accountcode');
		$ampuDevice = $astman->database_get('AMPUSER', $dExt . '/device');
		if (empty($ampuAcctCode) && !empty($ampuDevice)
			&& is_numeric($dExt)
		) {
			$astman->database_put('AMPUSER', $dExt . '/accountcode', 'u'.$uId);
		}
	}

	$um->setConfig('setAmpuserAcctCodeToUserIdComplete', 1);
}
