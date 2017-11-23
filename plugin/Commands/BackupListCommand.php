<?php namespace RancherizeBackupStoragebox\Commands;

use Rancherize\Configuration\Traits\LoadsConfigurationTrait;
use RancherizeBackupStoragebox\Storagebox\Service\StorageboxService;
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

	/**
	 * @var StorageboxService
	 */
	private $storageboxService;

	/**
	 * BackupListCommand constructor.
	 * @param StorageboxService $storageboxService
	 */
	public function __construct( StorageboxService $storageboxService ) {
		$this->storageboxService = $storageboxService;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('backup:list')
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

		$this->storageboxService->list($configuration, $environment, $input, $output);
	}


}