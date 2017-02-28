<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector;
use Rancherize\Configuration\Traits\EnvironmentConfigurationTrait;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EnvironmentConfigCollector
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector
 */
class EnvironmentConfigCollector implements InformationCollector {

	use EnvironmentConfigurationTrait;

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param StorageboxData $data
	 * @return
	 */
	public function collect(InputInterface $input, OutputInterface $output, &$data) {
		$environment = $data->getEnvironmentName();
		$configuration = $data->getConfiguration();

		$environmentConfig = $this->environmentConfig($configuration, $environment);
		$data->setEnvironmentConfig($environmentConfig);
	}
}