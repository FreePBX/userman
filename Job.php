<?php

namespace FreePBX\modules\Userman;
use FreePBX\Job\TaskInterface;
use FreePBX;
use Exception;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
class Job implements TaskInterface {
	public static function run(InputInterface $input, OutputInterface $output) {
		$userman = FreePBX::create()->Userman;
		$directories = $userman->getAllDirectories();
		foreach($directories as $directory) {
			self::syncDirectory($userman, $directory,$output);
		}
		return true;
	}

	public static function syncDirectory($userman, $directory,$output) {
		$force = null;
  		if(!$directory['active']) {
			$output->writeln("Directory '".$directory['name']."' is not active. Skipping");
			return;
		}
		$dir = $userman->getDirectoryObjectByID($directory['id']);
		if(method_exists($dir,"sync")) {
			if(!$force && empty($directory['config']['sync'])) {
				$output->writeln("Directory '".$directory['name']."' sync is None. Skipping (Unless --force flag is set)");
				return;
			}
			$timeSince = $userman->getConfig("directory-last-sync-time");
			$timeSince = !empty($timeSince) ? $timeSince : 0;
			$timeNow = time();
			$secondsSince = 0;
			switch($directory['config']['sync']) {
				case "*/15 * * * *":
					$secondsSince = 900;
				break;
				case "*/30 * * * *":
					$secondsSince = 1800;
				break;
				case "0 * * * *":
					$secondsSince = 3600;
				break;
				case "0 */6 * * *":
					$secondsSince = 21600;
				break;
				case "0 0 * * *":
					$secondsSince = 86400;
				break;
			}
			if($force || ($timeNow > ($timeSince + $secondsSince))) {
				$userman->setConfig("directory-last-sync-time", $timeNow);
				$output->writeln("Starting Sync on directory '".$directory['name']."'...");
				$userman->lockDirectory($directory['id']);
				try {
					$dir->sync($output);
				} catch(Exception $e) {
					$output->writeln("\t<error>".$e->getMessage()."</error>");
				}
				$userman->unlockDirectory($directory['id']);
				$output->writeln("Finished");
			} else {
				$output->writeln("Not syncing directory for another ".(($timeSince + $secondsSince)-$timeNow)." seconds");
			}

		} else {
			$output->writeln("Directory '".$directory['name']."' does not support syncing");
		}
	}
}
