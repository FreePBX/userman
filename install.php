<?php

$freepbx = FreePBX::Create();

/* When Legacy restore(less than 2.12) where authtype itself missing from advanced settings **/
$sql = "Update freepbx_settings SET `readonly`='0',`value` = 'usermanager' WHERE `keyword` ='AUTHTYPE'";
FreePBX::Database()->query($sql);
if($freepbx->Config->get('AUTHTYPE') == '') {
	out('AUTHTYPE is missing we are adding back');
	$description = "Authentication type to use for web admin. If type set to database, the primary AMP admin credentials will be the AMPDBUSER/AMPDBPASS above. When using database you can create users that are restricted to only certain module pages. When set to none, you should make sure you have provided security at the apache level. When set to webserver, FreePBX will expect authentication to happen at the apache level, but will take the user credentials and apply any restrictions as if it were in database mode.";
	$sql = "INSERT INTO freepbx_settings (`keyword`,`value`,`name`,`description`,`type`,`options`,`defaultval`,`readonly`,`category`) VALUES('AUTHTYPE','usermanager','Authorization Type','$description','select','database,none,webserver,usermanager','usermanager','0','System Setup')";
	FreePBX::Database()->query($sql);
	out("Added the AUTHTYPE settings");
}
createDefaultUCPTemplate();
//Change login type to usermanager if installed.
if($freepbx->Config->get('AUTHTYPE') == "database") {
	$freepbx->Config->update('AUTHTYPE','usermanager');
}

$set = [];
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

$set['value'] = false;
$set['defaultval'] =& $set['value'];
$set['options'] = '';
$set['sortorder'] = 1;
$set['name'] = _('Enable Call Activity Groups');
$set['description'] = _("When set to 'Yes',The User Management UI will be having a new tab 'Call Activity Groups'."
." This group is designed for call-related tasks. Use it to efficiently organize users for specific call operations, such as streamlining call monitoring assignments.");
$set['emptyok'] = 0;
$set['level'] = 1;
$set['readonly'] = 0;
$set['type'] = CONF_TYPE_BOOL;
$set['hidden'] = 0;
$freepbx->Config->define_conf_setting('USERMAN_ENABLE_CALL_ACTIVITY_GROUPS',$set);

$set['value'] = '30';
$set['defaultval'] =& $set['value'];
$set['options'] = array(1,50);
$set['sortorder'] = 2;
$set['name'] = _('Call Activity Groups Max User Limit');
$set['description'] = _("This is the limit for the number of users that can be added to a Call Activity Group.");
$set['emptyok'] = 0;
$set['level'] = 1;
$set['readonly'] = 0;
$set['type'] = CONF_TYPE_INT;
$set['hidden'] = 0;
$freepbx->Config->define_conf_setting('USERMAN_CALL_ACTIVITY_GRP_USER_LIMIT',$set);

cronjobEntry($freepbx);

function cronjobEntry($freepbx){
	$AMPASTERISKWEBUSER = $freepbx->Config->get("AMPASTERISKWEBUSER");
	$AMPSBIN = $freepbx->Config->get("AMPSBIN");
		$freepbxCron = $freepbx->Cron($AMPASTERISKWEBUSER);
		$crons = $freepbxCron->getAll();
		foreach($crons as $cron) {
			if(preg_match("/fwconsole userman sync$/",(string) $cron) || preg_match("/fwconsole userman --syncall /",(string) $cron)) {
				$freepbxCron->remove($cron);
				out('Removed existing Cron entry '.$cron);
			}
		}
		$freepbx->Job->remove('userman', 'syncall');
		$freepbxCron->addLine("*/15 * * * * [ -e ".$AMPSBIN."/fwconsole ] && sleep $((RANDOM\%30)) && ".$AMPSBIN."/fwconsole userman --syncall -q");
		outn(' Added new Cron entry');
}
function createDefaultUCPTemplate(){
	//No harm if delete templatecreator on install  ,as we auto generated this from Userman page
	$delete = "delete from kvstore_FreePBX_modules_Userman where `id` = 'templatecreator'";
	$sth = FreePBX::Database()->prepare($delete);
	$sth->execute();
	//check any entries are there before we insert
	$select = "SELECT COUNT(*) as rowcount FROM userman_ucp_templates ";
	$sth = FreePBX::Database()->prepare($select);
	$sth->execute();
	$count = $sth->fetch(PDO::FETCH_ASSOC);
	if($count['rowcount'] == 0) {
		// add default template if it is not added
		$sql = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_NAME ='userman_ucp_templates'";
		$sth = FreePBX::Database()->prepare($sql);
		$sth->execute();
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		if($row['AUTO_INCREMENT'] == 1){
			$brand = FreePBX::Config()->get('DASHBOARD_FREEPBX_BRAND');
			$brandtemp = $brand.'-Template';
			out("Adding default template settings ".$brandtemp );
			// add the defult template
			$insert = "INSERT INTO userman_ucp_templates(`templatename`,`description`,`importedfromuname`)VALUES(?,'Template with Vm and CDR widgets','Default-Template')";
			$sth = FreePBX::Database()->prepare($insert);
			$sth->execute([$brandtemp]);
			//insert the template dashboard
			$truncate = "truncate userman_template_settings";
			$sth = FreePBX::Database()->prepare($truncate);
			$sth->execute();
			$sql = "INSERT INTO userman_template_settings(`tid`,`module`,`key`,`val`,`type`) VALUES(:tid,'UCP',:key,:val,:type)";
			$sth = FreePBX::Database()->prepare($sql);
			$sth->execute([':tid' => 1, ':key' => 'dashboards', ':val' => '[{"id":"513cb28f-f834-4b66-8d36-4405bd302520","name":"'.$brand.'-dashboard"}]', ':type'=>'json-arr']);
			//insert template settings
			$sql = "INSERT INTO userman_template_settings(`tid`,`module`,`key`,`val`,`type`) VALUES(:tid,'UCP',:key,:val,:type)";
			$sth = FreePBX::Database()->prepare($sql);
			$sth->execute([':tid' => 1, ':key' => 'dashboard-layout-513cb28f-f834-4b66-8d36-4405bd302520', ':val' => '[{"id":"eac6afa4-2a21-43bc-9b4d-e34d06ceeaa6","widget_module_name":"Call History","name":"XXXX","rawname":"cdr","widget_type_id":"XXX","has_settings":false,"size_x":0,"size_y":0,"col":6,"row":7,"locked":false},{"id":"122acb19-b22f-4e71-9ba6-5e0f201c9142","widget_module_name":"Voicemail","name":"XXXX","rawname":"voicemail","widget_type_id":"XXX","has_settings":true,"size_x":6,"size_y":0,"col":6,"row":7,"locked":false}]', ':type'=>'']);	
		} else {
			out("Default template already added");
		}
	}else {
		out("UCP template settings configured already");
	}
}
