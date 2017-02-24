<?php namespace RancherizeBackupStoragebox\Storagebox\Repository;

use RancherizeBackupStoragebox\General\Configuration\RequiresConfiguration;
use RancherizeBackupStoragebox\Storagebox\Storagebox;

/**
 * Interface StorageboxRepository
 * @package RancherizeBackupStoragebox\Storagebox\Repository
 */
interface StorageboxRepository extends RequiresConfiguration {

	/**
	 * @param string $key
	 * @return Storagebox
	 */
	function find(string $key);


	/**
	 * @return Storagebox[]
	 */
	function get();
}