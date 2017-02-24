<?php namespace RancherizeBackupStoragebox\Storagebox\Parser\Exceptions;

/**
 * Class StorageboxFieldMissingException
 * @package RancherizeBackupStoragebox\Storagebox\Parser
 */
class StorageboxFieldMissingException extends ParserException {
	/**
	 * @var string
	 */
	private $field;
	/**
	 * @var array
	 */
	private $data;

	/**
	 * StorageboxFieldMissingException constructor.
	 * @param string $field
	 * @param array $data
	 * @param int $code
	 * @param \Exception $e
	 */
	public function __construct($field, $data, int $code = 0, \Exception $e = null) {
		$this->field = $field;
		$this->data = $data;
		$availableFields = implode( ',', array_keys($data) );

		parent::__construct("Missing field $field to parse storagebox. Available fields: $availableFields", $code, $e);
	}
}