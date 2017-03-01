<?php namespace RancherizeBackupStoragebox\General\Helper;

use Symfony\Component\Console\Helper\ProcessHelper;

/**
 * Interface RequiresProcessHelper
 * @package RancherizeBackupStoragebox\General\Helper
 */
interface RequiresProcessHelper {

	/**
	 * @param ProcessHelper $processHelper
	 */
	function setProcessHelper(ProcessHelper $processHelper);
}