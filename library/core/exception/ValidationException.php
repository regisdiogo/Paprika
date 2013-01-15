<?php
namespace core\exception;
if (!defined('PAPRIKA_PATH')) { die('Direct access not allowed'); }

class ValidationException extends BaseException {

	public function __construct($parameters) {
		parent::__construct(__CLASS__, $parameters);
	}
}
?>