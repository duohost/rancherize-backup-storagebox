<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface InformationCollector
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector
 */
interface InformationCollector {
	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param $data
	 */
	function collect(InputInterface $input, OutputInterface $output, &$data);
}