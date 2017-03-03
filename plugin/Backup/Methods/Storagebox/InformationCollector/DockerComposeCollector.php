<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector;
use Rancherize\Commands\Traits\RancherTrait;
use Rancherize\Docker\DockerComposeReader\DockerComposeReader;
use Rancherize\Docker\RancherComposeReader\RancherComposeReader;
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
	 * @var RancherComposeReader
	 */
	private $rancherReader;

	/**
	 * DockerComposeCollector constructor.
	 * @param DockerComposeReader $composeReader
	 * @param RancherComposeReader $rancherReader
	 */
	public function __construct(DockerComposeReader $composeReader, RancherComposeReader $rancherReader) {
		$this->composeReader = $composeReader;
		$this->rancherReader = $rancherReader;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param StorageboxData $data
	 */
	public function collect(InputInterface $input, OutputInterface $output, &$data) {

		$backup = $data->getBackup();

		$currentConfig = $backup->getDockerCompose();
		$currentRancher = $backup->getRancherCompose();

		$composeData = $this->composeReader->read($currentConfig);
		$rancherizeData = $this->rancherReader->read($currentRancher);
		$data->setComposeData($composeData);
		$data->setRancherData($rancherizeData);

	}
}