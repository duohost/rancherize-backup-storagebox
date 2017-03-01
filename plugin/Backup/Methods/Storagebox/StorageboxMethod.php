<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox;

use Rancherize\Configuration\Configuration;
use Rancherize\Docker\DockerComposeReader\DockerComposeReader;
use Rancherize\Docker\DockerComposerVersionizer;
use Rancherize\General\Services\ByKeyService;
use RancherizeBackupStoragebox\Backup\Backup;
use RancherizeBackupStoragebox\Backup\BackupMethod;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\FileModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\RequiresReplacementRegex;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\ServiceNameModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\DockerComposeCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\DockerComposeVersionCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\EnvironmentConfigCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\InformationCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\NamedVolumeCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\RancherAccountCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\RequiresQuestionHelper;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\RootPasswordCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\ServiceCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\SidekickCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\SstPasswordCollector;
use RancherizeBackupStoragebox\Database\Database;
use RancherizeBackupStoragebox\Storagebox\AccessMethods\AccessMethodFactory;
use RancherizeBackupStoragebox\Storagebox\Repository\StorageboxRepository;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StorageboxMethod
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox
 */
class StorageboxMethod implements BackupMethod {

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
	 * StorageboxMethod constructor.
	 * @param StorageboxRepository $repository
	 * @param AccessMethodFactory $methodFactory
	 * @param DockerComposeReader $composeReader
	 * @param DockerComposerVersionizer $composerVersionizer
	 * @param ByKeyService $byKeyService
	 */
	public function __construct(StorageboxRepository $repository, AccessMethodFactory $methodFactory,
							DockerComposeReader $composeReader, DockerComposerVersionizer $composerVersionizer,
							ByKeyService $byKeyService
	) {
		$this->repository = $repository;
		$this->methodFactory = $methodFactory;
		$this->byKeyService = $byKeyService;

		$this->collectors = [
			new EnvironmentConfigCollector(),
			new RancherAccountCollector(),
			new DockerComposeCollector($composeReader),
			new DockerComposeVersionCollector($composerVersionizer),
			new ServiceCollector(),
			new SidekickCollector(),
			new RootPasswordCollector($byKeyService),
			new SstPasswordCollector($byKeyService),
			new NamedVolumeCollector(),
		];

		$this->modifiers = [
			new ServiceNameModifier(),
		];
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

		$file = [
			'version' => '2',
			'services' => array_merge(
				[$data->getDatabase()->getService() => $data->getService()],
				$data->getSidekicks()
			)
		];

		// TODO: Allow to set as option. If not set: ask user
		$regex = '$';
		$replacement = '-backup';

		foreach($this->modifiers as $modifier) {
			if($modifier instanceof RequiresReplacementRegex)
				$modifier->setReplacementRegex($regex, $replacement);

			$modifier->modify($file, $data);
		}

		var_dump( $file );
	}

	/**
	 * @param $questionHelper
	 */
	public function setQuestionHelper($questionHelper) {
		$this->questionHelper = $questionHelper;
	}
}