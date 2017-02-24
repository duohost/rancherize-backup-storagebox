<?php namespace RancherizeBackupStoragebox\Database\Exceptions;

/**
 * Class DatabaseNotFoundException
 * @package RancherizeBackupStoragebox\Database\Exceptions
 */
class DatabaseNotFoundException extends Exception {
	/**
	 * @var string
	 */
	private $database;

	/**
	 * DatabaseNotFoundException constructor.
	 * @param string $database
	 * @param int $code
	 * @param \Exception|null $e
	 */
	public function __construct($database, int $code = 0, \Exception $e = null) {
		$this->database = $database;
		parent::__construct("Database with key $database not found", $code, $e);
	}
}