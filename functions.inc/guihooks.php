<?php
/**
 * This is the User Control Panel Object.
 *
 * Copyright (C) 2013 Schmooze Com, INC
 * Copyright (C) 2013 Andrew Nagy <andrew.nagy@schmoozecom.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   FreePBX UCP BMO
 * @author   Andrew Nagy <andrew.nagy@schmoozecom.com>
 * @license   AGPL v3
 */
function userman_configpageinit($pagename) {
	global $currentcomponent;
	global $amp_conf;

	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$extension = isset($_REQUEST['extension'])?$_REQUEST['extension']:null;
	$tech_hardware = isset($_REQUEST['tech_hardware'])?$_REQUEST['tech_hardware']:null;

    if(version_compare(getVersion(), '12.0', '<') && $pagename == 'userman') {
        $userman = setup_userman();
        $userman->doConfigPageInit($_REQUEST['display']);
    }

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
	$userman = setup_userman();
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
		foreach(core_users_list() as $user) {
			$usersC[] = $user[0];
		}
		if($extdisplay != '') {
			$section = _("User Manager Settings");
			$userM = $userman->getUserByDefaultExtension($extdisplay);
			if(!empty($userM)) {
				$selarray = array(
					array(
						"value" => 'none',
						"text" => _('None')
					),
					array(
						"value" => $userM['id'],
						"text" => $userM['username']
					)
				);
			} else {
				$selarray = array(array(
					"value" => 'none',
					"text" => _('None')
				));
			}
			foreach($userman->getAllUsers() as $user) {
				if($user['default_extension'] != 'none' && in_array($user['default_extension'],$usersC)) {
					continue;
				}
				$selarray[] = array(
						"value" => $user['id'],
						"text" => $user['username']
				);
			}
			if(!empty($userM)) {
				$currentcomponent->addguielem($section, new gui_link('userman|'.$extdisplay, sprintf(_('Linked to User %s'),$userM['username']), '?display=userman&action=showuser&user='.$userM['id']));
				$currentcomponent->addguielem($section, new gui_selectbox('userman|assign', $selarray, $userM['id'], _('Assign to a Different Default User:'), _('Select a user that this extension should be assigned to in User Manager, else select Create New User to have User Manager autogenerate a new user that will be assigned to this extension'), false));
			} else {
				$currentcomponent->addguielem($section, new gui_selectbox('userman|assign', $selarray, '', _('Assign to a Default User:'), _('Select a user that this extension should be assigned to in User Manager, else select Create New User to have User Manager autogenerate a new user that will be assigned to this extension'), false));
			}
		} else {
			$section = _("User Manager Settings");
			$selarray = array(
				array(
					"value" => 'none',
					"text" => _('None')
				),
				array(
					"value" => "add",
					"text" => _('Create New User')
				)
			);
			foreach($userman->getAllUsers() as $user) {
				if($user['default_extension'] != 'none' && in_array($user['default_extension'],$usersC)) {
					continue;
				}
				$selarray[] = array(
						"value" => $user['id'],
						"text" => $user['username']
				);
			}
			$currentcomponent->addjsfunc('usermanPassword()',"if(\$('#userman\\\|assign').val() != 'add') {\$('#userman\\\|password').attr('disabled',true)} else {\$('#userman\\\|password').attr('disabled',false)}");
			$currentcomponent->addguielem($section, new gui_selectbox('userman|assign', $selarray, 'add', _('Assign to a Default User:'), _('Select a user that this extension should be assigned to in User Manager, else select None to have no association to a user'), false, 'frm_extensions_usermanPassword();'));
			$currentcomponent->addguielem($section, new gui_textbox('userman|password',md5(uniqid()), _('Password'), _('If Create New User is selected this will be the autogenerated users new password')));
		}
	} else {
		//unassign all extensions for this user
		foreach($userman->getAllUsers() as $user) {
			$assigned = $userman->getGlobalSettingByID($user['id'],'assigned');
			$assigned = array_diff($assigned, array($extdisplay));
			$userman->setGlobalSettingByID($user['id'],'assigned',$assigned);
		}
	}
}

function userman_configprocess() {
	$action = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	$extension = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$userman = setup_userman();
	//if submitting form, update database
	switch ($action) {
		case "add":
			$extension = isset($_REQUEST['extension']) ? $_REQUEST['extension'] : null;
			if(isset($_REQUEST['userman|assign']) && !empty($extension)) {
				if($_REQUEST['userman|assign'] == 'add') {
					$ret = $userman->addUser($extension, $_REQUEST['userman|password'], $extension, 'Autogenerated user on new user creation');
					if($ret['status']) {
						$userman->setGlobalSettingByID($ret['id'],'assigned',array($extension));
					}
				} elseif($_REQUEST['userman|assign'] != 'none') {
					$user = $userman->getUserByID($_REQUEST['userman|assign']);
					//run this last so that hooks to other modules get the correct information
					$userman->updateUser($user['username'],$user['username'],$extension);
				}
			}
		break;
		case "edit":
			if(isset($_REQUEST['userman|assign']) && $_REQUEST['userman|assign'] != 'none') {
				$userO = $userman->getUserByDefaultExtension($extension);
				if(!empty($userO['id']) && ($userO['id'] != $_REQUEST['userman|assign'])) {
					$assigned = $userman->getGlobalSettingByID($userO['id'],'assigned');
					$assigned = array_diff($assigned, array($extension));
					$userman->setGlobalSettingByID($userO['id'],'assigned',$assigned);
					//run this last so that hooks to other modules get the correct information
					$userman->updateUser($userO['username'],$userO['username'],'none');

					$ret = $userman->getUserByID($_REQUEST['userman|assign']);
					$assigned = $userman->getGlobalSettingByID($ret['id'],'assigned');
					if(is_array($assgined) && !in_array($extension,$assigned)) {
						$assigned[] = $extension;
						$userman->setGlobalSettingByID($ret['id'],'assigned',$assigned);
					} elseif(!is_array($assigned) || empty($assigned)) {
						$userman->setGlobalSettingByID($ret['id'],'assigned',array($extension));
					}
					//run this last so that hooks to other modules get the correct information
					$userman->updateUser($ret['username'],$ret['username'],$extension);
				} elseif(empty($userO['id'])) {
					$user = $userman->getUserByID($_REQUEST['userman|assign']);
					//run this last so that hooks to other modules get the correct information
					$ret = $userman->updateUser($user['username'],$user['username'],$extension);
					if($ret['status']) {
						$userman->setGlobalSettingByID($ret['id'],'assigned',array($extension));
					}
				}
			//Set to none so remove the extension as a default from this user
			//also remove extension from assigned devices, since we probably did it
			} elseif(isset($_REQUEST['userman|assign']) && $_REQUEST['userman|assign'] == 'none') {
				$userO = $userman->getUserByDefaultExtension($extension);
				if(!empty($userO['id'])) {
					$assigned = $userman->getGlobalSettingByID($userO['id'],'assigned');
					$assigned = array_diff($assigned, array($extension));
					$userman->setGlobalSettingByID($userO['id'],'assigned',$assigned);
					//run this last so that hooks to other modules get the correct information
					$userman->updateUser($userO['username'],$userO['username'],'none');
				}
			}
		break;
	}
}
