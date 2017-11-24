<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector;
use Rancherize\Commands\Traits\RancherTrait;
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
	 * @return
	 */
	public function collect(InputInterface $input, OutputInterface $output, &$data) {
		$configuration = $data->getConfiguration();
		$environmentConfig = $data->getEnvironmentConfig();

		if( $this->rancherAccessService instanceof RancherAccessParsesConfiguration)
			$this->rancherAccessService->parse($configuration);
		$rancherAccount = $this->rancherAccessService->getAccount( $environmentConfig->get('rancher.account') );

		$data->setRancherAccount($rancherAccount);
	}
}