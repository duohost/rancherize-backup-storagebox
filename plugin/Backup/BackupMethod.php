<?php namespace RancherizeBackupStoragebox\Backup;
use Rancherize\Configuration\Configuration;

/**
 * Interface BackupMethod
 * @package RancherizeBackupStoragebox\Backup
 */
interface BackupMethod {

	/**
	 * @param Configuration $configuration
	 */
	function setConfiguration(Configuration $configuration);

	/**
	 * @return Backup[]
	 */
	function list();
}