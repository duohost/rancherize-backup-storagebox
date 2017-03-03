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
	 * @var string
	 */
	private $dockerCompose;

	/**
	 * @var string
	 */
	private $rancherCompose;

	/**
	 * @var string
	 */
	private $serviceName;

	/**
	 * @var string
	 */
	private $stackName;

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

	/**
	 * @return string
	 */
	public function getRancherCompose(): string {
		return $this->rancherCompose;
	}

	/**
	 * @param string $rancherCompose
	 */
	public function setRancherCompose(string $rancherCompose) {
		$this->rancherCompose = $rancherCompose;
	}

	/**
	 * @return string
	 */
	public function getDockerCompose(): string {
		return $this->dockerCompose;
	}

	/**
	 * @param string $dockerCompose
	 */
	public function setDockerCompose(string $dockerCompose) {
		$this->dockerCompose = $dockerCompose;
	}

	/**
	 * @return string
	 */
	public function getServiceName(): string {
		return $this->serviceName;
	}

	/**
	 * @param string $serviceName
	 */
	public function setServiceName(string $serviceName) {
		$this->serviceName = $serviceName;
	}

	/**
	 * @return string
	 */
	public function getStackName(): string {
		return $this->stackName;
	}

	/**
	 * @param string $stackName
	 */
	public function setStackName(string $stackName) {
		$this->stackName = $stackName;
	}


}