<?php
error_reporting(E_ALL ^ E_WARNING);

define('PAPRIKA_LIBRARY_PATH', realpath('library'));
define('PAPRIKA_CUSTOM_PATH', realpath('custom'));
define('ROOT_PATH', dirname(__FILE__));

require(PAPRIKA_LIBRARY_PATH.'/AppUtils.php');

AppUtils::getInstance()->import();

?>