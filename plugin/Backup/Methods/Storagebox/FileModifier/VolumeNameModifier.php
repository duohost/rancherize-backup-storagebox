<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier;
use Rancherize\General\Services\NameIsPathChecker;
use RancherizeBackupStoragebox\Backup\Methods\Storagebox\StorageboxData;

/**
 * Class VolumeNameModifier
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier
 */
class VolumeNameModifier implements FileModifier, RequiresReplacementRegex {

	/**
	 * @var string
	 */
	private $replacement;

	/**
	 * @var string
	 */
	private $regex;
	/**
	 * @var NameIsPathChecker
	 */
	private $nameIsPathChecker;

	/**
	 * VolumeNameModifier constructor.
	 * @param NameIsPathChecker $nameIsPathChecker
	 */
	public function __construct(NameIsPathChecker $nameIsPathChecker) {
		$this->nameIsPathChecker = $nameIsPathChecker;
	}

	/**
	 * Set replacement regex and value to replace it with
	 *
	 * @param string $regex
	 * @param string $replacement
	 */
	public function setReplacementRegex(string $regex, string $replacement) {
		$this->regex = $regex;
		$this->replacement = $replacement;
	}

	/**
	 * @param array $dockerFile
	 * @param array $rancherFile
	 * @param StorageboxData $data
	 */
	public function modify(array &$dockerFile, array &$rancherFile, $data) {
		$composeParser = $data->getComposeParser();

		foreach($dockerFile['services'] as &$service) {
			if(!array_key_exists('volumes', $service))
				continue;

			$renamedVolumes = [];
			$volumes = $composeParser->getVolumes($service);
			foreach($volumes as $name => $path) {

				if( is_int($name) )
					continue;
				if( $this->nameIsPathChecker->isPath($name) )
					continue;

				$newName = preg_replace($this->regex, $this->replacement, $name);
				$renamedVolumes[] = "$newName:$path";
			}

			$composeParser->setVolumes($service, $renamedVolumes);
		}
	}
}