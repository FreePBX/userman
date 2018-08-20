<?php
namespace FreePBX\modules\Userman;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
  public function runBackup($id,$transaction){
    $userman = $this->FreePBX->Userman();
    $configs = [
        'usermanusers' => $userman->bulkhandlerExport('usermanusers'),
        'usermangroups' => $userman->bulkhandlerExport('usermanugroups'),
        'directories' => $userman->getAllDirectories(),
        'defaultdirectory' => $userman->getDefaultDirectory()
    ];
    $this->addDirectories($dirs);
    $this->addDependency('');
    $this->addConfigs($configs);
  }
}