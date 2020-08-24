<?php
namespace FreePBX\modules\Userman;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
  public function runBackup($id,$transaction){
    $tables = $this->dumpTables();
    $kvstore = $this->dumpKVStore();
    $configs = [
        'usermantables' => $tables,
        'kvstore'       => $kvstore
    ];
    $this->addDependency('');
    $this->addConfigs($configs);
  }
}
