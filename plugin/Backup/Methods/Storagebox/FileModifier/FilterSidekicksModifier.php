<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier;

use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;

/**
 * Class FilterSidekicksModifier
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier
 */
class FilterSidekicksModifier implements FileModifier {

	/**
	 * @param array $dockerFile
	 * @param array $rancherFile
	 * @param StorageboxData $data
	 */
	public function modify(array &$dockerFile, array &$rancherFile, $data) {

		$sidekicks = $data->getSidekicks();
		$serviceName = $data->getBackup()->getServiceName();
		$serviceData = $data->getService();

		$services = array_merge(
			[$serviceName => $serviceData],
			$sidekicks

		);

		$filteredServices = [];
		foreach($rancherFile['services'] as $name => $service) {
			if(!array_key_exists(strtolower($name), $services))
				continue;

			$filteredServices[$name] = $service;
		}
		$rancherFile['services'] = $filteredServices;
	}
}