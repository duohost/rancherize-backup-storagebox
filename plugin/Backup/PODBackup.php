<?php namespace RancherizeBackupStoragebox\Backup;

/**
 * Class PODBackup
 * @package RancherizeBackupStoragebox\Backup
 */
class PODBackup implements Backup {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name) {
		$this->name = $name;
	}

	/**
	 * @param string $key
	 */
	public function setKey(string $key) {
		$this->key = $key;
	}
}