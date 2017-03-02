<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox;

use Rancherize\Blueprint\Infrastructure\Infrastructure;
use Rancherize\Blueprint\Infrastructure\InfrastructureWriter;
use Rancherize\Blueprint\Infrastructure\Service\Service;
use Rancherize\Blueprint\Infrastructure\Volume\Volume;
use Rancherize\Configuration\Configuration;
use Rancherize\Docker\DockerComposeReader\DockerComposeReader;
use Rancherize\Docker\DockerComposerVersionizer;
use Rancherize\Docker\RancherComposeReader\RancherComposeReader;
use Rancherize\File\FileWriter;
use Rancherize\General\Services\ByKeyService;
use Rancherize\General\Services\NameIsPathChecker;
use Rancherize\RancherAccess\RancherService;
use Rancherize\RancherAccess\SingleStateMatcher;
use Rancherize\Services\BuildService;
use RancherizeBackupStoragebox\Backup\Backup;
use RancherizeBackupStoragebox\Backup\BackupMethod;
use RancherizeBackupStoragebox\Backup\Exceptions\ConfigurationNotFoundException;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\ContainerNetModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\FileModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\FilterSidekicksModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\RequiresReplacementRegex;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\ScaleDownModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\ServiceNameModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\SidekickNameModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\VolumeNameModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\VolumesEntryModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\VolumesFromNameModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\DockerComposeCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\DockerComposeVersionCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\EnvironmentConfigCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\InformationCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\NamedVolumeCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\RancherAccountCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\RootPasswordCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\ServiceCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\SidekickCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\SstPasswordCollector;
use RancherizeBackupStoragebox\Database\Database;
use RancherizeBackupStoragebox\General\Helper\RequiresProcessHelper;
use RancherizeBackupStoragebox\General\Helper\RequiresQuestionHelper;
use RancherizeBackupStoragebox\Storagebox\AccessMethods\AccessMethodFactory;
use RancherizeBackupStoragebox\Storagebox\Repository\StorageboxRepository;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class StorageboxMethod
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox
 */
class StorageboxMethod implements BackupMethod, RequiresQuestionHelper, RequiresProcessHelper {

	/**
	 * @var Configuration
	 */
	private $configuration;
	/**
	 * @var StorageboxRepository
	 */
	private $repository;
	/**
	 * @var AccessMethodFactory
	 */
	private $methodFactory;

	/**
	 * @var ByKeyService
	 */
	private $byKeyService;

	/**
	 * @var QuestionHelper
	 */
	private $questionHelper;

	/**
	 * @var InformationCollector[]
	 */
	private $collectors = [];

	/**
	 * @var FileModifier[]
	 */
	private $modifiers = [];
	/**
	 * @var BuildService
	 */
	private $buildService;
	/**
	 * @var RancherService
	 */
	private $rancherService;

	/**
	 * @var ProcessHelper
	 */
	private $processHelper;
	/**
	 * @var InfrastructureWriter
	 */
	private $infrastructureWriter;

	/**
	 * StorageboxMethod constructor.
	 * @param StorageboxRepository $repository
	 * @param AccessMethodFactory $methodFactory
	 * @param DockerComposeReader $composeReader
	 * @param RancherComposeReader $rancherReader
	 * @param DockerComposerVersionizer $composerVersionizer
	 * @param ByKeyService $byKeyService
	 * @param BuildService $buildService
	 * @param RancherService $rancherService
	 * @param NameIsPathChecker $nameIsPathChecker
	 * @param InfrastructureWriter $infrastructureWriter
	 */
	public function __construct(StorageboxRepository $repository, AccessMethodFactory $methodFactory,
							DockerComposeReader $composeReader, RancherComposeReader $rancherReader,
							DockerComposerVersionizer $composerVersionizer,
							ByKeyService $byKeyService, BuildService $buildService, RancherService $rancherService,
							NameIsPathChecker $nameIsPathChecker,
							// TODO: Move populating the collectors and modifiers outside into the container function -> dependencies don't have to pass through here
							InfrastructureWriter $infrastructureWriter
	) {
		$this->repository = $repository;
		$this->methodFactory = $methodFactory;
		$this->byKeyService = $byKeyService;

		$this->collectors = [
			new EnvironmentConfigCollector(),
			new RancherAccountCollector(),
			new DockerComposeCollector($composeReader, $rancherReader),
			new DockerComposeVersionCollector($composerVersionizer),
			new ServiceCollector(),
			new SidekickCollector(),
			new RootPasswordCollector($byKeyService),
			new SstPasswordCollector($byKeyService),
			new NamedVolumeCollector(),
		];

		$this->modifiers = [
			new FilterSidekicksModifier(),
			new ServiceNameModifier(),
			new SidekickNameModifier(),
			new VolumesFromNameModifier(),
			new ScaleDownModifier(),
			new VolumeNameModifier($nameIsPathChecker),
			new VolumesEntryModifier(),
			new ContainerNetModifier(),
		];
		$this->buildService = $buildService;
		$this->rancherService = $rancherService;
		$this->infrastructureWriter = $infrastructureWriter;
	}

