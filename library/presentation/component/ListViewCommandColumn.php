<?php
namespace presentation\component;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

class ListViewCommandColumn extends ListViewColumn {

    public function __construct($name, $callback) {
        $this->setName($name);
        $this->setCallback($callback);
    }

}
?>