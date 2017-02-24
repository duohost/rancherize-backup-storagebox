<?php namespace RancherizeBackupStoragebox\Database\Exceptions;

/**
 * Class DatabaseFieldMissingException
 * @package RancherizeBackupStoragebox\Database\Exceptions
 */
class DatabaseFieldMissingException extends Exception {
	/**
	 * @var string
	 */
	private $field;
	/**
	 * @var array
	 */
	private $data;

	/**
	 * DatabaseFieldMissingException constructor.
	 * @param string $field
	 * @param array $data
	 * @param int $code
	 * @param \Exception $e
	 */
	public function __construct($field, array $data, int $code = 0, \Exception $e = null) {
		$this->field = $field;
		$this->data = $data;
		$availableFields = implode(',', array_keys($data));

		parent::__construct("Database field missing from definition: $field. Available fields: $availableFields", $code, $e);
	}

}