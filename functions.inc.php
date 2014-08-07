<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function setup_userman() {
	if(version_compare(getVersion(), '12.0', '<')) {
		if(!interface_exists('BMO')) {
			include(dirname(__FILE__).'/BMO.class.php');
			include(dirname(__FILE__).'/Userman.class.php');
		}
		$userman = Userman::create();
		return $userman;
	}
	return FreePBX::create()->Userman;
}

include('functions.inc/guihooks.php');
include('functions.inc/functions.php');
