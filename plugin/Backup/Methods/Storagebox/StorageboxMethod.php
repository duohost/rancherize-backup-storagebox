<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox;

use Rancherize\Blueprint\Infrastructure\Infrastructure;
use Rancherize\Blueprint\Infrastructure\InfrastructureWriter;
use Rancherize\Blueprint\Infrastructure\Service\Service;
use Rancherize\Blueprint\Infrastructure\Volume\Volume;
use Rancherize\Configuration\Configuration;
use Rancherize\File\FileWriter;
use Rancherize\RancherAccess\HealthStateMatcher;
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
use RancherizeBackupStoragebox\Storagebox\AccessMethods\Factory\AccessMethodFactory;
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
	 * @param BuildService $buildService
	 * @param RancherService $rancherService
	 * @param InfrastructureWriter $infrastructureWriter
	 */
	public function __construct(StorageboxRepository $repository, AccessMethodFactory $methodFactory,
							BuildService $buildService, RancherService $rancherService,
							InfrastructureWriter $infrastructureWriter
	) {
		$this->repository = $repository;
		$this->methodFactory = $methodFactory;

		$this->collectors = [
			container(EnvironmentConfigCollector::class),
			container(RancherAccountCollector::class ),
			container(DockerComposeCollector::class),
			container(DockerComposeVersionCollector::class),
			container(ServiceCollector::class),
			container(SidekickCollector::class),
			container(RootPasswordCollector::class),
			container( SstPasswordCollector::class ),
			container( NamedVolumeCollector::class ),
		];

		$this->modifiers = [
			container(FilterSidekicksModifier::class),
			container(ServiceNameModifier::class),
			container(SidekickNameModifier::class),
			container(VolumesFromNameModifier::class),
			container(ScaleDownModifier::class),
			container(VolumeNameModifier::class),
			container(VolumesEntryModifier::class),
			container(ContainerNetModifier::class),
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
	 * @param Backup $backup
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	public function restore(string $environment, Database $database, Backup $backup, InputInterface $input, OutputInterface $output) {

		$backupKey = $backup->getKey();

		$data = new StorageboxData();
		$data->setBackup($backup);
		$data->setEnvironmentName($environment);
		$data->setDatabase($database);
		$data->setBackupKey($backupKey);
		$data->setConfiguration($this->configuration);

		foreach ($this->collectors as $collector) {
			if($collector instanceof  RequiresQuestionHelper)
				$collector->setQuestionHelper($this->questionHelper);

			$collector->collect($input, $output, $data);
		}

		$dockerCompose = [
			'version' => '2',
			'services' => array_merge(
				[$data->getBackup()->getServiceName() => $data->getService()],
				$data->getSidekicks()
			)
		];
		$rancherCompose = $data->getRancherData();

		// TODO: Allow to set as option. If not set: ask user
		$regex = '~$~';
		$replacement = '-'.$backupKey;

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
		$stackName = $data->getBackup()->getStackName();
		$newServiceName = $data->getNewServiceName();
		$output->writeln("Starting $newServiceName.");
		$this->rancherService->start($workDirectory, $stackName);
		$output->writeln("Waiting for $newServiceName to start.");
		$this->rancherService->wait($stackName, $newServiceName, new SingleStateMatcher('active') );
		$output->writeln("$newServiceName Started.");
		$output->writeln("Stopping $newServiceName.");
		$this->rancherService->stop($workDirectory, $stackName);
		$output->writeln("Waiting for $newServiceName to stop.");
		$this->rancherService->wait($stackName, $newServiceName, new SingleStateMatcher('inactive') );
		$output->writeln("$newServiceName Stopped.");

		$clearService = new Service();
		$clearService->setName($data->getNewServiceName().'-clear');
		$clearService->setImage('ipunktbs/xtrabackup:0.2.1');
		$clearService->setCommand('clear yes');
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

		$output->writeln("Starting ".$clearService->getName().".");
		$this->rancherService->start($workDirectory, $stackName);
		$output->writeln("Waiting for ".$clearService->getName()." to finish.");
		$this->rancherService->wait($stackName, $clearService->getName(), new HealthStateMatcher('started-once') );
		$output->writeln($clearService->getName()." finished.");
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

		$output->writeln("Starting ".$volumeCreateService->getName().".");
		$this->rancherService->start($workDirectory, $stackName);
		$output->writeln("Waiting for ".$volumeCreateService->getName()." to finish.");
		$this->rancherService->wait($stackName, $volumeCreateService->getName(), new HealthStateMatcher('started-once') );
		$output->writeln($volumeCreateService->getName()." finished.");

		/*
		 * TODO: Move to restore service
		 */

		$restoreService = new Service();
		$restoreService->setImage('ipunktbs/xtrabackup:0.2.1');
		$restoreService->setName($data->getNewServiceName().'-restore');
		$restoreService->setCommand('restore '.$backupKey);
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
		$output->writeln("Starting ".$restoreService->getName().".");
		$this->rancherService->start($workDirectory, $stackName);
		$output->writeln("Waiting for ".$restoreService->getName()." to finish.");
		$this->rancherService->wait($stackName, $restoreService->getName(), new HealthStateMatcher('started-once') );
		$output->writeln($restoreService->getName()." finished.");
		/*
		 * TODO: /Move to restore service
		 */

		// TODO: move to builder service and just call again
		$dockerFileContent = Yaml::dump($dockerCompose, 100, 2);
		$this->buildService->createDockerCompose($dockerFileContent);

		$rancherFileContent = Yaml::dump($rancherCompose, 100, 2);
		$this->buildService->createRancherCompose($rancherFileContent);

		$output->writeln("Starting $newServiceName.");
		$this->rancherService->start($workDirectory, $stackName);

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
		$pmaService->addLabel('io.rancher.scheduler.affinity:container_label', "io.rancher.stack_service.name=${stackName}/${newServiceName}");

		$pmaInfrastructure = new Infrastructure();
		$pmaInfrastructure->addService($pmaService);
		$pmaInfrastructure->addVolume($volume);
		$pmaInfrastructure->addVolume($backupVolume);

		$this->infrastructureWriter->setPath($workDirectory)
			->setSkipClear(false)
			->write($pmaInfrastructure, new FileWriter());
		$output->writeln("Starting ".$pmaService->getName().".");
		$this->rancherService->start($workDirectory, $stackName);

		$rmInfrastructure = new Infrastructure();
		$commandServices = [
			$clearService,
			$restoreService,
			$volumeCreateService
		];
		$commandServiceNames = [];
		foreach($commandServices as $commandService) {
			$rmInfrastructure->addService($commandService);
			$commandServiceNames[] = $commandService->getName();
		}
		$rmInfrastructure->addVolume($volume);
		$rmInfrastructure->addVolume($backupVolume);

		$this->infrastructureWriter->setPath($workDirectory)
			->setSkipClear(false)
			->write($rmInfrastructure, new FileWriter());

		$output->writeln("Starting cleanup up command services ".implode(', ', $commandServiceNames).'.');
		$this->rancherService->rm($workDirectory, $stackName, $commandServiceNames);

		$output->writeln("Waiting for pma to become active");
		$this->rancherService->wait($stackName, $newServiceName, new SingleStateMatcher('active') );
		if( array_key_exists('pma-url', $backupData) )
			$output->writeln( [
				"PMA is active. You may use it on: ".$backupData['pma-url'].'.',
				'Note that it may take around 2 minutes before a load balancer notices the new pma service.',
			]);
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