<?php
namespace FreePBX\modules\Userman;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
	private $userman;

	public function runRestore($jobid){
		$configs = $this->getConfigs();
		$this->userman = $this->FreePBX->Userman;

		$this->processData($configs);
	}

	public function processLegacy($pdo, $data, $tables, $unknownTables, $tmpfiledir){
			$tables = array_flip($tables+$unknownTables);
			if(!isset($tables['userman_users'])){
					return $this;
			}
			$this->userman = $this->FreePBX->Userman;

			$configs = [
				'users' => $pdo->query("SELECT * FROM userman_users")->fetchAll(\PDO::FETCH_ASSOC),
				'users_settings' => $pdo->query("SELECT * FROM userman_users_settings")->fetchAll(\PDO::FETCH_ASSOC),
				'groups' => $pdo->query("SELECT * FROM userman_groups")->fetchAll(\PDO::FETCH_ASSOC),
				'groups_settings' => $pdo->query("SELECT * FROM userman_groups_settings")->fetchAll(\PDO::FETCH_ASSOC),
				'directories' => $pdo->query("SELECT * FROM userman_directories")->fetchAll(\PDO::FETCH_ASSOC),
				'kvstore' => []
			];

			$this->processData($configs);

			return $this;
	}

	private function processData($configs){
		foreach($configs['kvstore'] as $id => $entries) {
			foreach($entries as $key => $value) {
				$this->userman->setConfig($key, $value, $id);
			}
		}

		$sth = $this->FreePBX->Database->prepare("REPLACE INTO userman_directories (`id`, `name`, `driver`, `active`, `order`, `default`, `locked`) VALUES (:id, :name, :driver, :active, :order, :default, :locked)");
		foreach($configs['directories'] as $directory) {
			$sth->execute($directory);
		}

		$sth = $this->FreePBX->Database->prepare("REPLACE INTO userman_users (`id`, `auth`, `authid`, `username`, `description`, `password`, `default_extension`, `primary_group`, `permissions`, `fname`, `lname`, `displayname`, `title`, `company`, `department`, `language`, `timezone`, `dateformat`, `timeformat`, `datetimeformat`, `email`, `cell`, `work`, `home`, `fax`) VALUES (:id, :auth, :authid, :username, :description, :password, :default_extension, :primary_group, :permissions, :fname, :lname, :displayname, :title, :company, :department, :language, :timezone, :dateformat, :timeformat, :datetimeformat, :email, :cell, :work, :home, :fax)");
		foreach($configs['users'] as $user) {
			$sth->execute($user);
		}

		$sth = $this->FreePBX->Database->prepare("REPLACE INTO userman_groups (`id`, `auth`, `authid`, `groupname`, `description`, `language`, `timezone`, `dateformat`, `timeformat`, `datetimeformat`, `priority`, `users`, `permissions`, `local`) VALUES (:id, :auth, :authid, :groupname, :description, :language, :timezone, :dateformat, :timeformat, :datetimeformat, :priority, :users, :permissions, :local)");
		foreach($configs['groups'] as $group) {
			$sth->execute($group);
		}

		$sth = $this->FreePBX->Database->prepare("REPLACE INTO userman_users_settings (`uid`, `module`, `key`, `val`, `type`) VALUES (:uid, :module, :key, :val, :type)");
		foreach($configs['users_settings'] as $user) {
			$sth->execute($user);
		}

		$sth = $this->FreePBX->Database->prepare("REPLACE INTO userman_groups_settings (`gid`, `module`, `key`, `val`, `type`) VALUES (:gid, :module, :key, :val, :type)");
		foreach($configs['groups_settings'] as $group) {
			$sth->execute($group);
		}
	}
}
