<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SidekickCollector
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector
 */
class SidekickCollector implements InformationCollector {

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param StorageboxData $data
	 * @return
	 */
	public function collect(InputInterface $input, OutputInterface $output, &$data) {
		$databaseService = $data->getDatabase()->getService();
		$composeParser = $data->getComposeParser();
		$service = $data->getService();
		$composeData = $data->getComposeData();

		$sidekicks = $composeParser->getSidekicks($databaseService, $service, $composeData);

		$data->setSidekicks($sidekicks);
	}
}