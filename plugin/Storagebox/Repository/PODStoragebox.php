<?php namespace RancherizeBackupStoragebox\Storagebox\Repository;

use RancherizeBackupStoragebox\Storagebox\Storagebox;

/**
 * Class PODStoragebox
 * @package RancherizeBackupStoragebox\Storagebox\Repository
 */
class PODStoragebox implements Storagebox {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $method;

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var array
	 */
	private $accessData;

	/**
	 *
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * @return string
	 */
	public function getAccessData() {
		return $this->accessData;
	}

	/**
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name) {
		$this->name = $name;
	}

	/**
	 * @param string $method
	 */
	public function setMethod(string $method) {
		$this->method = $method;
	}

	/**
	 * @param string $key
	 */
	public function setKey(string $key) {
		$this->key = $key;
	}

	/**
	 * @param array $accessData
	 */
	public function setAccessData(array $accessData) {
		$this->accessData = $accessData;
	}

}