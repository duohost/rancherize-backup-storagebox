<?php namespace RancherizeBackupStoragebox\Backup;

/**
 * Interface Backup
 * @package RancherizeBackupStoragebox
 */
interface Backup {

	/**
	 * @return string
	 */
	function getKey();

	/**
	 * @return string
	 */
	function getName();

}