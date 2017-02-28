<?php namespace RancherizeBackupStoragebox\Storagebox\Service;

use Rancherize\Commands\Traits\IoTrait;
use Rancherize\Configuration\Configurable;
use Rancherize\Configuration\Configuration;
use Rancherize\Configuration\Traits\EnvironmentConfigurationTrait;
use RancherizeBackupStoragebox\Backup\Backup;
use RancherizeBackupStoragebox\Backup\Factory\BackupMethodFactory;
use RancherizeBackupStoragebox\Database\Repository\DatabaseRepository;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StorageboxService
 * @package RancherizeBackupStoragebox\Storagebox\Service
 */
class StorageboxService {

	use IoTrait;
	use EnvironmentConfigurationTrait;

	/**
	 * @var DatabaseRepository
	 */
	private $databaseRepository;
	/**
	 * @var BackupMethodFactory
	 */
	private $methodFactory;

	/**
	 * @var QuestionHelper
	 */
	private $questionHelper;

	/**
	 * StorageboxService constructor.
	 * @param DatabaseRepository $databaseRepository
	 * @param BackupMethodFactory $methodFactory
	 */
	public function __construct(DatabaseRepository $databaseRepository, BackupMethodFactory $methodFactory
	) {
		$this->databaseRepository = $databaseRepository;
		$this->methodFactory = $methodFactory;
	}

	/**
	 * @param QuestionHelper $questionHelper
	 */
	public function setQuestionHelper(QuestionHelper $questionHelper) {
		$this->questionHelper = $questionHelper;
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

		$databaseName = $configuration->get('project.environments.'.$environment.'.database.global');
		if($databaseName == null) {
			$output->writeln("Global Database not set for $environment, can not have a restore configuration.");
			return;
		}

		$database = $this->databaseRepository->find($databaseName);
		if($database->getBackupData() === null) {
			$output->writeln("No restore set for Database $databaseName.");
			return;
		}

		$backupData = $database->getBackupData();
		$backupMethod = $backupData['method'];

		$output->writeln("Environment $environment uses restore method $backupMethod");
		$method = $this->methodFactory->make($backupMethod, $backupData);
		$method->setConfiguration($configuration);

		$backups = $method->list();
		usort($backups, function(Backup $a, Backup $b) {
			return strcmp($a->getKey(), $b->getKey());
		});
		foreach($backups as $backup) {
			$key = $backup->getKey();
			$name = $backup->getName();

			$output->writeln("'$key' => '$name'");
		}
	}

	/**
	 * @param string $environment
	 * @param string $backup
	 * @param Configurable|Configuration $configuration
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	public function restore(string $environment, string $backup, Configurable $configuration, InputInterface $input, OutputInterface $output) {
		$environmentConfig = $this->environmentConfig($configuration, $environment);
		$globalDatabaseName = $environmentConfig->get('database.global', null);

		if($globalDatabaseName === null) {
			$output->writeln("Global Database not set for $environment, can not have a restore configuration.");
			return;
		}

		$this->databaseRepository->setConfiguration($configuration);
		$database = $this->databaseRepository->find($globalDatabaseName);
		if($database->getBackupData() === null) {
			$output->writeln("No restore set for Database $globalDatabaseName.");
			return;
		}

		$backupData = $database->getBackupData();
		$backupMethod = $backupData['method'];

		$output->writeln("Environment $environment uses restore method $backupMethod");
		$method = $this->methodFactory->make($backupMethod, $backupData);
		$method->setConfiguration($configuration);
		$backups = $method->list();

		if(!array_key_exists($backup, $backups)) {
			$output->writeln("Backup $backup does not exist in the backup list returned by the backup method $backupMethod.");
			return;
		}
		$backupName = $backups[$backup]->getName();

		$output->writeln("Restoring Backup $backup => $backupName using $backupMethod.");
		$method->setQuestionHelper($this->questionHelper);
		$method->restore($environment, $database, $backup, $input, $output);
	}

}