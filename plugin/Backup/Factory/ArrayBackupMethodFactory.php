<?php namespace RancherizeBackupStoragebox\Backup\Factory;

use Closure;
use RancherizeBackupStoragebox\Backup\BackupMethod;
use RancherizeBackupStoragebox\Backup\Exceptions\MethodNotFoundException;

/**
 * Class ArrayBackupMethodFactory
 * @package RancherizeBackupStoragebox\Backup\Factory
 */
class ArrayBackupMethodFactory implements BackupMethodFactory {

	/**
	 * @var Closure[]
	 */
	protected $methods = [
		'storagebox'
	];

	/**
	 * @param string $backupMethod
	 * @param array $backupData
	 * @return BackupMethod
	 */
	public function make(string $backupMethod, array $backupData) {
		$availableMethods = $this->methods;

		if( ! array_key_exists($backupMethod, $availableMethods) )
			throw new MethodNotFoundException($backupMethod, array_keys($availableMethods));

		$closure = $availableMethods[$backupMethod];

		return $closure($backupData);
	}
}