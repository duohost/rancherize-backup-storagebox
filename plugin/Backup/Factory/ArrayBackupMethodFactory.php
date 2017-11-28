<?php namespace RancherizeBackupStoragebox\Backup\Factory;

use Closure;
use RancherizeBackupStoragebox\Backup\BackupMethod;
use RancherizeBackupStoragebox\Backup\Exceptions\MethodNotFoundException;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxMethod;
use RancherizeBackupStoragebox\Storagebox\Parser\Exceptions\StorageboxFieldMissingException;

/**
 * Class ArrayBackupMethodFactory
 * @package RancherizeBackupStoragebox\Backup\Factory
 */
class ArrayBackupMethodFactory implements BackupMethodFactory {

	/**
	 * @var Closure[]
	 */
	protected $methods = [
	];

	/**
	 * ArrayBackupMethodFactory constructor.
	 */
	public function __construct() {
		$this->methods = [
			'storagebox' => function(array $backupData) {
				/**
				 * @var StorageboxMethod $method
				 */
				$method = container(StorageboxMethod::class);

				if(in_array('box', $backupData))
					throw new StorageboxFieldMissingException('box', $backupData);

				$method->setStorageBox($backupData['box']);

				return $method;
			}
		];
	}

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