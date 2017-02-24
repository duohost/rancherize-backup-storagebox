<?php namespace RancherizeBackupStoragebox\Database\Repository;
use Rancherize\Configuration\Configuration;
use RancherizeBackupStoragebox\Database\Database;
use RancherizeBackupStoragebox\Database\Exceptions\DatabaseNotFoundException;
use RancherizeBackupStoragebox\Database\Parser\DatabaseParser;

/**
 * Class ConfigurationDatabaseRepository
 * @package RancherizeBackupStoragebox\Database\Repository
 */
class ConfigurationDatabaseRepository implements DatabaseRepository {

	/**
	 * @var Configuration
	 */
	private $configuration;
	/**
	 * @var DatabaseParser
	 */
	private $databaseParser;

	/**
	 * ConfigurationDatabaseRepository constructor.
	 * @param DatabaseParser $databaseParser
	 */
	public function __construct(DatabaseParser $databaseParser) {
		$this->databaseParser = $databaseParser;
	}

	/**
	 * @return Database[]
	 */
	public function get() {
		$data = $this->configuration->get('global.database', []);

		$databases = [];
		foreach($data as $name => $databaseData)
			$databases[] = $this->databaseParser->parse($name, $databaseData);

		return $databases;
	}

	/**
	 * @param string $key
	 * @return Database
	 */
	public function find(string $key) {
		$data = $this->configuration->get('global.database.'.$key, null);
		if( !is_array($data) )
			throw new DatabaseNotFoundException($key);

		return $this->databaseParser->parse($key, $data);
	}

	/**
	 * @param Configuration $configuration
	 */
	public function setConfiguration(Configuration $configuration) {
		$this->configuration = $configuration;
	}
}