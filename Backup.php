<?php
namespace FreePBX\modules\Userman;
use FreePBX;
use FreePBX\modules\Backup as Base;
class Backup Extends Base\BackupBase{
  public function runBackup($id,$transaction){
    $tables    = $this->dumpTables();
    $kvstore   = $this->dumpKVStore();
    $settings  = $this->dumpAdvancedSettings();

    // Backup > Email Settings
    $this->log(_("Exporting Module XML userman"));
    $userman = FreePBX::Userman();
    $modulexml = $userman->getGlobalsettings();

    $configs   = [
        'usermantables' => $tables,
        'kvstore'       => $kvstore,
        'settings'      => $settings,
        'modulexml'     => $modulexml
    ];
    $this->addDependency('');
    $this->addConfigs($configs);
  }
}
