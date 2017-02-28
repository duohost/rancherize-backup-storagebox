<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServiceCollector
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector
 */
class ServiceCollector implements InformationCollector {

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param StorageboxData $data
	 * @return
	 */
	public function collect(InputInterface $input, OutputInterface $output, &$data) {
		$databaseService = $data->getDatabase()->getService();
		$composeParser = $data->getComposeParser();
		$composeData = $data->getComposeData();

		$service = $composeParser->getService($databaseService, $composeData);

		$data->setService($service);
	}
}