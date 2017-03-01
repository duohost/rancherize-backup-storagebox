<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier;

/**
 * Class ServiceNameModifier
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier
 */
class ServiceNameModifier implements FileModifier, RequiresReplacementRegex {

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

		$renamedServices = [];
		/**
		 *
		 */
		foreach($dockerFile['services'] as $serviceName => $service) {

			$newName = preg_replace($regex, $replacement, $serviceName);
			$renamedServices[$newName] = $service;

		}
		$dockerFile['services'] = $renamedServices;

		/**
		 *
		 */
		$renamedRancherServices = [];
		foreach($rancherFile['services'] as $serviceName => $service) {

			$newName = preg_replace($regex, $replacement, $serviceName);
			$renamedRancherServices[$newName] = $service;

		}

		$rancherFile['services'] = $renamedRancherServices;
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