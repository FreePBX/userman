<?php
namespace FreePBX\modules\Userman;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
    $configs = $this->getConfigs();
    $userman = $this->FreePBX->Userman;
    foreach ($configs['directories'] as $dir) {
        if($userman->getDirectoryByID($dir['id']) !== false){
            $userman->updateDirectory($dir['id'], $dir['name'], $dir['active'], $dir['config']);
            continue;
        }
        $userman->addDirectory($dir['driver'], $dir['name'], $dir['active'], $dir['config']);
    }
    if($configs['defaultdirectory']){
        $userman->setDefaultDirectory($configs['defaultdirectory']);
    }
    $userman->bulkhandlerImport('usermangroups', $configs['usermangroups'], true);
    $userman->bulkhandlerImport('usermanusers', $configs['usermanusers'], true);
  }
}