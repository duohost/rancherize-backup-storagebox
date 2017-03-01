<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier;

/**
 * Class SidekickNameModifier
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier
 */
class VolumesFromNameModifier implements FileModifier, RequiresReplacementRegex {

	/**
	 * @var string
	 */
	private $regex;

	/**
	 * @var string
	 */
	private $replacement;

	/**
	 * @param array $dockerFile
	 * @param array $rancherFile
	 * @param $data
	 */
	public function modify(array &$dockerFile, array &$rancherFile, $data) {

		$regex = $this->regex;
		$replacement = $this->replacement;

		foreach($dockerFile['services'] as &$service) {
			if( !array_key_exists('volumes_from', $service) )
				continue;

			$renamedVolumesFroms = [];
			foreach($service['volumes_from'] as $volumesFrom) {
				$renamedVolumesFrom = preg_replace($regex, $replacement, $volumesFrom);
				$renamedVolumesFroms[] = $renamedVolumesFrom;
			}

			$service['volumes_from'] = $renamedVolumesFroms;
		}
	}

	/**
	 * @param string $regex
	 * @param string $replacement
	 */
	public function setReplacementRegex(string $regex, string $replacement) {
		$this->regex = $regex;
		$this->replacement = $replacement;
	}
}