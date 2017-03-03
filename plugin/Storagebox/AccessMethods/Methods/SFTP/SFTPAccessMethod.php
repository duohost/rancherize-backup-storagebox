<?php namespace RancherizeBackupStoragebox\Storagebox\AccessMethods\Methods\SFTP;

use RancherizeBackupStoragebox\Backup\Backup;
use RancherizeBackupStoragebox\Backup\PODBackup;
use RancherizeBackupStoragebox\Storagebox\AccessMethods\AccessMethod;

/**
 * Class SFTPAccessMethod
 * @package RancherizeBackupStoragebox\Storagebox\AccessMethods\Methods\SFTP
 */
class SFTPAccessMethod implements AccessMethod {

	/**
	 * @var string
	 */
	protected $url = '';

	/**
	 * @var string
	 */
	protected $user = '';

	/**
	 * @var string
	 */
	protected $password = '';

	/**
	 * @return Backup[]
	 */
	public function list() {

		$host = parse_url($this->url, PHP_URL_HOST);
		$path = parse_url($this->url, PHP_URL_PATH);

		$port = parse_url($this->url, PHP_URL_PORT);
		if( empty($port))
			$port = 22;

		$connection = new SFTPConnection($host, $port);
		$connection->login($this->user, $this->password);
		$directories = $connection->scanFilesystem($path, SFTPConnection::SCAN_DIRECTORIES);

		$backups = [];
		foreach($directories as $directory) {
			$backup = new PODBackup();


			try {
				$backupConfig = $connection->receiveFile("${path}$directory/backup.json");
				$dockerCompose = $connection->receiveFile("${path}$directory/docker-compose.yml");
				$rancherCompose = $connection->receiveFile("${path}$directory/rancher-compose.yml");
			} catch(\Exception $e) {
				continue;
			}

			$backupData = json_decode($backupConfig, true);
			if( !array_key_exists('stack', $backupData) )
				continue;
			if( !array_key_exists('service', $backupData) )
				continue;

			$backup->setKey($directory);
			$backup->setName($directory);
			$backup->setDockerCompose($dockerCompose);
			$backup->setRancherCompose($rancherCompose);
			$backup->setStackName($backupData['stack']);
			$backup->setServiceName($backupData['service']);

			$backups[] = $backup;

		}

		return $backups;
	}

	/**
	 * @param string $url
	 */
	public function setUrl(string $url) {
		$this->url = $url;
	}

	/**
	 * @param string $user
	 */
	public function setUser(string $user) {
		$this->user = $user;
	}

	/**
	 * @param string $password
	 */
	public function setPassword(string $password) {
		$this->password = $password;
	}

}