<?php namespace RancherizeBackupStoragebox\General\Helper;

use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Interface RequiresQuestionHelper
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector
 *
 * Used by InformationCollector to indicate that a QuestionHelper is necessary to collect the information
 */
interface RequiresQuestionHelper {

	/**
	 * @param QuestionHelper $questionHelper
	 */
	function setQuestionHelper(QuestionHelper $questionHelper);

}