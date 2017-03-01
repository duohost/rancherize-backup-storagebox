<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier;

/**
 * Interface RequiresReplacementRegex
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier
 */
interface RequiresReplacementRegex {

	/**
	 * Set replacement regex and value to replace it with
	 *
	 * @param string $regex
	 * @param string $replacement
	 */
	function setReplacementRegex(string $regex, string $replacement);
}