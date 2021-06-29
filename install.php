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
