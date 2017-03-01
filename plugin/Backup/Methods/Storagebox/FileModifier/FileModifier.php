<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier;

/**
 * Interface FileModifier
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier
 */
interface FileModifier {
	/**
	 * @param array $file
	 * @param $data
	 */
	function modify(array &$file, $data);
}