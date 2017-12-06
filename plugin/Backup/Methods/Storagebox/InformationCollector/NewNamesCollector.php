<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class NewNamesCollector
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector
 */
class NewNamesCollector implements InformationCollector {

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param StorageboxData $data
	 */
	public function collect( InputInterface $input, OutputInterface $output, &$data ) {
		$backupKey = $data->getBackupKey();

		$regex = '~$~';
		$replacement = '-'.$backupKey;

		$serviceName = $data->getBackup()->getServiceName();

		$newName = preg_replace($regex, $replacement, $serviceName);
		$data->setNewServiceName($newName);
		$data->setNewMysqlVolumeService($newName);

		$newVolumeName = preg_replace($regex, $replacement, $data->getMysqlVolumeName());
		$data->setNewMysqlVolumeName($newVolumeName);
	}
}