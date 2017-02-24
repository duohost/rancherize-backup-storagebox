<?php namespace RancherizeBackupStoragebox\Storagebox\AccessMethods\Exceptions;

/**
 * Class AccessFieldMissingException
 * @package RancherizeBackupStoragebox\Storagebox\AccessMethods\Exceptions
 */
class AccessFieldMissingException extends AccessException {
	/**
	 * @var string
	 */
	private $method;
	/**
	 * @var string
	 */
	private $field;
	/**
	 * @var array
	 */
	private $fields;

	/**
	 * AccessFieldMissingException constructor.
	 * @param string $method
	 * @param string $field
	 * @param array $fields
	 * @param int $code
	 * @param \Exception|null $e
	 */
	public function __construct(string $method, string $field, array $fields, int $code = 0, \Exception $e = null) {
		$availableFields = implode(',', array_keys($fields));

		parent::__construct("Field for access method $method missing: $field. Available fields: $availableFields", $code, $e);
		$this->method = $method;
		$this->field = $field;
		$this->fields = $fields;
	}

}