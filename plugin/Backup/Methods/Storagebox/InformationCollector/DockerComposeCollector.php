<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector;
use Rancherize\Commands\Traits\RancherTrait;
use Rancherize\Docker\DockerComposeReader\DockerComposeReader;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DockerComposeCollector
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector
 */
class DockerComposeCollector implements InformationCollector {

	use RancherTrait;
	/**
	 * @var DockerComposeReader
	 */
	private $composeReader;

	/**
	 * DockerComposeCollector constructor.
	 * @param DockerComposeReader $composeReader
	 */
	public function __construct(DockerComposeReader $composeReader) {
		$this->composeReader = $composeReader;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param StorageboxData $data
	 * @return
	 */
	public function collect(InputInterface $input, OutputInterface $output, &$data) {

		$rancherService = $this->getRancher();
		$rancherAccount = $data->getRancherAccount();
		$databaseStack = $data->getDatabase()->getStack();

		$rancherService->setAccount($rancherAccount);
		list($currentConfig, $currentRancherize) = $rancherService->retrieveConfig($databaseStack);
		$composeData = $this->composeReader->read($currentConfig);
		$data->setComposeData($composeData);
	}
}