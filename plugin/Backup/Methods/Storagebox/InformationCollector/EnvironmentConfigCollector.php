<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector;
use Rancherize\Configuration\Services\EnvironmentConfigurationService;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EnvironmentConfigCollector
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector
 */
class EnvironmentConfigCollector implements InformationCollector {

	/**
	 * @var EnvironmentConfigurationService
	 */
	private $environmentConfigurationService;

	public function __construct( EnvironmentConfigurationService $environmentConfigurationService) {
		$this->environmentConfigurationService = $environmentConfigurationService;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param StorageboxData $data
	 * @return
	 */
	public function collect(InputInterface $input, OutputInterface $output, &$data) {
		$environment = $data->getEnvironmentName();
		$configuration = $data->getConfiguration();

		$environmentConfig = $this->environmentConfigurationService->environmentConfig($configuration, $environment);
		$data->setEnvironmentConfig($environmentConfig);
	}
}