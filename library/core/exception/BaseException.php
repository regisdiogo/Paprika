<?php
namespace core\exception;
if (!defined('PAPRIKA_PATH')) {
	die('Direct access not allowed');
}

abstract class BaseException extends \Exception {

	private $parameters;
	private $fatal = false;

	public function __construct($e, $parameters = null) {
		if ($e instanceof BaseException)
			$this->setFatal($e->getFatal());

		if (isset($parameters))
			$this->setParameters($parameters);

		$message = '';
		if ($e instanceof \Exception) {
			$message = $e->getMessage();
		} else {
			$message = $e;
		}
		parent::__construct($message);
	}

	public function __toString() {
		return $this->getMessage();
	}

	public function getParameters() {
		return $this->parameters;
	}

	public function setParameters($parameters) {
		$this->parameters = $parameters;
	}

	public function getFatal() {
		return $this->fatal;
	}

	public function setFatal($fatal) {
		$this->fatal = $fatal;
	}
}
?>