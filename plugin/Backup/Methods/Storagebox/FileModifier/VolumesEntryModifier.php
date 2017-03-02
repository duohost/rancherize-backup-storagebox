<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier;

use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;

/**
 * Class VolumesEntryModifier
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier
 */
class VolumesEntryModifier implements FileModifier {

	/**
	 * @param array $dockerFile
	 * @param array $rancherFile
	 * @param StorageboxData $data
	 */
	public function modify(array &$dockerFile, array &$rancherFile, $data) {
		$composeParser = $data->getComposeParser();

		if( !array_key_exists('volumes', $dockerFile) )
			$dockerFile['volumes'] = [];

		foreach($dockerFile['services'] as $service) {
			if( !array_key_exists('volumes', $service) )
				continue;

			$volumes = $composeParser->getVolumes($service);
			foreach($volumes as $name => $volume) {
				$dockerFile['volumes'][$name] = [
					'driver' => 'local'
				];
			}
		}

	}
}