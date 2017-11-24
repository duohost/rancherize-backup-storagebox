<?php namespace RancherizeBackupStoragebox;

use function foo\func;
use Rancherize\Blueprint\Infrastructure\InfrastructureWriter;
use Rancherize\Configuration\Services\EnvironmentConfigurationService;
use Rancherize\Docker\DockerComposeReader\DockerComposeReader;
use Rancherize\Docker\DockerComposerVersionizer;
use Rancherize\Docker\RancherComposeReader\RancherComposeReader;
use Rancherize\General\Services\ByKeyService;
use Rancherize\General\Services\NameIsPathChecker;
use Rancherize\Plugin\ProviderTrait;
use Rancherize\RancherAccess\RancherAccessService;
use Rancherize\RancherAccess\RancherService;
use Rancherize\Services\BuildService;
use RancherizeBackupStoragebox\Backup\Factory\ArrayBackupMethodFactory;
use RancherizeBackupStoragebox\Backup\Factory\BackupMethodFactory;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\EnvironmentConfigCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\RancherAccountCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxMethod;
use RancherizeBackupStoragebox\Commands\BackupListCommand;
use RancherizeBackupStoragebox\Commands\BackupRestoreCommand;
use RancherizeBackupStoragebox\Database\Parser\DatabaseParser;
use RancherizeBackupStoragebox\Database\Repository\ConfigurationDatabaseRepository;
use RancherizeBackupStoragebox\Database\Repository\DatabaseRepository;
use RancherizeBackupStoragebox\Storagebox\AccessMethods\Factory\AccessMethodFactory;
use RancherizeBackupStoragebox\Storagebox\AccessMethods\Factory\ArrayAccessMethodFactory;
use RancherizeBackupStoragebox\Storagebox\Parser\StorageboxParser;
use RancherizeBackupStoragebox\Storagebox\Repository\ConfigurationStorageboxRepository;
use RancherizeBackupStoragebox\Storagebox\Repository\StorageboxRepository;
use RancherizeBackupStoragebox\Storagebox\Service\StorageboxService;

class Provider implements \Rancherize\Plugin\Provider {

	use ProviderTrait;

	public function register() {
		$container = $this->container;

		$container[DatabaseParser::class] = function() {
			return new DatabaseParser();
		};

		$container[DatabaseRepository::class] = function($c) {
			return new ConfigurationDatabaseRepository($c[DatabaseParser::class]);
		};

		$container[BackupMethodFactory::class] = function() {
			return new ArrayBackupMethodFactory();
		};

		$container[StorageboxService::class] = function($c) {
			return new StorageboxService($c[DatabaseRepository::class], $c[BackupMethodFactory::class], $c[EnvironmentConfigurationService::class]);
		};

		$container[StorageboxParser::class] = function() {
			return new StorageboxParser();
		};

		$container[StorageboxRepository::class] = function($c) {
			return new ConfigurationStorageboxRepository($c[StorageboxParser::class]);
		};

		$container[AccessMethodFactory::class] = function() {
			return new ArrayAccessMethodFactory([]);
		};

		$container[StorageboxMethod::class] = function($c) {
			return new StorageboxMethod($c[StorageboxRepository::class], $c[AccessMethodFactory::class],
					$c[DockerComposeReader::class], $c[RancherComposeReader::class],
				$c[DockerComposerVersionizer::class], $c[ByKeyService::class], $c[BuildService::class], $c[RancherService::class], $c[NameIsPathChecker::class],
				$c[InfrastructureWriter::class]
			);
		};

		$container[BackupListCommand::class] = function ( $c ) {
			return new BackupListCommand( $c[StorageboxService::class] );
		};
		$container[BackupRestoreCommand::class] = function( $c ) {
			return new BackupRestoreCommand($c[StorageboxService::class]);
		};

		$this->container[EnvironmentConfigCollector::class] = function($c) {
			return new EnvironmentConfigCollector( $c[EnvironmentConfigurationService::class] );
		};

		$this->container[RancherAccountCollector::class] = function($c) {
			return new RancherAccountCollector($c[RancherAccessService::class]);
		};
	}

	public function boot() {
		$container = $this->container;

		$this->app->add( $container[BackupListCommand::class] );
		$this->app->add( $container[BackupRestoreCommand::class] );
	}
}
