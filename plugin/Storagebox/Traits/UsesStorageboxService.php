<?php


namespace RancherizeBackupStoragebox\Storagebox\Traits;


use RancherizeBackupStoragebox\Storagebox\Service\StorageboxService;

trait UsesStorageboxService {

	/**
	 * @return StorageboxService
	 */
	public function getStorageboxService() {
		return container('storagebox-service');
	}
}