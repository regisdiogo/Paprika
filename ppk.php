<?php

define('PAPRIKA_PATH', realpath('library'));

require(PAPRIKA_PATH.'/App.php');

App::getInstance()->init();

?>