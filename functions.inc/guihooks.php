<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
function userman_configpageinit($pagename) {
	global $currentcomponent;
	global $amp_conf;

	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$extension = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;
	$tech_hardware = isset($_REQUEST['tech_hardware'])?$_REQUEST['tech_hardware']:null;

	// We only want to hook 'users' or 'extensions' pages.
	if ($pagename != 'users' && $pagename != 'extensions')  {
		return true;
	}

	//$currentcomponent->addprocessfunc('userman_configprocess', 1);

	if ($tech_hardware != null || $extdisplay != '' || $pagename == 'users' || $action == 'add') {
		// On a 'new' user, 'tech_hardware' is set, and there's no extension. Hook into the page.
		if ($tech_hardware != null ) {
			userman_applyhooks();
		} elseif ($action == 'add') {
			$currentcomponent->addprocessfunc('userman_configprocess', 1);
		} elseif ($extdisplay != '' || $pagename == 'users') {
			// We're now viewing an extension, so we need to display _and_ process.
			userman_applyhooks();
			$currentcomponent->addprocessfunc('userman_configprocess', 1);
		}
	}
}

function userman_applyhooks() {
	global $currentcomponent;
	$currentcomponent->addguifunc('userman_configpageload');
}

function userman_configpageload() {
	global $currentcomponent;
	global $amp_conf;
	global $astman;
	$userman = FreePBX::create()->Userman;
	// Init vars from $_REQUEST[]
	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$ext = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$extn = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;
	$display = isset($_REQUEST['display'])?$_REQUEST['display']:null;

	if ($ext==='') {
		$extdisplay = $extn;
	} else {
		$extdisplay = $ext;
	}

	if ($action != 'del') {
		$usersC = array();  // Initialize the array.
		foreach(core_users_list() as $user) {
			$usersC[] = $user[0];
		}
		$section = _("User Manager Settings");
		$category = "general";
		$usettings = $userman->getAuthAllPermissions();
		$allGroups = array();
		foreach($userman->getAllGroups() as $g) {
			$allGroups[] = array(
				"value" => $g['id'],
				"text" => $g['groupname']
			);
		}
		//Old Extension
		if($extdisplay != '') {
			$userM = $userman->getUserByDefaultExtension($extdisplay);
			$selarray = array(
				array(
					"value" => 'none',
					"text" => _('None')
				),
			);
			if($usettings['addUser']) {
				$selarray[]	= array(
					"value" => 'add',
					"text" => _('Create New User')
				);
			}
			if(!empty($userM)) {
				$selarray[] = array(
					"value" => $userM['id'],
					"text" => $userM['username'] . " (" . _("Linked") . ")"
				);
			}

			$userarray = array();
			$uUsers = array();
			foreach($userman->getAllUsers() as $user) {
				$uUsers[] = $user['username'];
				if($user['default_extension'] != 'none' && in_array($user['default_extension'],$usersC)) {
					continue;
				}
				$userarray[] = array(
						"value" => $user['id'],
						"text" => $user['username']
				);
			}
			$selarray = array_merge($selarray,$userarray);

			if(!empty($userM)) {
				$currentcomponent->addguielem($section, new gui_link('userman|'.$extdisplay, sprintf(_('Linked to User %s'),$userM['username']), '?display=userman&action=showuser&user='.$userM['id']),$category);
				$currentcomponent->addguielem($section, new gui_selectbox('userman_assign', $selarray, $userM['id'], _('Link to a Different Default User:'), _('Select a user that this extension should be linked to in User Manager, else select Create New User to have User Manager autogenerate a new user that will be linked to this extension'), false, 'frm_extensions_usermanPassword();'),$category);
				$groups = $userman->getGroupsByID($userM['id']);
				$groups = is_array($groups) ? $groups : array();
			} else {
				$currentcomponent->addguielem($section, new gui_selectbox('userman_assign', $selarray, '', _('Link to a Default User'), _('Select a user that this extension should be linked to in User Manager, else select Create New User to have User Manager autogenerate a new user that will be linked to this extension'), false, 'frm_'.$display.'_usermanPassword();'),$category);
				$groups = array();
			}

			$currentcomponent->addjsfunc('usermanUsername()',"if(\$('#userman_username_cb').prop('checked')) {var users = ".json_encode($uUsers)."; if(isEmpty(\$('#userman_username').val()) || users.indexOf(\$('#userman_username').val()) >= 0) {return true;}} return false;");
			$ao = $userman->getAuthObject();
			$currentcomponent->addjsfunc('usermanPassword()',"if(\$('#userman_assign').val() != 'none') {\$('#userman_group').prop('disabled',false)} else {\$('#userman_group').prop('disabled',true)} $('#userman_group').trigger('chosen:updated'); if(\$('#userman_assign').val() != 'add') {var id = \$('#userman_assign').val(); var groups = ".json_encode($userman->getAllGroups())."; var fg = []; $.each(groups, function(i,v) { if(v.users.indexOf(id) > -1) { fg.push(v.id) } }); \$('#userman_group').val(fg); $('#userman_group').trigger('chosen:updated'); \$('#userman_password').attr('disabled',true);if($('#userman_username_cb').prop('checked')) { $('#userman_username_cb').click() }\$('#userman_username_cb').attr('disabled',true);} else { var d = ".json_encode($ao->getDefaultGroups())."; \$('#userman_group').val(d); $('#userman_group').trigger('chosen:updated'); if($('#userman_assign option[value=\"".$extdisplay."\"]').length == 0){\$('#userman_username_cb').click();}\$('#userman_password').attr('disabled',false);\$('#userman_username_cb').attr('disabled',false)}");

			if($usettings['addUser']) {
				$currentcomponent->addguielem($section, new gui_textbox_check('userman_username','', _('Username'), _('If Create New User is selected this will be the username. If blank the username will be the same number as this device'),'frm_'.$display.'_usermanUsername()', _("Please select a valid username for New User Creation"),false,0,true,_('Use Custom Username'),""),$category);
				$currentcomponent->addguielem($section, new gui_textbox('userman_password',md5(uniqid()), _('Password For New User'), _('If Create New User is selected this will be the autogenerated users new password'),'','',false,0,true,false,'password-meter',false),$category);
			}
			if($usettings['modifyGroup']) {
				$currentcomponent->addguielem($section, new gui_multiselectbox('userman_group', $allGroups, $groups, _('Groups'), _('Groups that this user is a part of. You can add and remove this user from groups in this view as well'), false, '',empty($userM),"chosenmultiselect"),$category);
			}
		} else {
			//New Extension
			$selarray = array(
				array(
					"value" => 'none',
					"text" => _('None')
				),
			);
			if($usettings['addUser']) {
				$selarray[] = array(
					"value" => "add",
					"text" => _('Create New User')
				);
			}
			$uUsers = array();
			foreach($userman->getAllUsers() as $user) {
				$uUsers[] = $user['username'];
				if($user['default_extension'] != 'none' && in_array($user['default_extension'],$usersC)) {
					continue;
				}
				$selarray[] = array(
						"value" => $user['id'],
						"text" => $user['username']
				);
			}
			$currentcomponent->addjsfunc('usermanUsername()',"if(\$('#userman_username_cb').prop('checked')) {var users = ".json_encode($uUsers)."; if(isEmpty(\$('#userman_username').val()) || users.indexOf(\$('#userman_username').val()) >= 0) {return true;}} return false;");
			$ao = $userman->getAuthObject();
			$dgroups = $ao->getDefaultGroups();
			$currentcomponent->addjsfunc('usermanPassword()',"if(\$('#userman_assign').val() != 'none') {\$('#userman_group').prop('disabled',false)} else {\$('#userman_group').prop('disabled',true)} $('#userman_group').trigger('chosen:updated'); if(\$('#userman_assign').val() != 'add') {var id = \$('#userman_assign').val(); var groups = ".json_encode($userman->getAllGroups())."; var fg = []; $.each(groups, function(i,v) { if(v.users.indexOf(id) > -1) { fg.push(v.id) } }); \$('#userman_group').val(fg); $('#userman_group').trigger('chosen:updated'); \$('#userman_password').attr('disabled',true);if($('#userman_username_cb').prop('checked')) { $('#userman_username_cb').click() }\$('#userman_username_cb').attr('disabled',true);} else {var d = ".json_encode($dgroups)."; \$('#userman_group').val(d); $('#userman_group').trigger('chosen:updated'); \$('#userman_password').attr('disabled',false);\$('#userman_username_cb').attr('disabled',false)}");
			$currentcomponent->addguielem($section, new gui_selectbox('userman_assign', $selarray, 'add', _('Link to a Default User'), _('Select a user that this extension should be linked to in User Manager, else select None to have no association to a user'), false, 'frm_extensions_usermanPassword()'),$category);
			if($usettings['addUser']) {
				$currentcomponent->addguielem($section, new gui_textbox_check('userman_username','', _('Username'), _('If Create New User is selected this will be the username. If blank the username will be the same number as this device'),'frm_'.$display.'_usermanUsername()', _("Please select a valid username for New User Creation"),false,0,true,_('Use Custom Username'),""),$category);
				$currentcomponent->addguielem($section, new gui_textbox('userman_password',md5(uniqid()), _('Password For New User'), _('If Create New User is selected this will be the autogenerated users new password'),'','',false,0,false,false,'password-meter',false),$category);
			}
			if($usettings['modifyGroup']) {
				$groups = !empty($groups) && is_array($groups) ? $groups : array();
				$currentcomponent->addguielem($section, new gui_multiselectbox('userman_group', $allGroups, $dgroups, _('Groups'), _('Groups that this user is a part of. You can add and remove this user from groups in this view as well'), false, '',false,"chosenmultiselect"),$category);
			}
		}
	} else {
		//unassign all extensions for this user
		foreach($userman->getAllUsers() as $user) {
			$assigned = $userman->getGlobalSettingByID($user['id'],'assigned');
			$assigned = is_array($assigned) ? $assigned : array();
			$assigned = array_diff($assigned, array($extdisplay));
			$userman->setGlobalSettingByID($user['id'],'assigned',$assigned);
		}
	}
}

