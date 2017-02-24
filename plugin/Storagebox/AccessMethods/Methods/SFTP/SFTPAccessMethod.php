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

		$host = parse_url($this->url);
		$path = parse_url($this->url, PHP_URL_PATH);

		$port = parse_url($this->url, PHP_URL_PORT);
		if( empty($port))
			$port = 22;

		$connection = new SFTPConnection($host, $port);
		$directories = $connection->scanFilesystem($path, SFTPConnection::SCAN_DIRECTORIES);

		$backups = [];
		foreach($directories as $directory) {
			$backup = new PODBackup();


			$backup->setKey($directory);
			$backup->setName($directory);

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