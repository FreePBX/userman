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
// add default template if it is not added 
$sql = "SHOW INDEXES FROM userman_ucp_templates";
$sth = \FreePBX::Database()->prepare($sql);
$sth->execute();
$row = $sth->fetch(PDO::FETCH_ASSOC);
if($row['Cardinality'] == 0){
	$brand = \FreePBX::Config()->get('DASHBOARD_FREEPBX_BRAND');
	$brandtemp = $brand.'-Template';
	out("Adding default template settings ".$brandtemp );
	// add the defult template
	$insert = "INSERT INTO userman_ucp_templates(`templatename`,`description`,`importedfromuname`)VALUES(?,'Template with Vm and CDR widgets','Default-Template')";
	$sth = \FreePBX::Database()->prepare($insert);
	$sth->execute(array($brandtemp));
	//insert the template dashboard
	$sql = "INSERT INTO userman_template_settings(`tid`,`module`,`key`,`val`,`type`) VALUES(:tid,'UCP',:key,:val,:type)";
	$sth = \FreePBX::Database()->prepare($sql);
	$sth->execute(array(':tid' => 1, ':key' => 'dashboards',':val' => '[{"id":"513cb28f-f834-4b66-8d36-4405bd302520","name":"'.$brand.'-dashboard"}]',':type'=>'json-arr'));
	//insert template settings
	$sql = "INSERT INTO userman_template_settings(`tid`,`module`,`key`,`val`,`type`) VALUES(:tid,'UCP',:key,:val,:type)";
	$sth = \FreePBX::Database()->prepare($sql);
	$sth->execute(array(':tid' => 1, ':key' => 'dashboard-layout-513cb28f-f834-4b66-8d36-4405bd302520',':val' => '[{"id":"eac6afa4-2a21-43bc-9b4d-e34d06ceeaa6","widget_module_name":"Call History","name":"XXXX","rawname":"cdr","widget_type_id":"XXX","has_settings":false,"size_x":0,"size_y":0,"col":6,"row":7,"locked":false},{"id":"122acb19-b22f-4e71-9ba6-5e0f201c9142","widget_module_name":"Voicemail","name":"XXXX","rawname":"voicemail","widget_type_id":"XXX","has_settings":true,"size_x":6,"size_y":0,"col":6,"row":7,"locked":false}]',':type'=>''));	
}else {
	out("Default template already added");
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
