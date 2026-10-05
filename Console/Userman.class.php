<?php
//Namespace should be FreePBX\Console\Command
namespace FreePBX\Console\Command;

//Symfony stuff all needed add these
use FreePBX;
use Exception;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
//la mesa
use Symfony\Component\Console\Helper\Table;
//Process
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\HelpCommand;
class Userman extends Command {
	protected function configure() {
		$this->setName('userman')
			->setDescription(_('User Manager'))
			->setDefinition([
				new InputArgument('subcommand', InputArgument::OPTIONAL, _('Sub-command (syncdisplaynames)')),
				new InputOption('syncall', null, InputOption::VALUE_NONE, _('Syncronize all directories')),
				new InputOption('sync', null, InputOption::VALUE_REQUIRED, _('Syncronize a single directory by id (obtained from --list)')),
				new InputOption('force', null, InputOption::VALUE_NONE, _('Force syncronization')),
				new InputOption('list', null, InputOption::VALUE_NONE, _('List directories')),
				new InputOption('deletegenerictemplate', null, InputOption::VALUE_NONE, _('Delete generic templates user')),
				new InputOption('from', null, InputOption::VALUE_REQUIRED, _('Display name sync source: userman or extension')),
				new InputOption('dry-run', null, InputOption::VALUE_NONE, _('Preview display name sync changes without modifying records'))
			]);
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		$subcommand = $input->getArgument('subcommand');
		if($subcommand === 'syncdisplaynames') {
			return $this->syncDisplayNames($input, $output);
		}

		$status = [];
  		$force = $input->getOption('force');
		$sync = $input->getOption('sync');
		$userman = FreePBX::create()->Userman;
		if($input->getOption('list')) {
			$table = new Table($output);
			$table->setHeaders([_('ID'), _('Name')]);
			$rows = [];
			$directories = $userman->getAllDirectories();
			foreach($directories as $directory) {
				$rows[] = [$directory['id'], $directory['name']];
			}
			$table->setRows($rows);
			$table->render();
		}
		if($input->getOption('syncall') && $input->getOption('sync')) {
			$output->writeln("<error>Can not sync and syncall at the same time!</error>");
			exit(-1);
		}
		if($input->getOption('syncall')) {
			$this->setLock();
			$directories = $userman->getAllDirectories();
			foreach($directories as $directory) {
				$this->syncDirectory($directory,$output,$force);
			}
			$this->removeLock();
		}
		if($input->getOption('deletegenerictemplate')) {
			try {
				$status = $userman->deletetemplatecreator();
			} catch(Exception $e) {
				$output->writeln("\t<error>".$e->getMessage()."</error>");
				$output->writeln("\t Already Deleted ");
			}
			if($status['status']){
				$output->writeln("Removed the Generic Template User");
			}
			exit(-1);
		}
		if($input->getOption('sync')) {
			$this->setLock();
			$id = $input->getOption('sync');
			$directory = $userman->getDirectoryByID($id);
			$this->syncDirectory($directory,$output,$force);
			$this->removeLock();
		}
		if(!$input->getOption('syncall') && !$input->getOption('sync') && !$input->getOption('list') && !$input->getOption('deletegenerictemplate')) {
			$this->outputHelp($input,$output);
			exit(4);
		}
	}

