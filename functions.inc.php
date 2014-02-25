<?php
function setup_userman() {
	if(!interface_exists('BMO')) {
		include(dirname(__FILE__).'/BMO.class.php');
		include(dirname(__FILE__).'/Userman.class.php');
		$userman = Userman::create();
		return $userman;
	} else {
		return FreePBX::create()->Userman;
	}
}

include('functions.inc/guihooks.php');
include('functions.inc/functions.php');