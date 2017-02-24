<?php namespace RancherizeBackupStoragebox\Database;

/**
 * Interface Database
 * @package RancherizeBackupStoragebox\Database
 */
interface Database {

	/**
	 * @return string
	 */
	function getKey();

	/**
	 * @return string
	 */
	function getName();

	/**
	 * @return string
	 */
	function getStack();

	/**
	 * @return string
	 */
	function getService();

	/**
	 * @return array
	 */
	function getBackupData();
}