	/**
	 * @return int
	 */
	private function syncDisplayNames(InputInterface $input, OutputInterface $output) {
		$from = $input->getOption('from');
		$dryRun = (bool)$input->getOption('dry-run');
		$userman = FreePBX::create()->Userman;

		if($dryRun && empty($from)) {
			try {
				$stats = $userman->previewDisplayNameDifferences();
			} catch (Exception $e) {
				$output->writeln('<error>'.$e->getMessage().'</error>');
				return Command::FAILURE;
			}

			$output->writeln('<info>'._('Display name differences (no records modified)').'</info>');
			if(!empty($stats['differences'])) {
				$table = new Table($output);
				$table->setHeaders([_('Username'), _('Extension'), _('User Manager Display Name'), _('Extension Display Name')]);
				$rows = [];
				foreach($stats['differences'] as $diff) {
					$rows[] = [$diff['username'], $diff['extension'], $diff['userman_displayname'], $diff['extension_displayname']];
				}
				$table->setRows($rows);
				$table->render();
			} else {
				$output->writeln(_('No display name differences found.'));
			}

			$output->writeln(sprintf(_('Total records scanned: %d'), $stats['scanned']));
			$output->writeln(sprintf(_('Total records with differences: %d'), count($stats['differences'])));
			$output->writeln(sprintf(_('Total records skipped: %d'), $stats['skipped']));
			$output->writeln(sprintf(_('Errors encountered: %d'), count($stats['errors'])));
			foreach($stats['errors'] as $error) {
				$output->writeln('<error>'.$error.'</error>');
			}

			return empty($stats['errors']) ? Command::SUCCESS : Command::FAILURE;
		}

		if(empty($from)) {
			$output->writeln('<error>'._('The --from option is required when not using --dry-run. Use --from=userman or --from=extension').'</error>');
			return Command::FAILURE;
		}

		try {
			$stats = $userman->bulkSyncDisplayNames($from, $dryRun);
		} catch (Exception $e) {
			$output->writeln('<error>'.$e->getMessage().'</error>');
			return Command::FAILURE;
		}

		if($dryRun) {
			$output->writeln('<info>'._('Dry run mode: no records were modified.').'</info>');
		}

		$output->writeln(sprintf(_('Sync direction: %s -> %s'), $from, $from === 'userman' ? 'extension' : 'userman'));
		$output->writeln(sprintf(_('Total records scanned: %d'), $stats['scanned']));
		$output->writeln(sprintf(_('Total records updated: %d'), $stats['updated']));
		$output->writeln(sprintf(_('Total records skipped: %d'), $stats['skipped']));
		$output->writeln(sprintf(_('Errors encountered: %d'), count($stats['errors'])));
		foreach($stats['errors'] as $error) {
			$output->writeln('<error>'.$error.'</error>');
		}

		return empty($stats['errors']) ? Command::SUCCESS : Command::FAILURE;
	}

	private function syncDirectory($directory,$output,$force=false) {
		if(isset($directory)) {
			$userman = FreePBX::create()->Userman;
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
					if(\FreePBX::Config()->get("USERMAN_ACCOUNT_CODE")) {
                	                        $output->writeln("Updating account codes...");
                        	                $userman->updateUserAccountCodes($directory['id']);
                                	        $output->writeln("Done updating account codes.");
        	                        }
					$displayNameStats = $userman->syncDisplayNamesAfterDirectorySync($directory['id']);
					if($displayNameStats !== null) {
						$output->writeln(sprintf(_("Updating display names... %d updated, %d skipped"), $displayNameStats['updated'], $displayNameStats['skipped']));
						foreach($displayNameStats['errors'] as $error) {
							$output->writeln("\t<error>".$error."</error>");
						}
						$output->writeln(_("Done updating display names."));
					}
					$output->writeln("Finished");
				} else {
					$output->writeln("Not syncing directory for another ".(($timeSince + $secondsSince)-$timeNow)." seconds");
				}
	
			} else {
				$output->writeln("Directory '".$directory['name']."' does not support syncing");
			}
		} else {
			$output->writeln("Directory not found");
		}
	}

	private function setLock() {
		$ASTRUNDIR = FreePBX::Config()->get("ASTRUNDIR");
		$lock = $ASTRUNDIR."/userman.lock";

		if(!$this->checkLock()) {
			$pid = getmypid();
			file_put_contents($lock,$pid);
			return true;
		} else {
			$pid = file_get_contents($lock);
			throw new Exception("User Manager is already syncing (Process: ".$pid.")");
		}
		return false;
	}

	private function checkLock() {
		$ASTRUNDIR = FreePBX::Config()->get("ASTRUNDIR");
		$lock = $ASTRUNDIR."/userman.lock";
		if(file_exists($lock)) {
			$pid = file_get_contents($lock);
			if(posix_getpgid($pid) !== false) {
				return true;
			} else {
				$this->removeLock();
			}
		}
		return false;
	}

	private function removeLock() {
		$ASTRUNDIR = FreePBX::Config()->get("ASTRUNDIR");
		$lock = $ASTRUNDIR."/userman.lock";
		if(file_exists($lock)) {
			unlink($lock);
		}
		return true;
	}

	/**
  * @return int
  * @throws ExceptionInterface
  */
 protected function outputHelp(InputInterface $input, OutputInterface $output)	 {
		$help = new HelpCommand();
		$help->setCommand($this);
		return $help->run($input, $output);
	}
}
