<?php

/**
 * This is the User Control Panel Object.
 *
 * Copyright (C) 2022 Sangoma Communications
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
 * @author   James Finstrom <jfinstrom@sangoma.com>
 * @license   AGPL v3
 */
//Namespace needs to always be this
namespace UCP\Modules;
//Use UCP\Modules as Modules for simplicity
use \UCP\Modules as Modules;
//Module class should always extend Modules
class Userman extends Modules
{
	//Always declare the module name here
	protected $module = 'Userman';

	public function __construct($Modules)
	{
		//User information. Returned as an array. See:
		$this->user = $this->UCP->User->getUser();
		//Asterisk Manager. See: https://wiki.freepbx.org/display/FOP/Asterisk+Manager+Class
		$this->astman = $this->UCP->FreePBX->astman;
		$this->userman = $this->UCP->FreePBX->Userman;
		//Setting retrieved from the UCP Interface in User Manager in Admin
		$this->enabled = $this->UCP->getCombinedSettingByID($this->user['id'], $this->module, 'enabled');
	}

	/**
	 * Ajax Request
	 * @method ajaxRequest
	 * @link https://wiki.freepbx.org/display/FOP/BMO+Ajax+Calls#BMOAjaxCalls-ajaxRequest
	 * @param  string      $command  The command name
	 * @param  array      $settings Returned array settings
	 * @return boolean                True if allowed or false if not allowed
	 */
	public function ajaxRequest($command, $settings)
	{
		switch ($command) {
			case 'checkPasswordReminder':
				$setting['authenticate'] = false;
				return true;
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * Ajax Handler
	 * @method ajaxHandler
	 * @link https://wiki.freepbx.org/display/FOP/BMO+Ajax+Calls#BMOAjaxCalls-ajaxHandler
	 * @return mixed      Data to return to Javascript
	 */
	public function ajaxHandler()
	{
		switch ($_REQUEST['command']) {
			case 'checkPasswordReminder':
				return $this->userman->pwdExpReminder()->checkPasswordReminder($_REQUEST);
				break;
			default:
				return false;
				break;
		}
	}
}
