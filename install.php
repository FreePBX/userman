<?php

$freepbx = \FreePBX::Create();

/* When Legacy restore(less than 2.12) where authtype itself missing from advanced settings **/
$sql = "Update freepbx_settings SET `readonly`='0',`value` = 'usermanager' WHERE `keyword` ='AUTHTYPE'";
\FreePBX::Database()->query($sql);
if($freepbx->Config->get('AUTHTYPE') == '') {
	out('AUTHTYPE is missing we are adding back');
	$description = "Authentication type to use for web admin. If type set to database, the primary AMP admin credentials will be the AMPDBUSER/AMPDBPASS above. When using database you can create users that are restricted to only certain module pages. When set to none, you should make sure you have provided security at the apache level. When set to webserver, FreePBX will expect authentication to happen at the apache level, but will take the user credentials and apply any restrictions as if it were in database mode.";
	$sql = "INSERT INTO freepbx_settings (`keyword`,`value`,`name`,`description`,`type`,`options`,`defaultval`,`readonly`,`category`) VALUES('AUTHTYPE','usermanager','Authorization Type','$description','select','database,none,webserver,usermanager','usermanager','0','System Setup')";
	\FreePBX::Database()->query($sql);
	out("Added the AUTHTYPE settings");
}

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
