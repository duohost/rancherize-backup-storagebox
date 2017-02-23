<?php namespace RancherizeBackupStoragebox;

use Rancherize\Plugin\ProviderTrait;
use RancherizeBackupStoragebox\Commands\BackupListCommand;

class Provider implements \Rancherize\Plugin\Provider {

	use ProviderTrait;

	public function register() {
		$this->app->add( new BackupListCommand() );
	}

	public function boot() {
	}
}
