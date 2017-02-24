<?php namespace RancherizeBackupStoragebox\Storagebox\Exceptions;

/**
 * Class StorageboxNotFoundException
 * @package RancherizeBackupStoragebox\Storagebox\Exceptions
 */
class StorageboxNotFoundException extends StorageboxException {
	/**
	 * @var string
	 */
	private $key;

	/**
	 * StorageboxNotFoundException constructor.
	 * @param string $key
	 * @param int $code
	 * @param \Exception|null $e
	 */
	public function __construct(string $key, int $code = 0, \Exception $e = null) {
		$this->key = $key;
		parent::__construct("Storagebox $key not found.", $code, $e);
	}

}