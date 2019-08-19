<?php
namespace FreePBX\modules\Userman;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
  public function runBackup($id,$transaction){
    $tables = $this->dumpTables();
    $configs = [
        'usermantables' => $tables
    ];
    $this->addDependency('');
    $this->addConfigs($configs);
  }
}
