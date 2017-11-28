<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector;
use Rancherize\RancherAccess\RancherAccessParsesConfiguration;
use Rancherize\RancherAccess\RancherAccessService;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RancherAccountCollector
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector
 */
class RancherAccountCollector implements InformationCollector {
	/**
	 * @var RancherAccessService
	 */
	private $rancherAccessService;

	/**
	 * RancherAccountCollector constructor.
	 * @param RancherAccessService $rancherAccessService
	 */
	public function __construct( RancherAccessService $rancherAccessService) {
		$this->rancherAccessService = $rancherAccessService;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param StorageboxData $data
	 */
	public function collect(InputInterface $input, OutputInterface $output, &$data) {
		$configuration = $data->getConfiguration();
		$environmentConfig = $data->getEnvironmentConfig();

		$rancherConfiguration = $this->rancherAccessService;
		if($rancherConfiguration instanceof RancherAccessParsesConfiguration)
			$rancherConfiguration->parse($configuration);

		$rancherAccount = $rancherConfiguration->getAccount( $environmentConfig->get('rancher.account') );

		$data->setRancherAccount($rancherAccount);
	}
}