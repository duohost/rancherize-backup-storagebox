<?php namespace RancherizeBackupStoragebox\Storagebox\Repository;
use Rancherize\Configuration\Configuration;
use RancherizeBackupStoragebox\Storagebox\Exceptions\StorageboxNotFoundException;
use RancherizeBackupStoragebox\Storagebox\Parser\StorageboxParser;
use RancherizeBackupStoragebox\Storagebox\Storagebox;

/**
 * Class ConfigurationStorageboxRepository
 * @package RancherizeBackupStoragebox\Storagebox\Repository
 */
class ConfigurationStorageboxRepository implements StorageboxRepository {
	/**
	 * @var Configuration
	 */
	private $configuration;
	/**
	 * @var StorageboxParser
	 */
	private $parser;

	/**
	 * ConfigurationStorageboxRepository constructor.
	 * @param StorageboxParser $parser
	 */
	public function __construct(StorageboxParser $parser) {
		$this->parser = $parser;
	}

	/**
	 * @return Storagebox[]
	 */
	public function get() {
		$storageboxData = $this->configuration->get('global.storagebox');

		$storageboxes = [];
		foreach($storageboxData as $name => $storagebox) {
			$storageboxes[] = $this->parser->parse($name, $storagebox);
		}

		return $storageboxes;
	}

	/**
	 * @param Configuration $configuration
	 */
	public function setConfiguration(Configuration $configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * @param string $key
	 * @return Storagebox
	 */
	public function find(string $key) {
		$storageboxData = $this->configuration->get('global.storagebox.'.$key);
		if($storageboxData === null)
			throw new StorageboxNotFoundException($key);

		return $this->parser->parse($key, $storageboxData);
	}
}