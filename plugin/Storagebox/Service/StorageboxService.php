<?php namespace RancherizeBackupStoragebox\Storagebox\Service;

use Rancherize\Commands\Traits\IoTrait;
use Rancherize\Configuration\Configuration;
use RancherizeBackupStoragebox\Backup\Factory\BackupMethodFactory;
use RancherizeBackupStoragebox\Database\Repository\DatabaseRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StorageboxService
 * @package RancherizeBackupStoragebox\Storagebox\Service
 */
class StorageboxService {

	use IoTrait;
	/**
	 * @var DatabaseRepository
	 */
	private $databaseRepository;
	/**
	 * @var BackupMethodFactory
	 */
	private $methodFactory;

	/**
	 * StorageboxService constructor.
	 * @param DatabaseRepository $databaseRepository
	 * @param BackupMethodFactory $methodFactory
	 */
	public function __construct(DatabaseRepository $databaseRepository, BackupMethodFactory $methodFactory) {
		$this->databaseRepository = $databaseRepository;
		$this->methodFactory = $methodFactory;
	}

	/**
	 * @param Configuration $configuration
	 * @param string $environment
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	public function list(Configuration $configuration, string $environment, InputInterface $input, OutputInterface $output) {
		$this->setIo($input, $output);

		$this->databaseRepository->setConfiguration($configuration);

		$databaseName = $configuration->get('project.environments'.$environment.'.database.global');
		if($databaseName == null) {
			$output->writeln("Global Database not set for $environment, can not have a backup configuration.");
			return;
		}

		$database = $this->databaseRepository->find($databaseName);
		if($database->getBackupData() === null) {
			$output->writeln("No backup set for Database $databaseName.");
			return;
		}

		$backupData = $database->getBackupData();
		$backupMethod = $backupData['method'];

		$output->writeln("Environment $environment uses backup method $backupMethod");
		$method = $this->methodFactory->make($backupMethod, $backupData);
		$method->setConfiguration($configuration);

		$backups = $method->list();
		foreach($backups as $backup) {
			$key = $backup->getKey();
			$name = $backup->getName();

			$output->writeln("'$key' => '$name'");
		}
	}
}