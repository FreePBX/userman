<?php
namespace FreePBX\modules\Userman;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{

	public function runRestore($jobid){
		$configs = $this->getConfigs();
		$this->importKVStore($configs['kvstore']);
		$this->importTables($configs['tables']);
	}

	public function processLegacyKvstore($pdo, $data, $tables, $unknownTables){
		$this->restoreLegacyDatabase($pdo);
	}
}
