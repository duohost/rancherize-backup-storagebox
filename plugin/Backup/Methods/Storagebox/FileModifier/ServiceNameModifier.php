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
	 * @param array $file
	 * @param $data
	 */
	public function modify(array &$file, $data) {

		$regex = $this->regex;
		$replacement = $this->replacement;

		$renamedServices = [];
		/**
		 *
		 */
		foreach($file['services'] as $serviceName => $service) {

			$newName = preg_replace($regex, $replacement, $serviceName);
			$renamedServices[$newName] = $service;

		}

		foreach($renamedServices as $service) {
			if( !array_key_exists('labels', $service) )
				continue;

			$labels = &$service['labels'];
			if( !array_key_exists('io.rancher.sidekicks', $labels))
				continue;

			$sidekicksString = $labels['io.rancher.sidekicks'];
			$sidekicks = explode(',', $sidekicksString);

			$renamedSidekicks = [];
			foreach($sidekicks as $sidekick) {
				$renamedSidekick = preg_replace($regex, $replacement, $sidekick);
				$renamedSidekicks[] = $renamedSidekick;
			}

			$labels['io.rancher.sidekicks'] = implode(',', $renamedSidekicks);

		}

		$file['services'] = $renamedServices;
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