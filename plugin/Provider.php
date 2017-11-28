<?php namespace RancherizeBackupStoragebox;

use Rancherize\Blueprint\Infrastructure\InfrastructureWriter;
use Rancherize\Configuration\Services\EnvironmentConfigurationService;
use Rancherize\Docker\DockerComposerVersionizer;
use Rancherize\General\Services\ByKeyService;
use Rancherize\General\Services\NameIsPathChecker;
use Rancherize\Plugin\ProviderTrait;
use Rancherize\RancherAccess\RancherAccessService;
use Rancherize\RancherAccess\RancherService;
use Rancherize\Services\BuildService;
use RancherizeBackupStoragebox\Backup\Factory\ArrayBackupMethodFactory;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\ContainerNetModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\FilterSidekicksModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\ScaleDownModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\ServiceNameModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\SidekickNameModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\VolumeNameModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\VolumesEntryModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\VolumesFromNameModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\DockerComposeVersionCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\EnvironmentConfigCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\NamedVolumeCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\RancherAccountCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\RootPasswordCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\ServiceCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\SidekickCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\SstPasswordCollector;
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
		$container = $this->container;

		$container['database-parser'] = function($c) {
			return $c[DatabaseParser::class];
		};
		$container[DatabaseParser::class] = function() {
			return new DatabaseParser();
		};

		$container['database-repository'] = function($c) {
			return $c[ConfigurationDatabaseRepository::class];
		};
		$container[ConfigurationDatabaseRepository::class] = function($c) {
			return new ConfigurationDatabaseRepository($c[DatabaseParser::class]);
		};

		$container['restore-method-factory'] = function() {
			return new ArrayBackupMethodFactory();
		};

		$f = function($c) {
			return new StorageboxService($c['database-repository'], $c['restore-method-factory'], $c[EnvironmentConfigurationService::class]);
		};
		$container['storagebox-service'] = $f;
		$container[StorageboxService::class] = $f;

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
			return new StorageboxMethod($c['storagebox-repository'], $c['access-method-factory'],
				$c[BuildService::class], $c[RancherService::class],
				$c[InfrastructureWriter::class]
			);
		};

		$container[BackupListCommand::class] = function ( $c ) {
			return new BackupListCommand( $c[StorageboxService::class] );
		};
		$container[BackupRestoreCommand::class] = function( $c ) {
			return new BackupRestoreCommand($c[StorageboxService::class]);
		};

		/***************************************************************
		 *  Collectors
		 ***************************************************************/

		$container[ EnvironmentConfigCollector::class ] = function($c) {
			return new EnvironmentConfigCollector($c[EnvironmentConfigurationService::class]);
		};
		$container[RancherAccountCollector::class] = function($c) {
			return new RancherAccountCollector( $c[RancherAccessService::class] );
		};

		$container[DockerComposeVersionCollector::class] = function ($c) {
			return new DockerComposeVersionCollector( $c[DockerComposerVersionizer::class] );
		};

		$container[ServiceCollector::class] = function() {
			return new ServiceCollector();
		};

		$container[SidekickCollector::class] = function() {
			return new SidekickCollector();
		};

		$container[RootPasswordCollector::class] = function($c) {
			return new RootPasswordCollector($c[ByKeyService::class]);
		};

		$container[SstPasswordCollector::class] = function($c) {
			return new SstPasswordCollector($c[ByKeyService::class]);
		};

		$container[NamedVolumeCollector::class] = function () {
			return new NamedVolumeCollector();
		};

		/***************************************************************
		 * Modfiers
		 ***************************************************************/
		$container[FilterSidekicksModifier::class] = function() {
			return new FilterSidekicksModifier();
		};

		$container[ServiceNameModifier::class] = function() {
			return new ServiceNameModifier();
		};

		$container[SidekickNameModifier::class] = function() {
			return new SidekickNameModifier();
		};

		$container[VolumesFromNameModifier::class] = function() {
			return new VolumesFromNameModifier();
		};

		$container[ScaleDownModifier::class] = function() {
			return new ScaleDownModifier();
		};

		$container[VolumeNameModifier::class] = function($c) {
			return new VolumeNameModifier( $c[NameIsPathChecker::class] );
		};

		$container[VolumesEntryModifier::class] = function() {
			return new VolumesEntryModifier();
		};

		$container[ContainerNetModifier::class] = function() {
			return new ContainerNetModifier();
		};
	}

	public function boot() {
		$container = $this->container;

		$this->app->add( $container[BackupListCommand::class] );
		$this->app->add( $container[BackupRestoreCommand::class] );
	}
}
