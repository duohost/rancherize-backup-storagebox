<?php namespace RancherizeBackupStoragebox\Storagebox\AccessMethods\Factory;
use Closure;
use RancherizeBackupStoragebox\Storagebox\AccessMethods\AccessMethod;
use RancherizeBackupStoragebox\Storagebox\AccessMethods\AccessMethodFactory;
use RancherizeBackupStoragebox\Storagebox\AccessMethods\Exceptions\AccessMethodNotFoundException;

/**
 * Class ArrayAccessMethodFactory
 * @package RancherizeBackupStoragebox\Storagebox\AccessMethods\Factory
 */
class ArrayAccessMethodFactory implements AccessMethodFactory {

	/**
	 * @var Closure[]
	 */
	protected $methods = [
	];

	/**
	 * ArrayAccessMethodFactory constructor.
	 * @param array $methods
	 */
	public function __construct(array $methods) {
		$this->methods = array_merge(
			$this->methods,
			$methods
		);
	}

	/**
	 * @param string $accessType
	 * @param array $accessData
	 * @return AccessMethod
	 */
	public function make(string $accessType, array $accessData) {
		if(! array_key_exists($accessType, $this->methods) )
			throw new AccessMethodNotFoundException($accessType);

		$closure = $this->methods[$accessType];

		return $closure( $accessData );
	}
}