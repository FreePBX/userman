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
    $this->processData($userman, $configs);
  }

  public function processLegacy($pdo, $data, $tables, $unknownTables, $tmpfiledir){
      $tables = array_flip($tables+$unknownTables);
      if(!isset($tables['userman_users'])){
          return $this;
      }
      $bmo = $this->FreePBX->Userman;
      $bmo->setDatabase($pdo);
        $configs = [
            'usermanusers' => $bmo->bulkhandlerExport('usermanusers'),
            'usermangroups' => $bmo->bulkhandlerExport('usermangroups'),
            'directories' => $bmo->getAllDirectories(),
            'defaultdirectory' => $bmo->getDefaultDirectory()
        ];
      $bmo->resetDatabase();
      $this->processData($bmo, $configs);

      return $this;
  }
  public function processData($userman,$configs){
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