function userman_configprocess() {
	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$extension = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$userman = FreePBX::create()->Userman;
	$usettings = $userman->getAuthAllPermissions();
	//if submitting form, update database
	switch ($action) {
		case "add":
			$extension = isset($_REQUEST['extension']) ? $_REQUEST['extension'] : null;
			if(isset($_REQUEST['userman_assign']) && !empty($extension)) {
				if($_REQUEST['userman_assign'] == 'add') {
					$username = (!empty($_REQUEST['userman_username_cb']) && !empty($_REQUEST['userman_username'])) ? $_REQUEST['userman_username'] : $extension;
					$displayname = !empty($_REQUEST['name']) ? $_REQUEST['name'] : $extension;
					$email = !empty($_REQUEST['email']) ? $_REQUEST['email'] : '';
					$password = $_REQUEST['userman_password'];
					$ret = $userman->addUser($username, $password, $extension, _('Autogenerated user on new device creation'), array('email' => $email, 'displayname' => $displayname));
					if($ret['status']) {
						if($usettings['modifyGroup']) {
							if(!empty($_POST['userman_group'])) {
								$groups = $userman->getAllGroups();
								foreach($groups as $group) {
									if(in_array($group['id'],$_POST['userman_group']) && !in_array($ret['id'],$group['users'])) {
										$group['users'][] = $ret['id'];
										$userman->updateGroup($group['id'],$group['groupname'], $group['groupname'], $group['description'], $group['users']);
									} elseif(!in_array($group['id'],$_POST['userman_group']) && in_array($ret['id'],$group['users'])) {
										$group['users'] = array_diff($group['users'], array($ret['id']));
										$userman->updateGroup($group['id'],$group['groupname'], $group['groupname'], $group['description'], $group['users']);
									}
								}
							} else {
								$groups = $userman->getGroupsByID($ret['id']);
								foreach($groups as $gid) {
									$group = $userman->getGroupByGID($gid);
									$group['users'] = array_diff($group['users'], array($ret['id']));
									$userman->updateGroup($group['id'],$group['groupname'], $group['groupname'], $group['description'], $group['users']);
								}
							}
						}
						if(!empty($email)) {
							$autoEmail = $userman->getGlobalsetting('autoEmail');
							$autoEmail = is_null($autoEmail) ? true : $autoEmail;
							if($autoEmail) {
								$userman->sendWelcomeEmail($username, $password);
							}
						}
					} else {
						echo "<script>alert('".$ret['message']."')</script>";
						return false;
					}
				} elseif($_REQUEST['userman_assign'] != 'none') {
					$ret = $userman->getUserByID($_REQUEST['userman_assign']);
					//run this last so that hooks to other modules get the correct information
					$o = $userman->updateUser($ret['id'],$ret['username'],$ret['username'],$extension);
					if(!empty($ret) && $o['status'] && $usettings['modifyGroup']) {
						if(!empty($_POST['userman_group'])) {
							$groups = $userman->getAllGroups();
							foreach($groups as $group) {
								if(in_array($group['id'],$_POST['userman_group']) && !in_array($ret['id'],$group['users'])) {
									$group['users'][] = $ret['id'];
									$userman->updateGroup($group['id'],$group['groupname'], $group['groupname'], $group['description'], $group['users']);
								} elseif(!in_array($group['id'],$_POST['userman_group']) && in_array($ret['id'],$group['users'])) {
									$group['users'] = array_diff($group['users'], array($ret['id']));
									$userman->updateGroup($group['id'],$group['groupname'], $group['groupname'], $group['description'], $group['users']);
								}
							}
						} else {
							$groups = $userman->getGroupsByID($ret['id']);
							foreach($groups as $gid) {
								$group = $userman->getGroupByGID($gid);
								$group['users'] = array_diff($group['users'], array($ret['id']));
								$userman->updateGroup($group['id'],$group['groupname'], $group['groupname'], $group['description'], $group['users']);
							}
						}
					} elseif(!$o['status']) {
						echo "<script>alert('".$ret['message']."')</script>";
						return false;
					}
				}
			}
		break;
		case "edit":
			if(isset($_REQUEST['userman_assign']) && $_REQUEST['userman_assign'] == 'add') {
				$userO = $userman->getUserByDefaultExtension($extension);
				$username = (!empty($_REQUEST['userman_username_cb']) && !empty($_REQUEST['userman_username'])) ? $_REQUEST['userman_username'] : $extension;
				$displayname = !empty($_REQUEST['name']) ? $_REQUEST['name'] : $extension;
				$email = !empty($_REQUEST['email']) ? $_REQUEST['email'] : '';
				$password = $_REQUEST['userman_password'];
				$ret = $userman->addUser($username, $password, $extension, _('Autogenerated user on new device creation'), array('email' => $email, 'displayname' => $displayname));
				if($ret['status'] && $usettings['modifyGroup']) {
					if(!empty($_POST['userman_group'])) {
						$groups = $userman->getAllGroups();
						foreach($groups as $group) {
							if(in_array($group['id'],$_POST['userman_group']) && !in_array($ret['id'],$group['users'])) {
								$group['users'][] = $ret['id'];
								$userman->updateGroup($group['id'],$group['groupname'], $group['groupname'], $group['description'], $group['users']);
							} elseif(!in_array($group['id'],$_POST['userman_group']) && in_array($ret['id'],$group['users'])) {
								$group['users'] = array_diff($group['users'], array($ret['id']));
								$userman->updateGroup($group['id'],$group['groupname'], $group['groupname'], $group['description'], $group['users']);
							}
						}
					} else {
						$groups = $userman->getGroupsByID($ret['id']);
						foreach($groups as $gid) {
							$group = $userman->getGroupByGID($gid);
							$group['users'] = array_diff($group['users'], array($ret['id']));
							$userman->updateGroup($group['id'],$group['groupname'], $group['groupname'], $group['description'], $group['users']);
						}
					}
				}
				if($ret['status'] && !empty($userO)) {
					$o = $userman->updateUser($userO['id'],$userO['username'],$userO['username'],'none');
					if($o['status'] && !empty($email)) {
						$autoEmail = $userman->getGlobalsetting('autoEmail');
						$autoEmail = is_null($autoEmail) ? true : $autoEmail;
						if($autoEmail) {
							$userman->sendWelcomeEmail($username, $password);
						}
					}
				}
			} elseif(isset($_REQUEST['userman_assign']) && $_REQUEST['userman_assign'] != 'none') {
				$userO = $userman->getUserByDefaultExtension($extension);
				if(!empty($userO['id']) && ($userO['id'] != $_REQUEST['userman_assign'])) {
					//run this last so that hooks to other modules get the correct information
					$o = $userman->updateUser($userO['id'],$userO['username'],$userO['username'],'none');

					if($o['status']) {
						$ret = $userman->getUserById($_REQUEST['userman_assign']);
						//run this last so that hooks to other modules get the correct information
						$userman->updateUser($ret['id'],$ret['username'],$ret['username'],$extension);
					}
				} elseif(empty($userO['id'])) {
					$ret = $userman->getUserByID($_REQUEST['userman_assign']);
					//run this last so that hooks to other modules get the correct information
					$userman->updateUser($ret['id'],$ret['username'],$ret['username'],$extension);
				} else {
					$ret = $userO;
				}
				if(!empty($ret) && $usettings['modifyGroup']) {
					if(!empty($_POST['userman_group'])) {
						$groups = $userman->getAllGroups();
						foreach($groups as $group) {
							if(in_array($group['id'],$_POST['userman_group']) && !in_array($ret['id'],$group['users'])) {
								$group['users'][] = $ret['id'];
								$userman->updateGroup($group['id'],$group['groupname'], $group['groupname'], $group['description'], $group['users']);
							} elseif(!in_array($group['id'],$_POST['userman_group']) && in_array($ret['id'],$group['users'])) {
								$group['users'] = array_diff($group['users'], array($ret['id']));
								$userman->updateGroup($group['id'],$group['groupname'], $group['groupname'], $group['description'], $group['users']);
							}
						}
					} else {
						$groups = $userman->getGroupsByID($ret['id']);
						foreach($groups as $gid) {
							$group = $userman->getGroupByGID($gid);
							$group['users'] = array_diff($group['users'], array($ret['id']));
							$userman->updateGroup($group['id'],$group['groupname'], $group['groupname'], $group['description'], $group['users']);
						}
					}
				}
			//Set to none so remove the extension as a default from this user
			//also remove extension from assigned devices, since we probably did it
			} elseif(isset($_REQUEST['userman_assign']) && $_REQUEST['userman_assign'] == 'none') {
				$userO = $userman->getUserByDefaultExtension($extension);
				if(!empty($userO['id'])) {
					//run this last so that hooks to other modules get the correct information
					$userman->updateUser($userO['id'], $userO['username'],$userO['username'],'none');
				}
			}
		break;
		case "del":
			$userO = $userman->getUserByDefaultExtension($extension);
			if(!empty($userO['id'])) {
				//run this last so that hooks to other modules get the correct information
				$userman->updateUser($userO['id'],$userO['username'],$userO['username'],'none');
			}
		break;
	}
	if(!empty($action) && $userman->getAuthName() == "Voicemail") {
		$auth = $userman->getAuthObject();
		if(method_exists($auth,"sync")) {
			//so that it picks up voicemail
			exec("sleep .5 && fwconsole userman sync");
		}
	}
}
