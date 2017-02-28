<?php namespace RancherizeBackupStoragebox\Commands;

use Rancherize\Configuration\Traits\LoadsConfigurationTrait;
use RancherizeBackupStoragebox\Storagebox\Traits\UsesStorageboxService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BackupRestoreCommand
 * @package RancherizeBackupStoragebox\Commands
 */
class BackupRestoreCommand extends Command {

	use LoadsConfigurationTrait;
	use UsesStorageboxService;

	/**
	 *
	 */
	protected function configure() {
		$this
			->setName('backup:restore')
			->setDescription('restore a previously created restore')
			->setHelp('Clones the database service with a fresh named volume for /var/lib/mysql, then populates this named volume with the restore given as [restore].')
			->addArgument('environment', InputArgument::REQUIRED, 'The environment for which the restore should be restored')
			->addArgument('restore', InputArgument::REQUIRED, 'The restore to restore')
			;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {

		$environment = $input->getArgument('environment');
		$backup = $input->getArgument('restore');

		$configuration = $this->loadConfiguration();
		$storageboxService = $this->getStorageboxService();
		$storageboxService->setQuestionHelper( $this->getHelper('question') );
		$storageboxService->restore($environment, $backup, $configuration, $input, $output);

		return 0;
	}


}