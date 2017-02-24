<?php namespace RancherizeBackupStoragebox\Storagebox\AccessMethods;

use RancherizeBackupStoragebox\Backup\Backup;

/**
 * Interface AccessMethod
 * @package RancherizeBackupStoragebox\Storagebox\AccessMethods
 */
interface AccessMethod {

	/**
	 * @return Backup[]
	 */
	function list();
}