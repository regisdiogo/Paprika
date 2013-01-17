<?
namespace repository;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

use annotation\SupportRepository;
use core\exception\RepositoryException;

abstract class SupportDatabase extends SupportRepository {

    protected function getConnection($usesTransaction = false) {
        return PDOConnection::getInstance($usesTransaction);
    }

    /**
     * Commit changes in database
     */
    public function commit() {
        if (PDOConnection::isOpenTransaction()) {
            PDOConnection::getInstance()->commit();
            PDOConnection::setOpenTransaction(false);
        }
    }

    /**
     * Rollback changes in database
     */
    public function rollback() {
        if (PDOConnection::isOpenTransaction()) {
            PDOConnection::getInstance()->rollBack();
            PDOConnection::setOpenTransaction(false);
        }
    }
}

class PDOConnection {
    /**
     * Singleton PDO instance
     * @var PDO
     */
    private static $instance = null;

    private static $openTransaction = false;

    private function __construct() {
    }

    private function __clone(){
    }

    /**
     * Creates, when necessary, and returns a PDO instance
     * @return PDO
     */
    public static function getInstance($usesTransaction = false) {
        try {
            if (!self::$instance) {
                self::$instance = new \PDO(
                        \core\Config::DATABASE_ADDRESS,
                        \core\Config::DATABASE_USERNAME,
                        \core\Config::DATABASE_PASSWORD,
                        array(
                                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                                \PDO::ATTR_PERSISTENT => true
                        )
                );
                self::$instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
            if ($usesTransaction && !self::$openTransaction) {
                self::$instance->beginTransaction();
                self::$openTransaction = true;
            }
            return self::$instance;

        } catch (\Exception $e) {
            $ex = new RepositoryException($e);
            $ex->setFatal(true);
            throw $ex;
        }
    }

    /**
     * Checks thats have a open transaction
     */
    public static function isOpenTransaction() {
        return self::$openTransaction;
    }

    public static function setOpenTransaction($openTransaction) {
        self::$openTransaction = $openTransaction;
    }
}
?>