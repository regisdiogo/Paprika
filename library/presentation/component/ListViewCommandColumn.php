<?php
namespace presentation\component;
if (!defined('PAPRIKA_PATH')) { die('Direct access not allowed'); }

class ListViewCommandColumn extends ListViewColumn {

	public function __construct($name, $callback) {
		$this->setName($name);
		$this->setCallback($callback);
	}

}
?>