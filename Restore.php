<?php
namespace FreePBX\modules\Userman;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{

	public function runRestore(){
		$configs = $this->getConfigs();
		$this->processData($configs['usermantables']);
	}

	public function processLegacy($pdo, $data, $tables, $unknownTables){
		$version = true;
		$defaultDir = false;
		if(version_compare_freepbx($this->getVersion(),"13","lt")) {
			$version = false;
			$directory = $this->FreePBX->Userman->getDefaultDirectory();
			$defaultDir = $directory['id'];
		}
		$usermandata = $this->FreePBX->Userman->dumpData($pdo,$version);
		$this->log(_("Processing Legacy Userman tables"));
		$this->processData($usermandata,$defaultDir);
		return $this;
	}

	public function processData($usermantables, $defaultDir = false){
		foreach ($usermantables as $table => $datas) {
			if ($table == 'userman_directories' || $table == 'userman_users') {
				if($defaultDir) {
					$addDir = array("auth" => $defaultDir);
					$datawithDir = array();
					foreach ($datas as $data) {
						$datawithDir[] = array_merge($data,$addDir);
					}
				}
				if($defaultDir) {
					$this->addDataToTableFromArray($table,$datawithDir);
				}
				else {
					$this->addDataToTableFromArray($table,$datas);
				}
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
