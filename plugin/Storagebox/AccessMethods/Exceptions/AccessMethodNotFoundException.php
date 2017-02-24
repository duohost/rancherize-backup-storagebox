<?php namespace RancherizeBackupStoragebox\Storagebox\AccessMethods\Exceptions;

/**
 * Class AccessMethodNotFoundException
 * @package RancherizeBackupStoragebox\Storagebox\AccessMethods\Factory
 */
class AccessMethodNotFoundException extends AccessException {
	/**
	 * @var string
	 */
	private $accessType;

	/**
	 * AccessMethodNotFoundException constructor.
	 * @param string $accessType
	 * @param int $code
	 * @param \Exception $e
	 */
	public function __construct($accessType, int $code = 0, \Exception $e = null) {
		$this->accessType = $accessType;
		parent::__construct("Storagebox access method $accessType not found.", $code, $e);
	}
}