	/**
	 * @var string
	 */
	protected $storageBox;

	/**
	 * @param Configuration $configuration
	 */
	public function setConfiguration(Configuration $configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * @return Backup[]
	 */
	public function list() {
		$this->repository->setConfiguration($this->configuration);

		$storageBox = $this->repository->find( $this->storageBox );

		$method = $this->methodFactory->make( $storageBox->getMethod(), $storageBox->getAccessData() );

		return $method->list( );
	}

	/**
	 * @param string $storageBox
	 */
	public function setStorageBox(string $storageBox) {
		$this->storageBox = $storageBox;
	}

	/**
	 * @param string $environment
	 * @param Database $database
	 * @param string $backup
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	public function restore(string $environment, Database $database, string $backup, InputInterface $input, OutputInterface $output) {

		$data = new StorageboxData();
		$data->setEnvironmentName($environment);
		$data->setDatabase($database);
		$data->setBackupKey($backup);
		$data->setConfiguration($this->configuration);

		foreach ($this->collectors as $collector) {
			if($collector instanceof  RequiresQuestionHelper)
				$collector->setQuestionHelper($this->questionHelper);

			$collector->collect($input, $output, $data);
		}

		$dockerCompose = [
			'version' => '2',
			'services' => array_merge(
				[$data->getDatabase()->getService() => $data->getService()],
				$data->getSidekicks()
			)
		];
		$rancherCompose = $data->getRancherData();

		// TODO: Allow to set as option. If not set: ask user
		$regex = '~$~';
		$replacement = '-'.$backup;

		foreach($this->modifiers as $modifier) {
			if($modifier instanceof RequiresReplacementRegex)
				$modifier->setReplacementRegex($regex, $replacement);

			$modifier->modify($dockerCompose, $rancherCompose, $data);
		}

		$dockerFileContent = Yaml::dump($dockerCompose, 100, 2);
		$this->buildService->createDockerCompose($dockerFileContent);

		$rancherFileContent = Yaml::dump($rancherCompose, 100, 2);
		$this->buildService->createRancherCompose($rancherFileContent);

		$this->rancherService->setAccount( $data->getRancherAccount() )
			->setOutput( $output )
			->setProcessHelper( $this->processHelper );
		$workDirectory = getcwd() . '/.rancherize';
		$this->rancherService->start($workDirectory, $data->getDatabase()->getStack());
		$this->rancherService->stop($workDirectory, $data->getDatabase()->getStack());

		$clearService = new Service();
		$clearService->setName($data->getNewServiceName().'-clear');
		$clearService->setImage('ipunktbs/xtrabackup:0.2.1');
		$clearService->setCommand('clear yes');
		$stackName = $data->getDatabase()->getStack();
		$newServiceName = $data->getNewServiceName();
		$newDataSidekick = $data->getNewMysqlVolumeService();
		$newMysqlVolume = $data->getNewMysqlVolumeName();

		/*
		 * TODO: Move to clear service
		 */
		$clearService->setRestart(Service::RESTART_START_ONCE);
		// Start on the same server as
		$clearService->addLabel('io.rancher.scheduler.affinity:container_label', "io.rancher.stack_service.name=${stackName}/${newServiceName}/${newDataSidekick}");
		// No 2 services on the same host
		$clearService->addLabel('io.rancher.scheduler.affinity:container_label_ne', 'io.rancher.stack_service.name=$${stack_name}/$${service_name}');
		$clearService->addVolume($newMysqlVolume, '/var/lib/mysql');
		$volume = new Volume();
		$volume->setName($newMysqlVolume);
		$volume->setDriver('local');

		$clearInfrastructure = new Infrastructure();
		$clearInfrastructure->addService($clearService);
		$clearInfrastructure->addVolume($volume);

		$this->infrastructureWriter->setPath($workDirectory)
			->setSkipClear(false)
			->write($clearInfrastructure, new FileWriter());
		$this->rancherService->start($workDirectory, $data->getDatabase()->getStack());
		$this->rancherService->wait($stackName, $clearService->getName(), new SingleStateMatcher('started-once') );
		/*
		 * TODO: /Move to clear service
		 */

		$backupData = $database->getBackupData();
		if( !array_key_exists('volume', $backupData) )
			throw new ConfigurationNotFoundException('backup.volume');
		if( !array_key_exists('volume-driver', $backupData) )
			throw new ConfigurationNotFoundException('backup.volume');
		$backupVolumeName = $backupData['volume'];
		$backupVolumeDriver = $backupData['volume-driver'];

		/*
		 * Ensure backup volume is available
		 */
		$volumeCreateInfrastructure = new Infrastructure();
		$volumeCreateService = new Service();
		$volumeCreateService->setName($data->getNewServiceName().'-volume');
		$volumeCreateService->setImage('area51/docker-client');
		$volumeCreateService->setCommand("docker volume create --driver=${backupVolumeDriver} --name=${backupVolumeName}");
		$volumeCreateService->addLabel('io.rancher.scheduler.affinity:container_label', "io.rancher.stack_service.name=${stackName}/${newServiceName}/${newDataSidekick}");
		$volumeCreateService->addLabel('io.rancher.scheduler.affinity:container_label_ne', 'io.rancher.stack_service.name=$${stack_name}/$${service_name}');
		$volumeCreateService->addVolume('/var/run/docker.sock', '/var/run/docker.sock');
		$volumeCreateService->setRestart(Service::RESTART_START_ONCE);
		$volumeCreateInfrastructure->addService($volumeCreateService);
		$this->infrastructureWriter->setPath($workDirectory)
			->setSkipClear(false)
			->write($volumeCreateInfrastructure, new FileWriter());
		$this->rancherService->start($workDirectory, $data->getDatabase()->getStack());
		$this->rancherService->wait($stackName, $volumeCreateService->getName(), new SingleStateMatcher('started-once') );

		/*
		 * TODO: Move to restore service
		 */

		$restoreService = new Service();
		$restoreService->setImage('ipunktbs/xtrabackup:0.2.1');
		$restoreService->setName($data->getNewServiceName().'-restore');
		$restoreService->setCommand('restore '.$backup);
		$restoreService->setRestart(Service::RESTART_START_ONCE);
		// Start on the same server as
		$restoreService->addLabel('io.rancher.scheduler.affinity:container_label', "io.rancher.stack_service.name=${stackName}/${newServiceName}/${newDataSidekick}");
		// No 2 services on the same host
		$restoreService->addLabel('io.rancher.scheduler.affinity:container_label_ne', 'io.rancher.stack_service.name=$${stack_name}/$${service_name}');
		$restoreService->addVolume( $newMysqlVolume, '/var/lib/mysql' );
		$restoreService->addVolume( $backupVolumeName, '/target' );

		$backupVolume = new Volume();
		$backupVolume->setName( $backupVolumeName );
		$backupVolume->setDriver( $backupVolumeDriver );
		$backupVolume->setExternal( true );

		$restoreInfrastructure = new Infrastructure();
		$restoreInfrastructure->addService($restoreService);
		$restoreInfrastructure->addVolume($volume);
		$restoreInfrastructure->addVolume($backupVolume);

		$this->infrastructureWriter->setPath($workDirectory)
			->setSkipClear(false)
			->write($restoreInfrastructure, new FileWriter());
		$this->rancherService->start($workDirectory, $data->getDatabase()->getStack());
		$this->rancherService->wait($stackName, $restoreService->getName(), new SingleStateMatcher('started-once') );
		/*
		 * TODO: /Move to restore service
		 */

		// TODO: move to builder service and just call again
		$dockerFileContent = Yaml::dump($dockerCompose, 100, 2);
		$this->buildService->createDockerCompose($dockerFileContent);

		$rancherFileContent = Yaml::dump($rancherCompose, 100, 2);
		$this->buildService->createRancherCompose($rancherFileContent);

		$this->rancherService->start($workDirectory, $data->getDatabase()->getStack());

		// TODO: add PMA
		$pmaService = new Service();
		$pmaService->setImage('phpmyadmin/phpmyadmin:4.6');
		$pmaService->setName($data->getNewServiceName().'-pma');
		$pmaService->addLabel('pma', 'backup');
		$pmaService->addExternalLink("${stackName}/${newServiceName}", 'db');
		// Workaround for the phpmyadmin bug https://github.com/phpmyadmin/docker/issues/23
		$pmaService->setEnvironmentVariable('PMA_HOST', 'db.rancher.internal');
		$pmaService->setEnvironmentVariable('PMA_USER', 'root');
		$pmaService->setEnvironmentVariable('PMA_PASSWORD', $data->getRootPassword());

		$pmaInfrastructure = new Infrastructure();
		$pmaInfrastructure->addService($pmaService);
		$pmaInfrastructure->addVolume($volume);
		$pmaInfrastructure->addVolume($backupVolume);

		$this->infrastructureWriter->setPath($workDirectory)
			->setSkipClear(false)
			->write($pmaInfrastructure, new FileWriter());
		$this->rancherService->start($workDirectory, $data->getDatabase()->getStack());
	}

	/**
	 * @param $questionHelper
	 */
	public function setQuestionHelper(QuestionHelper $questionHelper) {
		$this->questionHelper = $questionHelper;
	}

	/**
	 * @param ProcessHelper $processHelper
	 */
	public function setProcessHelper(ProcessHelper $processHelper) {
		$this->processHelper = $processHelper;
	}
}