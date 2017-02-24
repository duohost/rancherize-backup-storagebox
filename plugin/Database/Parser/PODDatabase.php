<?php namespace RancherizeBackupStoragebox\Database\Parser;

use RancherizeBackupStoragebox\Database\Database;

/**
 * Class PODDatabase
 * @package RancherizeBackupStoragebox\Database\Parser
 */
class PODDatabase implements Database {
	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $stack;

	/**
	 * @var string
	 */
	private $service;

	/**
	 * @var array
	 */
	private $backupData;

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
	 * @return string
	 */
	public function getStack() {
		return $this->stack;
	}

	/**
	 * @return string
	 */
	public function getService() {
		return $this->service;
	}

	/**
	 * @return array
	 */
	public function getBackupData() {
		return $this->backupData;
	}

	/**
	 * @param string $key
	 */
	public function setKey(string $key) {
		$this->key = $key;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name) {
		$this->name = $name;
	}

	/**
	 * @param string $stack
	 */
	public function setStack(string $stack) {
		$this->stack = $stack;
	}

	/**
	 * @param string $service
	 */
	public function setService(string $service) {
		$this->service = $service;
	}

	/**
	 * @param array $backupData
	 */
	public function setBackupData(array $backupData) {
		$this->backupData = $backupData;
	}

}