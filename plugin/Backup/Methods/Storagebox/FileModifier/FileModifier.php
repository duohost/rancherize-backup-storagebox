<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier;

/**
 * Interface FileModifier
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier
 */
interface FileModifier {
	/**
	 * @param array $dockerFile
	 * @param array $rancherFile
	 * @param $data
	 * @return
	 */
	function modify(array &$dockerFile, array &$rancherFile, $data);
}