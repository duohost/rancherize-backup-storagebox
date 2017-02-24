<?php namespace RancherizeBackupStoragebox\Storagebox;

/**
 * Interface Storagebox
 * @package RancherizeBackupStoragebox\Storagebox
 */
interface Storagebox {

	/**
	 * @return string
	 */
	function getKey();

	/**
	 * @return array
	 */
	function getAccessData();

	/**
	 * @return string
	 */
	function getMethod();
}