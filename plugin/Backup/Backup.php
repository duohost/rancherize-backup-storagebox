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

	/**
	 * The name of the stack from which this backup was created
	 *
	 * @return string
	 */
	function getStackName();

	/**
	 * The name of the service from which this backup was created
	 *
	 * @return string
	 */
	function getServiceName();

	/**
	 * The docker-compose.yml file that was active when the backup was created
	 *
	 * @return string
	 */
	function getDockerCompose();

	/**
	 * The rancher-compose.yml file that was active when the backup was createdd
	 *
	 * @return string
	 */
	function getRancherCompose();
}