<?php namespace RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier;

/**
 * Class SidekickNameModifier
 * @package RancherizeBackupStoragebox\Backup\Methods\Storagebox\FileModifier
 */
class SidekickNameModifier implements FileModifier, RequiresReplacementRegex {

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

		foreach($file['services'] as &$service) {
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