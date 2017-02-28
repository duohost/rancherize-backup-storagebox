<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector;
use Rancherize\Commands\Traits\RancherTrait;
use Rancherize\RancherAccess\RancherAccessService;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RancherAccountCollector
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector
 */
class RancherAccountCollector implements InformationCollector {

	use RancherTrait;

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param StorageboxData $data
	 * @return
	 */
	public function collect(InputInterface $input, OutputInterface $output, &$data) {
		$configuration = $data->getConfiguration();
		$environmentConfig = $data->getEnvironmentConfig();

		$rancherConfiguration = new RancherAccessService($configuration);
		$rancherAccount = $rancherConfiguration->getAccount( $environmentConfig->get('rancher.account') );

		$data->setRancherAccount($rancherAccount);
	}
}