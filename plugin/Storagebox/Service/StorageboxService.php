<?php namespace RancherizeBackupStoragebox\Storagebox\Service;

use Rancherize\Commands\Traits\IoTrait;
use Rancherize\Commands\Traits\RancherTrait;
use Rancherize\Configuration\Configurable;
use Rancherize\Configuration\Configuration;
use Rancherize\Configuration\PrefixConfigurableDecorator;
use Rancherize\Configuration\Traits\EnvironmentConfigurationTrait;
use Rancherize\Docker\DockerComposeParser\NotFoundException;
use Rancherize\Docker\DockerComposeReader\DockerComposeReader;
use Rancherize\Docker\DockerComposerVersionizer;
use Rancherize\RancherAccess\RancherAccessService;
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
	use RancherTrait;
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
	 * @var DockerComposeReader
	 */
	private $composeReader;
	/**
	 * @var DockerComposerVersionizer
	 */
	private $composerVersionizer;

	/**
	 * StorageboxService constructor.
	 * @param DatabaseRepository $databaseRepository
	 * @param BackupMethodFactory $methodFactory
	 * @param DockerComposeReader $composeReader
	 * @param DockerComposerVersionizer $composerVersionizer
	 */
	public function __construct(DatabaseRepository $databaseRepository, BackupMethodFactory $methodFactory,
							DockerComposeReader $composeReader, DockerComposerVersionizer $composerVersionizer) {
		$this->databaseRepository = $databaseRepository;
		$this->methodFactory = $methodFactory;
		$this->composeReader = $composeReader;
		$this->composerVersionizer = $composerVersionizer;
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

		$rancherService = $this->getRancher();
		$rancherConfiguration = new RancherAccessService($configuration);
		$rancherAccount = $rancherConfiguration->getAccount( $environmentConfig->get('rancher.account') );
		$rancherService->setAccount($rancherAccount);

		$databaseStack = $database->getStack();
		$databaseService = $database->getService();

		list($currentConfig, $currentRancherize) = $rancherService->retrieveConfig($databaseStack);
		$composeData = $this->composeReader->read($currentConfig);
		$composeVersion = $this->composerVersionizer->parse($composeData);
		try {
			$service = $composeVersion->getService($databaseService, $composeData);
		} catch(NotFoundException $e) {
			$output->writeln("Restore failed: stack ${databaseService} was not found within ${databaseStack}");
			return;
		}

		var_dump($service);
	}

}