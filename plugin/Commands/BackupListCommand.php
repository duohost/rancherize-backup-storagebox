<?php namespace RancherizeBackupStoragebox\Commands;

use Rancherize\Configuration\Traits\LoadsConfigurationTrait;
use RancherizeBackupStoragebox\Storagebox\Traits\UsesStorageboxService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BackupListCommand
 * @package RancherizeBackupStoragebox\Commands
 */
class BackupListCommand extends Command {

	use LoadsConfigurationTrait;
	use UsesStorageboxService;

	protected function configure() {
		$this
			->setName('restore:list')
			->setDescription('List available backups.')
			->setHelp('Connects to the hetzner storagebox and lists the available backups stored there.')
			->addArgument('environment', InputArgument::REQUIRED)
		;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$configuration = $this->loadConfiguration();
		$environment = $input->getArgument('environment');

		$storageboxService = $this->getStorageboxService();
		$storageboxService->list($configuration, $environment, $input, $output);
	}


}