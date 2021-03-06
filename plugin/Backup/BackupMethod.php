<?php namespace RancherizeBackupStoragebox\Backup;
use Rancherize\Configuration\Configuration;
use RancherizeBackupStoragebox\Database\Database;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface BackupMethod
 * @package RancherizeBackupStoragebox\Backup
 */
interface BackupMethod {

	/**
	 * @param Configuration $configuration
	 */
	function setConfiguration(Configuration $configuration);

	/**
	 * @return Backup[]
	 */
	function list();

	/**
	 * @param string $environment
	 * @param Database $database
	 * @param Backup $backupKey
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return
	 */
	function restore(string $environment, Database $database, Backup $backupKey, InputInterface $input, OutputInterface $output);
}