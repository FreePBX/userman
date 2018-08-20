<?php
namespace FreePBX\modules\Userman;
use FreePBX\modules\Backup as Base;
class Restore Extends Base\RestoreBase{
  public function runRestore($jobid){
    $configs = reset($this->getConfigs());
    $userman = $this->FreePBX->Userman;
    $configs['directories'] = is_array($configs['directories'])? $configs['directories']:[];
    $configs['usermanusers'] = is_array($configs['usermanusers'])? $configs['usermanusers']:[];
    $configs['usermangroups'] = is_array($configs['usermangroups'])? $configs['usermangroups']:[];
    $this->processData($configs);
  }

  public function processLegacy($pdo, $data, $tables, $unknownTables, $tmpfiledir){
      $tables = array_flip($tables+$unknownTables);
      if(!isset(tables['userman_users'])){
          return $this;
      }
      $bmo = $this->FreePBX->Userman;
      $bmo->setDatabase($pdo);
        $configs = [
            'usermanusers' => $userman->bulkhandlerExport('usermanusers'),
            'usermangroups' => $userman->bulkhandlerExport('usermanugroups'),
            'directories' => $userman->getAllDirectories(),
            'defaultdirectory' => $userman->getDefaultDirectory()
        ];
      $bmo->resetDatabase();
      $configs = reset($configs);
      $this->processData($configs);

      return $this;
  }
  public function processData($usermnan,$configs){
        foreach ($configs['directories'] as $dir) {
            if ($userman->getDirectoryByID($dir['id']) !== false) {
                $userman->updateDirectory($dir['id'], $dir['name'], $dir['active'], $dir['config']);
                continue;
            }
            $userman->addDirectory($dir['driver'], $dir['name'], $dir['active'], $dir['config']);
        }
        if ($configs['defaultdirectory']) {
            $userman->setDefaultDirectory($configs['defaultdirectory']);
        }
        $userman->bulkhandlerImport('usermangroups', $configs['usermangroups'], true);
        $userman->bulkhandlerImport('usermanusers', $configs['usermanusers'], true);
    }
  }

}
