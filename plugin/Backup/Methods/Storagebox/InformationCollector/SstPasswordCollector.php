<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector;
use Rancherize\General\Exceptions\KeyNotFoundException;
use Rancherize\General\Services\ByKeyService;
use RancherizeBackupStoragebox\Backup\Exceptions\ConfigurationNotFoundException;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;
use RancherizeBackupStoragebox\General\Helper\RequiresQuestionHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class SstPasswordCollector
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector
 */
class SstPasswordCollector implements InformationCollector, RequiresQuestionHelper {
	/**
	 * @var ByKeyService
	 */
	private $byKeyService;

	/**
	 * @var QuestionHelper
	 */
	private $questionHelper;

	/**
	 * SstPasswordCollector constructor.
	 * @param ByKeyService $byKeyService
	 */
	public function __construct(ByKeyService $byKeyService) {
		$this->byKeyService = $byKeyService;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param StorageboxData $data
	 */
	public function collect(InputInterface $input, OutputInterface $output, &$data) {
		$sidekicks = $data->getSidekicks();

		$sstPasswords = [];
		foreach($sidekicks as $sidekick) {
			if( !array_key_exists('environment', $sidekick) )
				continue;

			$environment = $sidekick['environment'];
			if( !array_key_exists('environment', $sidekick) )
				continue;

			try {
				$sstPassword = $this->byKeyService->byKey('PXC_SST_PASSWORD', $environment);
				$sstPasswords[] = $sstPassword[1];
			} catch(KeyNotFoundException $e) {
			}
		}

		if( empty($sstPasswords) )
			throw new ConfigurationNotFoundException("No PXC_SST_PASSWORD environment variable found");

		$sstPassword = $sstPasswords[0];
		if( count($sstPasswords) > 1 ) {
			$choices = array_merge($sstPasswords, ['-- manual input --']);

			$question = new ChoiceQuestion('More than one possible state transfer password was found. Please chose the one in use by the database backup.', $choices, 0);
			$sstPassword = $this->questionHelper->ask($input, $output, $question);
		}

		$data->setSstPassword($sstPassword);
	}

	/**
	 * @param QuestionHelper $questionHelper
	 */
	public function setQuestionHelper(QuestionHelper $questionHelper) {
		$this->questionHelper = $questionHelper;
	}
}