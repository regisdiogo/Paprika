<?php
namespace core\exception;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

class ValidationException extends BaseException {

    public function __construct($parameters) {
        parent::__construct(__CLASS__, $parameters);
    }
}
?>