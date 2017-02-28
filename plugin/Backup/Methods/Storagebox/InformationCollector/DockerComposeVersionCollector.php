<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector;
use Rancherize\Docker\DockerComposerVersionizer;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DockerComposeVersionCollector
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector
 */
class DockerComposeVersionCollector implements InformationCollector {
	/**
	 * @var DockerComposerVersionizer
	 */
	private $composerVersionizer;

	/**
	 * DockerComposeVersionCollector constructor.
	 * @param DockerComposerVersionizer $composerVersionizer
	 */
	public function __construct(DockerComposerVersionizer $composerVersionizer) {
		$this->composerVersionizer = $composerVersionizer;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param StorageboxData $data
	 * @return
	 */
	public function collect(InputInterface $input, OutputInterface $output, &$data) {
		$composeData = $data->getComposeData();

		$composeVersion = $this->composerVersionizer->parse($composeData);
		$data->setComposeParser($composeVersion);
	}
}