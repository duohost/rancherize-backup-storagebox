<?php namespace RancherizeBackupStoragebox\Storagebox\Parser;
use RancherizeBackupStoragebox\Storagebox\Parser\Exceptions\StorageboxFieldMissingException;
use RancherizeBackupStoragebox\Storagebox\Repository\PODStoragebox;
use RancherizeBackupStoragebox\Storagebox\Storagebox;

/**
 * Class StorageboxParser
 * @package RancherizeBackupStoragebox\Storagebox\Parser
 */
class StorageboxParser {

	/**
	 * @param array $data
	 * @return Storagebox
	 */
	public function parse(string $name, array $data) {
		$storagebox = new PODStoragebox();

		$storagebox->setKey($name);
		$storagebox->setName($name);

		if( !array_key_exists('method', $data) )
			throw new StorageboxFieldMissingException('method', $data);
		$storagebox->setMethod($data['method']);

		if( !array_key_exists('method', $data) )
			throw new StorageboxFieldMissingException('method', $data);
		$storagebox->setAccessData($data['access']);

		return $storagebox;
	}

}