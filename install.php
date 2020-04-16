<?php
/* When Legacy restore(less than 2.12) where authtype itself missing from advanced settings **/
if(FreePBX::Config()->get('AUTHTYPE') =='') {
	out('AUTHTYPE is missing we are adding back');
	$description = "Authentication type to use for web admin. If type set to database, the primary AMP admin credentials will be the AMPDBUSER/AMPDBPASS above. When using database you can create users that are restricted to only certain module pages. When set to none, you should make sure you have provided security at the apache level. When set to webserver, FreePBX will expect authentication to happen at the apache level, but will take the user credentials and apply any restrictions as if it were in database mode.";
	$sql = "INSERT INTO freepbx_settings (`keyword`,`value`,`name`,`description`,`type`,`options`,`defaultval`,`readonly`,`category`) VALUES('AUTHTYPE','usermanager','Authorization Type','$description','select','database,none,webserver,usermanager','database','1','System Setup')";
	FreePBX::Database()->query($sql);
	out("Added the AUTHTYPE settings");
}

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
