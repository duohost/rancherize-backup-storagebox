<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox;

use Rancherize\Configuration\Configuration;
use Rancherize\Docker\DockerfileParser\DockerComposeParserVersion;
use Rancherize\RancherAccess\RancherAccount;
use RancherizeBackupStoragebox\Database\Database;

/**
 * Class StorageboxData
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox
 */
class StorageboxData {

	/**
	 * @var string
	 */
	protected $environmentName;

	/**
	 * @var Database
	 */
	protected $database;

	/**
	 * @var string
	 */
	protected $backupKey;

	/**
	 * @var Configuration
	 */
	protected $environmentConfig;

	/**
	 * @var Configuration
	 */
	protected $configuration;

	/**
	 * @var RancherAccount
	 */
	protected $rancherAccount;

	/**
	 * @var array
	 */
	protected $composeData;

	/**
	 * @var DockerComposeParserVersion
	 */
	protected $composeParser;

	/**
	 * @var array
	 */
	protected $service;

	/**
	 * @var array
	 */
	protected $sidekicks;

	/**
	 * @var string
	 */
	protected $rootPassword;

	/**
	 * @var string
	 */
	protected $sstPassword;

	/**
	 * @return string
	 */
	public function getEnvironmentName(): string {
		return $this->environmentName;
	}

	/**
	 * @param string $environmentName
	 */
	public function setEnvironmentName(string $environmentName) {
		$this->environmentName = $environmentName;
	}

	/**
	 * @return Database
	 */
	public function getDatabase(): Database {
		return $this->database;
	}

	/**
	 * @param Database $database
	 */
	public function setDatabase(Database $database) {
		$this->database = $database;
	}

	/**
	 * @return string
	 */
	public function getBackupKey(): string {
		return $this->backupKey;
	}

	/**
	 * @param string $backupKey
	 */
	public function setBackupKey(string $backupKey) {
		$this->backupKey = $backupKey;
	}

	/**
	 * @return Configuration
	 */
	public function getEnvironmentConfig(): Configuration {
		return $this->environmentConfig;
	}

	/**
	 * @param Configuration $environmentConfig
	 */
	public function setEnvironmentConfig(Configuration $environmentConfig) {
		$this->environmentConfig = $environmentConfig;
	}

	/**
	 * @return Configuration
	 */
	public function getConfiguration(): Configuration {
		return $this->configuration;
	}

	/**
	 * @param Configuration $configuration
	 */
	public function setConfiguration(Configuration $configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * @return RancherAccount
	 */
	public function getRancherAccount(): RancherAccount {
		return $this->rancherAccount;
	}

	/**
	 * @param RancherAccount $rancherAccount
	 */
	public function setRancherAccount(RancherAccount $rancherAccount) {
		$this->rancherAccount = $rancherAccount;
	}

	/**
	 * @return array
	 */
	public function getComposeData(): array {
		return $this->composeData;
	}

	/**
	 * @param array $composeData
	 */
	public function setComposeData(array $composeData) {
		$this->composeData = $composeData;
	}

	/**
	 * @return DockerComposeParserVersion
	 */
	public function getComposeParser(): DockerComposeParserVersion {
		return $this->composeParser;
	}

	/**
	 * @param DockerComposeParserVersion $composeParser
	 */
	public function setComposeParser(DockerComposeParserVersion $composeParser) {
		$this->composeParser = $composeParser;
	}

	/**
	 * @return array
	 */
	public function getService(): array {
		return $this->service;
	}

	/**
	 * @param array $service
	 */
	public function setService(array $service) {
		$this->service = $service;
	}

	/**
	 * @return array
	 */
	public function getSidekicks(): array {
		return $this->sidekicks;
	}

	/**
	 * @param array $sidekicks
	 */
	public function setSidekicks(array $sidekicks) {
		$this->sidekicks = $sidekicks;
	}

	/**
	 * @return string
	 */
	public function getRootPassword(): string {
		return $this->rootPassword;
	}

	/**
	 * @param string $rootPassword
	 */
	public function setRootPassword(string $rootPassword) {
		$this->rootPassword = $rootPassword;
	}

	/**
	 * @return string
	 */
	public function getSstPassword(): string {
		return $this->sstPassword;
	}

	/**
	 * @param string $sstPassword
	 */
	public function setSstPassword(string $sstPassword) {
		$this->sstPassword = $sstPassword;
	}
}