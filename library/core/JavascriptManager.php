<?php
namespace core;
if (!defined('PAPRIKA_PATH')) {
	die('Direct access not allowed');
}

class JavascriptManager {

	private $commands = array();
	private static $instance = null;

	private function __construct() {
	}

	private function __clone() {
	}

	/**
	 * @return JavascriptManager
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new JavascriptManager();
		}
		return self::$instance;
	}

	public function putCommand($command) {
		$this->commands[] = $command;
	}

	public function windowLoad() {
		$jsPattern = '$(window).load(function(){'.PHP_EOL.'%1$s'.PHP_EOL.'});'.PHP_EOL;
		$output = implode(PHP_EOL, $this->commands);
		return sprintf($jsPattern, $output);
	}

}
?>