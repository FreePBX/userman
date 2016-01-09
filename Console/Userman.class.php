<?php
//Namespace should be FreePBX\Console\Command
namespace FreePBX\Console\Command;

//Symfony stuff all needed add these
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
//Tables
use Symfony\Component\Console\Helper\TableHelper;
//Process
use Symfony\Component\Process\Process;
class Userman extends Command {
  protected function configure(){
    $this->setName('userman')
      ->setDescription(_('User Manager'))
      ->setDefinition(array(
        new InputArgument('args', InputArgument::IS_ARRAY, null, null),));
  }
  protected function execute(InputInterface $input, OutputInterface $output){
    $args = $input->getArgument('args');
    $command = isset($args[0])?$args[0]:'';
		$userman = \FreePBX::create()->Userman;
		switch ($command) {
      case "sync":
        $auth = $userman->getAuthObject();
        if(method_exists($auth,"sync")) {
          $output->write("Starting Sync...");
          $auth->sync();
          $output->writeln("Finished");
        } else {
          $output->writeln("<comment>The active authentication driver does not support syncing.</comment>");
        }
      break;
	    default:
	      $output->writeln("<error>The command provided is not valid.</error>");
        $output->writeln("Avalible commands are:");
        $output->writeln("<info>sync</info> - Syncronize User/Group information for an authentication engine");
	      exit(4);
	    break;
    }
  }
}
