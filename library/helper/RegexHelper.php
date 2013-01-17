<?php
namespace helper;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

class RegexHelper {

    const EMAIL_PATTERN = '/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/';

    public static function isEmailValid($email) {
        $matches = null;
        preg_match(self::EMAIL_PATTERN, $email, $matches);
        return isset($matches) && count($matches) > 0;
    }
}
?>