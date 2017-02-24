<?php namespace RancherizeBackupStoragebox\General\Configuration;

use Rancherize\Configuration\Configuration;

/**
 * Interface RequiresConfiguration
 * @package RancherizeBackupStoragebox\General\Configuration
 */
interface RequiresConfiguration {

	/**
	 * @param Configuration $configuration
	 */
	function setConfiguration(Configuration $configuration);

}