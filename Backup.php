<?php
namespace FreePBX\modules\Userman;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
	public function runBackup($id,$transaction){
		$userman = $this->FreePBX->Userman();
		$configs = [
				'users' => $this->FreePBX->Database->query("SELECT * FROM userman_users")->fetchAll(\PDO::FETCH_ASSOC),
				'users_settings' => $this->FreePBX->Database->query("SELECT * FROM userman_users_settings")->fetchAll(\PDO::FETCH_ASSOC),
				'groups' => $this->FreePBX->Database->query("SELECT * FROM userman_groups")->fetchAll(\PDO::FETCH_ASSOC),
				'groups_settings' => $this->FreePBX->Database->query("SELECT * FROM userman_groups_settings")->fetchAll(\PDO::FETCH_ASSOC),
				'directories' => $this->FreePBX->Database->query("SELECT * FROM userman_directories")->fetchAll(\PDO::FETCH_ASSOC),
				'kvstore' => $this->FreePBX->Userman->getAll(null)
		];
		$this->addConfigs($configs);
	}
}