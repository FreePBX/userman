<?php
namespace FreePBX\modules\Userman;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{

	public function runRestore(){
		$configs = $this->getConfigs();

		if ( array_key_exists('kvstore',  $configs) ) { $this->importKVStore($configs['kvstore']); }
		if ( array_key_exists('settings', $configs) ) { $this->importAdvancedSettings($configs['settings']); }
		
		if ( array_key_exists('modulexml', $configs) )
		{
			// Recovery > Email Settings
			$this->log(_("Importing Module XML userman"));
			$sql = "REPLACE INTO module_xml (`id`, `data`) VALUES('userman_data', ?)";
			$sth = $this->FreePBX->Database->prepare($sql);
			$sth->execute(array(json_encode($configs['modulexml'])));
		}

		$this->processData($configs['usermantables']);
	}

	public function processLegacy($pdo, $data, $tables, $unknownTables){
		$version = 14;
		if(version_compare_freepbx($this->getVersion(),"13","lt") && version_compare_freepbx($this->getVersion(),"12","gt")) {
			$version = 12;
		}
		if(version_compare_freepbx($this->getVersion(),"12","lt")) {
			$version = 10;//super legacy 11,10
		}
		$defaultDir = false;
		if($version < 14) {
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

			if ($table == 'userman_template_settings' || $table == 'userman_ucp_templates') {
				$this->addDataToTableFromArray($table, $datas);
			}
		}
		$this->FreePBX->Userman->getUnlockKeyTemplateCreator();
		$this->log(_("created TemplateCreator "));
	}
}