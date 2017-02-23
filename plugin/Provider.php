<?php namespace RancherizeBackupStoragebox;

use Rancherize\Plugin\ProviderTrait;

class Provider implements \Rancherize\Plugin\Provider {

	use ProviderTrait;

	public function register() {
		echo "Backup registered!";
	}

	public function boot() {
		echo "Booted!";
	}
}
