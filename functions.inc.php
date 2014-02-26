<?php
function setup_userman() {
	if(version_compare(getVersion(), '12.0', '<')) {
		if(!interface_exists('BMO')) {
			include(dirname(__FILE__).'/BMO.class.php');
			include(dirname(__FILE__).'/Userman.class.php');
		}
		$userman = Userman::create();
		return $userman;
	} else {
		return FreePBX::create()->Userman;
	}
}

if(version_compare(getVersion(), '12.0', '<')) {
	$userman = setup_userman();
	$userman->doConfigPageInit($_REQUEST['display']);
}

include('functions.inc/guihooks.php');
include('functions.inc/functions.php');