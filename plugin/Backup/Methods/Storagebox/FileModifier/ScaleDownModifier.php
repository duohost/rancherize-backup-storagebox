<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier;

use RancherizeBackupStoragebox\Backup\Exceptions\ConfigurationNotFoundException;

/**
 * Class ScaleDownModifier
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier
 */
class ScaleDownModifier implements FileModifier {

	/**
	 * @param array $dockerFile
	 * @param array $rancherFile
	 * @param $data
	 */
	public function modify(array &$dockerFile, array &$rancherFile, $data) {
		if(! array_key_exists('services', $rancherFile))
			throw new ConfigurationNotFoundException('RancherCompose services');

		foreach($rancherFile['services'] as &$service) {
			if( !array_key_exists('scale', $service))
				continue;

			$service['scale'] = 1;
		}
	}
}