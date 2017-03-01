<?php namespace RancherizeBackupStoragebox\Storagebox\Service;

use Rancherize\Commands\Traits\IoTrait;
use Rancherize\Configuration\Configurable;
use Rancherize\Configuration\Configuration;
use Rancherize\Configuration\Traits\EnvironmentConfigurationTrait;
use RancherizeBackupStoragebox\Backup\Backup;
use RancherizeBackupStoragebox\Backup\Exceptions\BackupException;
use RancherizeBackupStoragebox\Backup\Factory\BackupMethodFactory;
use RancherizeBackupStoragebox\Database\Repository\DatabaseRepository;
use RancherizeBackupStoragebox\General\Helper\RequiresProcessHelper;
use RancherizeBackupStoragebox\General\Helper\RequiresQuestionHelper;
use Symfony\Component\Console\Helper\ProcessHelper;
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
	 * @var ProcessHelper
	 */
	private $processHelper;

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
	 * @param string $backupKey
	 * @param Configurable|Configuration $configuration
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	public function restore(string $environment, string $backupKey, Configurable $configuration, InputInterface $input, OutputInterface $output) {
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

		$backup = $this->assertBackupExists($backupKey, $backups);
		$backupName = $backup->getName();

		$output->writeln("Restoring Backup $backupKey => $backupName using $backupMethod.");

		if($method instanceof RequiresQuestionHelper)
			$method->setQuestionHelper($this->questionHelper);
		if($method instanceof RequiresProcessHelper)
			$method->setProcessHelper($this->processHelper);

		$method->restore($environment, $database, $backupKey, $input, $output);
	}

	/**
	 * @param string $backupKey
	 * @param Backup[] $backups
	 */
	private function assertBackupExists($backupKey, $backups) {
		foreach($backups as $backup) {
			if($backup->getKey() === $backupKey)
				return $backup;
		}

		throw new BackupException("Backup $backupKey does not exist in the List of backups.");
	}

	/**
	 * @param ProcessHelper $processHelper
	 */
	public function setProcessHelper(ProcessHelper $processHelper) {
		$this->processHelper = $processHelper;
	}

}