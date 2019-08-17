<?php
namespace FreePBX\modules\Userman;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{

	public function runRestore(){
		$configs = $this->getConfigs();
		$this->processData($configs['usermantables']);
	}

	public function processLegacy($pdo, $data, $tables, $unknownTables){
		$usermandata = $this->FreePBX->Userman->dumpData($pdo);
		$this->log(_("Processing Legacy Userman tables"));
		$this->processData($usermandata);
		return $this;
	}

	public function processData($usermantables){
		foreach ($usermantables as $table => $datas) {
			if ($table == 'userman_directories' || $table == 'userman_users') {
				$this->addDataToTableFromArray($table,$datas);
			}

			if ($table == 'userman_groups') {
				$cleandata = [];
				foreach($datas as $row) {
					$row['users'] =  stripslashes($row['users']);
					$cleandata[] = $row;
				}
				$this->addDataToTableFromArray($table,$cleandata);
			}

			if ($table == 'userman_groups_settings' || $table == 'userman_users_settings') {
				unset($cleandata);
				$cleandata = [];
				foreach($datas as $row) {
					if ($row['type'] == 'json-arr') {
						$row['val'] = stripslashes($row['val']);
					}
					$cleandata[] = $row;
				}
				$this->addDataToTableFromArray($table,$cleandata);
			}
		}
	}
}
