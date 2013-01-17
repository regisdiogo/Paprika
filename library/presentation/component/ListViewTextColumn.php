<?php
namespace presentation\component;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

class ListViewTextColumn extends ListViewColumn {

    private $orderEnabled;
    private $type;
    private $format;

    public function __construct($name, $orderEnabled = true, $callback = null) {
        $this->setName($name);
        $this->setOrderEnabled($orderEnabled);
        $this->setCallback($callback);
    }

    public function getOrderEnabled() {
        return $this->orderEnabled;
    }

    public function setOrderEnabled($orderEnabled) {
        $this->orderEnabled = $orderEnabled;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getFormat() {
        return $this->format;
    }

    public function setFormat($format) {
        $this->format = $format;
    }
}
?>