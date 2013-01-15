<?php
namespace presentation\component;
if (!defined('PAPRIKA_PATH')) { die('Direct access not allowed'); }

use annotation\Annotation;

abstract class ListViewColumn {

	private $name;
	private $callback;
	private $label;

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getCallback() {
		return $this->callback;
	}

	public function setCallback($callback) {
		$this->callback = $callback;
	}

	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
	}
}
?>