<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector;
use RancherizeBackupStoragebox\Backup\Exceptions\ConfigurationNotFoundException;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class NamedVolumeCollector
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector
 */
class NamedVolumeCollector implements InformationCollector {

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param StorageboxData $data
	 */
	public function collect(InputInterface $input, OutputInterface $output, &$data) {
		$sidekicks = $data->getSidekicks();

		foreach ($sidekicks as $sidekick) {

			if (!array_key_exists('volumes', $sidekick))
				continue;

			try {
				$volumeName = $this->getMysqlVolume($sidekick['volumes']);
				$data->setMysqlVolumeName($volumeName);
			} catch(ConfigurationNotFoundException $e) {
				// /var/lib/mysql volume not within this service
			}

		}
	}

	/**
	 * @param array $volumes
	 */
	private function getMysqlVolume(array $volumes) {
		foreach ($volumes as $volume) {
			$volumeData = explode(':', $volume);
			if (count($volumeData) < 2)
				continue;

			list($name, $path) = $volumeData;
			if ($path === '/var/lib/mysql')
				return $name;

		}

		throw new ConfigurationNotFoundException('Named volume for /var/lib/mysql not found.');
	}
}