<?php namespace RancherizeBackupStoragebox;

use Rancherize\Plugin\ProviderTrait;
use RancherizeBackupStoragebox\Backup\Factory\ArrayBackupMethodFactory;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxMethod;
use RancherizeBackupStoragebox\Commands\BackupListCommand;
use RancherizeBackupStoragebox\Commands\BackupRestoreCommand;
use RancherizeBackupStoragebox\Database\Parser\DatabaseParser;
use RancherizeBackupStoragebox\Database\Repository\ConfigurationDatabaseRepository;
use RancherizeBackupStoragebox\Storagebox\AccessMethods\Factory\ArrayAccessMethodFactory;
use RancherizeBackupStoragebox\Storagebox\Parser\StorageboxParser;
use RancherizeBackupStoragebox\Storagebox\Repository\ConfigurationStorageboxRepository;
use RancherizeBackupStoragebox\Storagebox\Service\StorageboxService;

class Provider implements \Rancherize\Plugin\Provider {

	use ProviderTrait;

	public function register() {
		$this->app->add( new BackupListCommand() );
		$this->app->add( new BackupRestoreCommand() );
		$container = container();

		$container['database-parser'] = function() {
			return new DatabaseParser();
		};

		$container['database-repository'] = function($c) {
			return new ConfigurationDatabaseRepository($c['database-parser']);
		};

		$container['backup-method-factory'] = function() {
			return new ArrayBackupMethodFactory();
		};

		$container['storagebox-service'] = function($c) {
			return new StorageboxService($c['database-repository'], $c['backup-method-factory'], $c['docker-compose-reader'], $c['docker-compose-versionizer']);
		};

		$container['storagebox-parser'] = function() {
			return new StorageboxParser();
		};

		$container['storagebox-repository'] = function($c) {
			return new ConfigurationStorageboxRepository($c['storagebox-parser']);
		};

		$container['access-method-factory'] = function() {
			return new ArrayAccessMethodFactory([]);
		};

		$container['storagebox-method'] = function($c) {
			return new StorageboxMethod($c['storagebox-repository'], $c['access-method-factory']);
		};
	}

	public function boot() {
	}
}
