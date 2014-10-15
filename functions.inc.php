<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function setup_userman() {
	return FreePBX::create()->Userman;
}

include('functions.inc/guihooks.php');
include('functions.inc/functions.php');
