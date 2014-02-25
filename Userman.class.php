<?php
// vim: set ai ts=4 sw=4 ft=php:
/**
 * This is the FreePBX Ucp Object, a subset of BMO.
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
 * @package   FreePBX UCP
 * @author    Andrew Nagy <andrew.nagy@schmoozecom.com>
 * @license   AGPL v3
 */

class Userman implements BMO {
	private $message = '';
	private $userTable = 'freepbx_users';
	private $userSettingsTable = 'freepbx_users_settings';
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			//TODO: 2.11 work here
		} else {
			$this->FreePBX = $freepbx;
			$this->db = $freepbx->Database;
		}
	}
	
    function &create() {
		static $obj;
		if (!isset($obj) || !is_object($obj)) {
			$obj = new Userman();
		}
		return $obj;
    }
	
	public function install() {
		
	}
	public function uninstall() {
		
	}
	public function backup(){
		
	}
	public function restore($backup){
		
	}
	public function genConfig() {

	}
	
	public function writeConfig($conf){
	}
	
	public function doConfigPageInit($display) {
		if(isset($_POST['submit'])) {
			$username = !empty($_POST['username']) ? $_POST['username'] : '';
			$password = !empty($_POST['password']) ? $_POST['password'] : '';
			$prevUsername = !empty($_POST['prevUsername']) ? $_POST['prevUsername'] : '';
			if(empty($password)) {
				$this->message = array(
					'message' => _('The Password Can Not Be blank!'),
					'type' => 'danger'
				);
				return false;
			}
			if(!empty($username) && empty($prevUsername)) {
				$ret = $this->addUser($username,$password);
				if(!$ret['status']) {
					$this->message = array(
						'message' => $ret['message'],
						'type' => $ret['type']
					);
				} else {
					$this->message = array(
						'message' => $ret['message'],
						'type' => $ret['type']
					);
				}
			} elseif(!empty($username) && !empty($prevUsername)) {
				$password = ($password != '******') ? $password : null;
				$ret = $this->updateUser($prevUsername, $username, $password);
				if(!$ret['status']) {
					$this->message = array(
						'message' => $ret['message'],
						'type' => $ret['type']
					);
				} else {
					$this->message = array(
						'message' => $ret['message'],
						'type' => $ret['type']
					);
				}
			} else {
				$this->message = array(
					'message' => _('Username Can Not Be Blank'),
					'type' => 'danger'
				);
				return false;
			}
		}
	}
	
	public function myShowPage() {
		$category = !empty($_REQUEST['category']) ? $_REQUEST['category'] : '';
		$html = '';
		$html .= load_view(dirname(__FILE__).'/views/header.php',array());
		
		$users = $this->getAllUsers();
		
		$html .= load_view(dirname(__FILE__).'/views/rnav.php',array("users"=>$users));
		switch($category) {
			default:
				if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'showuser' && !empty($_REQUEST['user'])) {
					$user = $this->getUserByID($_REQUEST['user']);
				} else {
					$user = array();
				}
				$html .= load_view(dirname(__FILE__).'/views/users.php',array("user" => $user, "message" => $this->message));
			break;
		}
		$html .= load_view(dirname(__FILE__).'/views/footer.php',array());
		
		return $html;
	}
	
	public function getAllUsers() {
		$sql = "SELECT * FROM ".$this->userTable;
		$users = $this->db->query($sql,PDO::FETCH_ASSOC);
		return $users;
	}
	
	public function getUserByUsername($username) {
		$sql = "SELECT * FROM ".$this->userTable." WHERE username = :username";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':username' => $username));
		$user = $sth->fetch(PDO::FETCH_ASSOC);
		return $user;
	}
	
	public function getUserByID($id) {
		$sql = "SELECT * FROM ".$this->userTable." WHERE id = :id";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':id' => $id));
		$user = $sth->fetch(PDO::FETCH_ASSOC);
		return $user;
	}
	
	public function deleteUser($username) {
		$user = $this->getUserByUsername($username);
		if(!$user) {
			return array("status" => false, "type" => "danger", "message" => _("User Does Not Exist"));
		}
		$sql = "DELETE FROM ".$this->userTable." WHERE `username` = :username";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':username' => $username));
		
		$sql = "DELETE FROM ".$this->userSettingsTable." WHERE `uid` = :uid";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':uid' => $user['id']));
		return array("status" => true, "type" => "success", "message" => _("User Successfully Deleted"));
	}
	
	public function addUser($username, $password, $encrypt = true) {
		if($this->getUserByUsername($username)) {
			return array("status" => false, "type" => "danger", "message" => _("User Already Exists"));
		}
		$sql = "INSERT INTO ".$this->userTable." (`username`,`password`) VALUES (:username,:password)";
		$sth = $this->db->prepare($sql);
		$password = ($encypt) ? sha1($password) : $password;
		$sth->execute(array(':username' => $username, ':password' => $password));
		return array("status" => true, "type" => "success", "message" => _("User Successfully Added"));
	}
	
	public function updateUser($prevUsername, $username, $password=null) {
		$user = $this->getUserByUsername($prevUsername);
		if(!$user || empty($user)) {
			return array("status" => false, "type" => "danger", "message" => _("User Does Not Exist"));
		}
		if(!isset($password)) {
			if($prevUsername != $username) {
				$sql = "UPDATE ".$this->userTable." SET `username` = :username WHERE `username` = :prevusername";
				$sth = $this->db->prepare($sql);
				$sth->execute(array(':username' => $username, ':prevusername' => $prevUsername));
			} else {
				return array("status" => true, "type" => "info", "message" => _("Nothing Changed, Did you mean that?"));
			}
		} else {
			if(sha1($password) != $user['password']) {
				$sql = "UPDATE ".$this->userTable." SET `username` = :username, `password` = :password WHERE `username` = :prevusername";
				$sth = $this->db->prepare($sql);
				$sth->execute(array(':username' => $username, ':prevusername' => $prevUsername, ':password' => sha1($password)));	
			} else {
				return array("status" => true, "type" => "info", "message" => _("Nothing Changed, Did you mean that?"));
			}
		}
		
		//if username and/or password changed then clear the UCP sessions for this user (which will force a logout)
		if($prevUsername != $username || (isset($password) || sha1($password) != $user['password'])) {
			$this->expireUserSessions($user['id']);
		}
		
		return array("status" => true, "type" => "success", "message" => _("User Successfully Updated"));
	}
	
	public function checkCredentials($username, $password_sha1) {
		$sql = "SELECT id, password FROM ".$this->userTable." WHERE username = :username";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':username' => $username));
		$result = $sth->fetch(\PDO::FETCH_ASSOC);
		if(!empty($result) && ($password_sha1 == $result['password'])) {
			return $result['id'];
		}
		return false;
	}
	
	public function getAssignedDevices() {
		
	}
	
	public function getAllGlobalSettings($username) {
		$sql = "SELECT a.val, a.type, a.key FROM ".$this->userSettingsTable." a, ".$this->userTable." b WHERE b.id = a.uid AND b.username = :username AND a.module is null";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':username' => $username));
		$result = $sth->fetch(\PDO::FETCH_ASSOC);
		if($result) {
			$fout = array();
			foreach($result as $res) {
				$fout[$res['key']] = ($result['type'] == 'json-arr' && $this->isJson($result['type'])) ? json_decode($result['type'],true) : $result;
			}
			return $fout;
		}
		return false;
	}
	
	public function getGlobalSetting($username,$setting) {
		$sql = "SELECT a.val, a.type FROM ".$this->userSettingsTable." a, ".$this->userTable." b WHERE b.id = a.uid AND b.username = :username AND a.key = :setting AND a.module is null";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':username' => $username, ':setting' => $setting));
		$result = $sth->fetch(\PDO::FETCH_ASSOC);
		if($result) {
			return ($result['type'] == 'json-arr' && $this->isJson($result['val'])) ? json_decode($result['val'],true) : $result['val'];
		}
		return false;
	}
	
	public function setGlobalSetting($username,$setting,$value) {
		$type = is_array($value) ? 'json-arr' : null;
		$value = is_array($value) ? json_encode($value) : $value;
		$user = $this->getUserByUsername($username);
		$sql = "REPLACE INTO ".$this->userSettingsTable." (`uid`, `module`, `key`, `val`, `type`) VALUES(:uid, :module, :setting, :value, :type)";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':uid' => $user['id'], ':module' => null, ':setting' => $setting, ':value' => $value, ':type' => $type));	
	}
	
	public function getAllModuleSettings($username,$module) {
		$sql = "SELECT a.val, a.type, a.key FROM ".$this->userSettingsTable." a, ".$this->userTable." b WHERE b.id = a.uid AND b.username = :username AND a.module = :module";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':username' => $username, ':module' => $module));
		$result = $sth->fetch(\PDO::FETCH_ASSOC);
		if($result) {
			$fout = array();
			foreach($result as $res) {
				$fout[$res['key']] = ($result['type'] == 'json-arr' && $this->isJson($result['val'])) ? json_decode($result['val'],true) : $result['val'];
			}
			return $fout;
		}
		return false;
	}
	
	public function getModuleSetting($username,$module,$setting) {
		$sql = "SELECT a.val, a.type FROM ".$this->userSettingsTable." a, ".$this->userTable." b WHERE b.id = a.uid AND b.username = :username AND a.module = :module AND a.key = :setting";
		$sth = $this->db->prepare($sql);
		$sth->execute(array(':username' => $username, ':setting' => $setting, ':module' => $module));
		$result = $sth->fetch(\PDO::FETCH_ASSOC);
		if($result) {
			return ($result['type'] == 'json-arr' && $this->isJson($result['type'])) ? json_decode($result['type'],true) : $result;
		}
		return false;
	}
	
	public function setModuleSetting($username,$module,$setting,$value) {
		
	}
	
	function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
}