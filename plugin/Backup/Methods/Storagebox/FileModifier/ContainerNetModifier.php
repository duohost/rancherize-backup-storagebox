<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier;

/**
 * Class SidekickNameModifier
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier
 *
 * Looks for all network: container:xyz entries and changes them to container:xyz-replaced based on the replacement regex
 */
class ContainerNetModifier implements FileModifier, RequiresReplacementRegex {

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
			if( !array_key_exists('network_mode', $service) )
				continue;

			$networkMode = $service['network_mode'];
			$targetMode = 'container:';


			$networkModeIsContainer = substr($networkMode, 0, strlen($targetMode) === $targetMode);
			if( !$networkModeIsContainer )
				continue;

			list($mode, $targetContainer) = explode(':', $networkMode);
			$renamedTarget = preg_replace($this->regex, $this->replacement, $targetContainer);


			$service['network_mode'] = $mode.':'.$renamedTarget;
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