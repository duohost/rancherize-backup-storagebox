<?php namespace RancherizeBackupStoragebox\Storagebox\AccessMethods;

/**
 * Interface AccessMethodFactory
 * @package RancherizeBackupStoragebox\Storagebox\AccessMethods
 */
interface AccessMethodFactory {

	/**
	 * @param string $accessType
	 * @param array $accessData
	 * @return AccessMethod
	 */
	function make(string $accessType, array $accessData );
}