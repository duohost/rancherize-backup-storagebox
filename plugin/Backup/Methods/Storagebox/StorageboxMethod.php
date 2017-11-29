<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox;

use Rancherize\Blueprint\Healthcheck\HealthcheckConfigurationToService\HealthcheckConfigurationToService;
use Rancherize\Blueprint\Infrastructure\Infrastructure;
use Rancherize\Blueprint\Infrastructure\InfrastructureWriter;
use Rancherize\Blueprint\Infrastructure\Service\Service;
use Rancherize\Blueprint\Infrastructure\Service\Volume;
use Rancherize\Blueprint\PublishUrls\PublishUrlsParser\PublishUrlsParser;
use Rancherize\Blueprint\Scheduler\SchedulerParser\SchedulerParser;
use Rancherize\Configuration\ArrayConfiguration;
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
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\NewNamesCollector;
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
			container(SstPasswordCollector::class ),
			container(NamedVolumeCollector::class ),
			container(NewNamesCollector::class ),
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

		$this->rancherService->setAccount( $data->getRancherAccount() )
			->setOutput( $output )
			->setProcessHelper( $this->processHelper );

		$restoreService = $this->startRestoreService($data, $backupKey, $database, $output);

		$this->startNewService($restoreService, $data, $backupKey, $output);

		$this->startPmaService($data, $output);

		$commandServices = [
			$restoreService,
		];
		$this->removeCommandServices($data, $commandServices, $output);

		$backupData = $database->getBackupData();
		if( array_key_exists('pma-url', $backupData) )
			$output->writeln( [
				"PMA is active. You may use it on: ".$backupData['pma-url'].'.'
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

	/**
	 * @param StorageboxData $data
	 * @return Volume
	 */
	private function makeVolume(StorageboxData $data) {

		$mySqlVolume = new Volume();
		$mySqlVolume->setExternalPath( $data->getNewMysqlVolumeName() );
		$mySqlVolume->setInternalPath('/var/mysql');
		$mySqlVolume->setDriver( 'local' );

		return $mySqlVolume;
	}

	/**
	 * @param Database $database
	 * @return Volume
	 */
	private function makeBackupVolume( Database $database) {
		$backupVolume = new Volume();

		$backupData = $database->getBackupData();

		if( !array_key_exists('volume', $backupData) )
			throw new ConfigurationNotFoundException('backup.volume');
		if( !array_key_exists('volume-driver', $backupData) )
			throw new ConfigurationNotFoundException('backup.volume-driver');

		$backupVolumeName = $backupData['volume'];
		$backupVolumeDriver = $backupData['volume-driver'];

		$backupVolume->setExternalPath( $backupVolumeName );
		$backupVolume->setInternalPath('/target');
		$backupVolume->setDriver( $backupVolumeDriver );

		return $backupVolume;
	}

	/**
	 * Start the restore service
	 *
	 * @param StorageboxData $data
	 * @param string $backupKey
	 * @param Database $database
	 * @param OutputInterface $output
	 * @return Service
	 */
	private function startRestoreService( StorageboxData $data, $backupKey, Database $database, OutputInterface $output) {
		$stackName = $data->getBackup()->getStackName();

		$restoreService = new Service();
		$restoreService->setImage('ipunktbs/xtrabackup:gvvs');
		$restoreService->setName('restore-'.$backupKey);
		$restoreService->setCommand('restore '.$backupKey);
		$restoreService->setRestart(Service::RESTART_START_ONCE);

		$mysqlVolume = $this->makeVolume($data);
		$backupVolume = $this->makeBackupVolume($database);
		$restoreService->addVolume( $mysqlVolume );
		$restoreService->addVolume( $backupVolume );

		/**
		 * @var SchedulerParser $schedulerParser
		 */
		//$schedulerParser = container(SchedulerParser::class);
		$schedulerParser = container('scheduler-parser');
		$config = new ArrayConfiguration([
			'scheduler' => [
				'should-have-tags' => [ 'primary-restore' ]
			]
		]);
		$schedulerParser->parse($restoreService, $config);


		$restoreInfrastructure = new Infrastructure();
		$restoreInfrastructure->addService($restoreService);

		$this->infrastructureWriter->setPath( $this->getWorkDirectory() )
			->setSkipClear(false)
			->write($restoreInfrastructure, new FileWriter());
		$output->writeln("Starting ".$restoreService->getName().".");
		$this->rancherService->start( $this->getWorkDirectory(), $stackName);
		$output->writeln("Waiting for ".$restoreService->getName()." to finish.");
		$this->rancherService->wait($stackName, $restoreService->getName(), new HealthStateMatcher('started-once') );
		$output->writeln($restoreService->getName()." finished.");

		return $restoreService;
	}

	/**
	 * Start the new service
	 * @param Service $restoreService
	 * @param StorageboxData $data
	 * @param $backupKey
	 * @param OutputInterface $output
	 */
	private function startNewService(Service $restoreService,StorageboxData $data, $backupKey, OutputInterface $output) {
		$stackName = $data->getBackup()->getStackName();
		$restoreServiceName = $restoreService->getName();

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

		// Start on the same server as the restore service
		$compose = &$dockerCompose;
		foreach([
			        'services',
			        $data->getNewServiceName(),
			        'labels',
		        ] as $key) {
			if( !array_key_exists($key, $compose) )
				$compose[$key] = [];
			$compose = &$compose[$key];
		}
		$dockerCompose['services'][$data->getNewServiceName()]['labels']['io.rancher.scheduler.affinity:container_label_soft'] = "io.rancher.stack_service.name=${stackName}/${restoreServiceName}";

		$dockerFileContent = Yaml::dump($dockerCompose, 100, 2);
		$this->buildService->createDockerCompose($dockerFileContent);

		$rancherFileContent = Yaml::dump($rancherCompose, 100, 2);
		$this->buildService->createRancherCompose($rancherFileContent);

		$stackName = $data->getBackup()->getStackName();
		$newServiceName = $data->getNewServiceName();
		$output->writeln("Starting $newServiceName.");
		$this->rancherService->start( $this->getWorkDirectory(), $stackName);
		$output->writeln("Waiting for $newServiceName to start.");
		$this->rancherService->wait($stackName, $newServiceName, new SingleStateMatcher('active') );
		$output->writeln("$newServiceName Started.");
	}

	private function startPmaService(StorageboxData $data, OutputInterface $output) {
		$stackName = $data->getBackup()->getStackName();
		$newServiceName = $data->getNewServiceName();
		$backupData = $data->getDatabase()->getBackupData();

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

		if( array_key_exists('pma-url', $backupData) ) {
			$pmaUrl = $backupData['pma-url'];

			$pmaDomain = parse_url($pmaUrl, PHP_URL_HOST);
			$pmaScheme = parse_url($pmaUrl, PHP_URL_SCHEME);
			$publishUrl = $pmaScheme.'://'.$pmaDomain;

			$output->writeln( [
				"PMA is active. You may use it on: ".$backupData['pma-url'].'.'
			]);
			/**
			 * @var HealthcheckConfigurationToService $healthcheck
			 */
			$healthcheckConfig = [
				'healthcheck' => [
					'port' => 80
				]
			];
			$healthcheck = container('healthcheck-parser');
			$healthcheck->parseToService($pmaService, new ArrayConfiguration($healthcheckConfig) );

			/**
			 * @var PublishUrlsParser $publisher
			 */
			$publisher = container('publish-urls-parser');
			$publishConfig = [
				'publish' => [
					'port' => 80,
					'url' => $publishUrl
				]
			];
			$publisher->parseToService($pmaService, new ArrayConfiguration($publishConfig) );
		}

		$this->infrastructureWriter->setPath( $this->getWorkDirectory() )
			->setSkipClear(false)
			->write($pmaInfrastructure, new FileWriter());
		$output->writeln("Starting ".$pmaService->getName().".");
		$this->rancherService->start( $this->getWorkDirectory(), $stackName);

		$output->writeln("Waiting for pma to become active");
		$this->rancherService->wait($stackName, $newServiceName, new SingleStateMatcher('active') );

	}

	/**
	 * @return string
	 */
	protected function getWorkDirectory() {
		return getcwd() . '/.rancherize';
	}

	/**
	 * @param StorageboxData $data
	 * @param Service[] $commandServices
	 * @param OutputInterface $output
	 */
	private function removeCommandServices( StorageboxData $data, $commandServices, OutputInterface $output ) {
		$stackName = $data->getBackup()->getStackName();

		$rmInfrastructure = new Infrastructure();
		$commandServiceNames = [];
		foreach($commandServices as $commandService) {
			$rmInfrastructure->addService($commandService);
			$commandServiceNames[] = $commandService->getName();
		}

		$this->infrastructureWriter->setPath( $this->getWorkDirectory() )
			->setSkipClear(false)
			->write($rmInfrastructure, new FileWriter());

		$output->writeln("Starting cleaning up command services ".implode(', ', $commandServiceNames).'.');
		$this->rancherService->rm( $this->getWorkDirectory(), $stackName, $commandServiceNames);
	}
}