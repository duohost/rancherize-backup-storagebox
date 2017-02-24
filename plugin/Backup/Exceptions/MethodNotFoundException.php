<?php namespace RancherizeBackupStoragebox\Backup\Exceptions;

/**
 * Class MethodNotFoundException
 * @package RancherizeBackupStoragebox\Backup\Exceptions
 */
class MethodNotFoundException extends BackupException {
	/**
	 * @var string
	 */
	private $method;
	/**
	 * @var array
	 */
	private $availableMethods;

	/**
	 * MethodNotFoundException constructor.
	 * @param string $method
	 * @param array $availableMethods
	 * @param int $code
	 * @param \Exception|null $e
	 */
	public function __construct(string $method, array $availableMethods, int $code = 0, \Exception $e = null) {
		$this->method = $method;
		$this->availableMethods = $availableMethods;
		$availableMethodNames = implode(',', $availableMethods);

		parent::__construct("Backup method not found: $method. Available methods are $availableMethodNames", $code, $e);
	}

}