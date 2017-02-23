<?php namespace RancherizeBackupStoragebox\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BackupListCommand
 * @package RancherizeBackupStoragebox\Commands
 */
class BackupListCommand extends Command {

	protected function configure() {
		$this
			->setName('backup:list')
			->setDescription('List available backups.')
			->setHelp('Connects to the hetzner storagebox and lists the available backups stored there.')
		;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		echo "Hi!";
	}


}