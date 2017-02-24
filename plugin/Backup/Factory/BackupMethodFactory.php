<?php namespace RancherizeBackupStoragebox\Backup\Factory;

use RancherizeBackupStoragebox\Backup\BackupMethod;

/**
 * Interface BackupMethodFactory
 * @package RancherizeBackupStoragebox\Backup\Factory
 */
interface BackupMethodFactory {

	/**
	 * @param string $backupMethod
	 * @param array $backupData
	 * @return BackupMethod
	 */
	function make(string $backupMethod, array $backupData);
}