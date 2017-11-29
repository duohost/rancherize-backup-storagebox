<?php namespace RancherizeBackupStoragebox;

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
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\ContainerNetModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\FilterSidekicksModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\ScaleDownModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\ServiceNameModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\SidekickNameModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\VolumeNameModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\VolumesEntryModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier\VolumesFromNameModifier;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\DockerComposeCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\DockerComposeVersionCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\EnvironmentConfigCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\NamedVolumeCollector;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector\NewNamesCollector;
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

		$container['database-parser'] = function($c) {
			return $c[DatabaseParser::class];
		};
		$container[DatabaseParser::class] = function() {
			return new DatabaseParser();
		};

		$container['database-repository'] = function($c) {
			return $c[ConfigurationDatabaseRepository::class];
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
		$container[DockerComposeCollector::class] = function($c) {
			return new DockerComposeCollector($c[DockerComposeReader::class], $c[RancherComposeReader::class]);
		};

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

		$container[NewNamesCollector::class] = function() {
			return new NewNamesCollector();
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
