<?php
namespace core;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

class SessionManager {

    const USER_ENTITY = '__ppk_user_entity';
    const USER_ROLE = '__ppk_user_role';
    const BASE_PREFIX = '__ppk_base_prefix';
    const URI_CURRENT = '__ppk_uri_current';
    const URI_PREVIOUS = '__ppk_uri_previous';
    const OUTPUT_MESSAGE = '__ppk_output_message';

    private static $instance = null;

    private function __construct() {
    }

    private function __clone() {
    }

    /**
     * @return SessionManager
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new SessionManager();
        }
        return self::$instance;
    }

    public function init() {
        if (isset($_POST['PHPSESSID'])) {
            session_id($_POST['PHPSESSID']);
        }
        session_start();
        setcookie('PHPSESSID', session_id(), 0, '/');
    }

    public function getValue($key) {
        if (\helper\StringHelper::isNull($key)) {
            throw new \Exception('$key is null');
        }
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return null;
        }
    }

    public function putValue($key, $value) {
        if (\helper\StringHelper::isNull($key)) {
            throw new \Exception('$key is null');
        }
        $_SESSION[$key] = $value;
    }

    public function destroy($key) {
        if (\helper\StringHelper::isNull($key)) {
            throw new \Exception('$key is null');
        }
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function saveUserRoles($entity, $profiles) {
        $this->putValue(self::USER_ENTITY, $entity);
        if (!is_array($profiles)) {
            $tmp = array($profiles);
            $profiles = $tmp;
        }
        $this->putValue(self::USER_ROLE, implode('|', $profiles));
    }

    public function getUserEntity() {
        return $this->getValue(self::USER_ENTITY);
    }

    public function destroyUserReference() {
        $this->destroy(self::USER_ENTITY);
        $this->destroy(self::USER_ROLE);
    }
}
?>