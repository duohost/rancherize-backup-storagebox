<?php namespace RancherizeBackupStoragebox\Database\Parser;

use RancherizeBackupStoragebox\Database\Exceptions\DatabaseFieldMissingException;

/**
 * Class DatabaseParser
 * @package RancherizeBackupStoragebox\Database\Parser
 */
class DatabaseParser {

	/**
	 * @param string $name
	 * @param array $data
	 * @return PODDatabase
	 */
	public function parse(string $name, array $data) {
		$database = new PODDatabase();

		$database->setName($name);
		$database->setKey($name);

		if(!array_key_exists('service', $data))
			throw new DatabaseFieldMissingException('service', $data);
		$database->setService($data['service']);

		if(!array_key_exists('stack', $data))
			throw new DatabaseFieldMissingException('stack', $data);
		$database->setStack($data['stack']);

		if(array_key_exists('restore', $data))
			$database->setBackupData($data['restore']);

		return $database;
	}

}