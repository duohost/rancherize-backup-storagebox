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
 * Class RootPasswordCollector
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\InformationCollector
 */
class RootPasswordCollector implements InformationCollector, RequiresQuestionHelper {
	/**
	 * @var ByKeyService
	 */
	private $byKeyService;

	/**
	 * @var QuestionHelper
	 */
	private $questionHelper;

	/**
	 * RootPasswordCollector constructor.
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

		$rootPasswords = [];

		foreach($sidekicks as $sidekick) {
			if( !array_key_exists('environment', $sidekick) )
				continue;

			$environment = $sidekick['environment'];
			if( !array_key_exists('environment', $sidekick) )
				continue;

			try {
				$rootPassword = $this->byKeyService->byKey('MYSQL_ROOT_PASSWORD', $environment);
				$rootPasswords[] = $rootPassword[1];
			} catch(KeyNotFoundException $e) {
			}
		}

		if( empty($rootPasswords) )
			throw new ConfigurationNotFoundException("No MYSQL_ROOT_PASSWORD environment variable found");

		$rootPassword = $rootPasswords[0];
		if( count($rootPasswords)  > 1 ) {
			$choices = array_merge($rootPasswords, ['-- manual input --']);

			$question = new ChoiceQuestion('More than one possible root password was found. Please chose the one in use by the database backup.', $choices, 0);
			$rootPassword = $this->questionHelper->ask($input, $output, $question);
		}

		$data->setRootPassword($rootPassword);
	}

	/**
	 * @param QuestionHelper $questionHelper
	 */
	public function setQuestionHelper(QuestionHelper $questionHelper) {
		$this->questionHelper = $questionHelper;
	}
}