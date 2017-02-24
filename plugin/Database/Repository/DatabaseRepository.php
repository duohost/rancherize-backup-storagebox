<?php namespace RancherizeBackupStoragebox\Database\Repository;

use RancherizeBackupStoragebox\Database\Database;
use RancherizeBackupStoragebox\General\Configuration\RequiresConfiguration;

/**
 * Interface DatabaseRepository
 * @package RancherizeBackupStoragebox\Database\Repository
 */
interface DatabaseRepository extends RequiresConfiguration {

	/**
	 * @return Database[]
	 */
	function get();

	/**
	 * @param string $key
	 * @return Database
	 */
	function find(string $key);
}