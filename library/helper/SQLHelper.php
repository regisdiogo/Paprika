<?php
namespace helper;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

class SQLHelper {

    const MYSQL_PDO_PARAM_DELIMITER = ':';

    const SELECT_BY_ID = 'SELECT %1$s, %3$s FROM %4$s WHERE %1$s = %2$s';
    const UPDATE_BY_ID = 'UPDATE %4$s SET %3$s WHERE %1$s = %2$s';
    const INSERT = 'INSERT INTO %3$s (%1$s) VALUES (%2$s)';
    const DELETE_BY_ID = 'DELETE FROM %3$s WHERE %1$s = %2$s';
    const SELECT_COUNT_FULL = 'SELECT COUNT(1) AS COUNT FROM %1$s';
    const SELECT_WITH_OPTIONS = 'SELECT %1$s FROM %2$s %3$s %4$s ';
    const ORDER_BY = 'ORDER BY %1$s %2$s';
    const LIMIT = 'LIMIT %1$s, %2$s';
    const WHERE = 'WHERE';

    public static function createSelectWithPagination($columns, $tableName, $offset, $rowsPerSelect, $orderBy, $orderDirection, $where = null) {
        $sqlWhere = array();
        if ($where) {
            $where = implode(' AND ', $where);
            $sqlWhere[] = self::WHERE;
            $sqlWhere[] = $where;
        }
        $sqlOptions = array();
        if ($orderBy) {
            $sqlOptions[] = sprintf(self::ORDER_BY, $orderBy, $orderDirection);
        }
        if (strlen($offset) > 0 && strlen($rowsPerSelect) > 0) {
            $sqlOptions[] = sprintf(self::LIMIT, $offset, $rowsPerSelect);
        }
        return sprintf(self::SELECT_WITH_OPTIONS, implode(', ', $columns), $tableName, implode(' ', $sqlWhere), implode(' ', $sqlOptions));
    }

    public static function createSelectWithoutPagination($columns, $tableName, $orderBy, $orderDirection, $where = null) {
        $sqlWhere = array();
        if ($where) {
            $where = implode(' AND ', $where);
            $sqlWhere[] = self::WHERE;
            $sqlWhere[] = $where;
        }
        $sqlOptions = array();
        if ($orderBy) {
            $sqlOptions[] = sprintf(self::ORDER_BY, $orderBy, $orderDirection);
        }
        if (strlen($offset) > 0 && strlen($rowsPerSelect) > 0) {
            $sqlOptions[] = sprintf(self::LIMIT, $offset, $rowsPerSelect);
        }
        return sprintf(self::SELECT_WITH_OPTIONS, implode(', ', $columns), $tableName, implode(' ', $sqlWhere), implode(' ', $sqlOptions));
    }

    public static function createSelectCountFull($tableName) {
        return sprintf(self::SELECT_COUNT_FULL, $tableName);
    }

    public static function createSelectById($identity, $columns, $tableName) {
        $value = self::MYSQL_PDO_PARAM_DELIMITER.$identity;
        return sprintf(self::SELECT_BY_ID, $identity, $value, implode(', ', $columns), $tableName);
    }

    public static function createUpdateById($identity, $columns, $tableName) {
        $sqlColumns = array();
        if ($columns) {
            foreach ($columns as $column) {
                $sqlColumns[] = $column.' = '.self::MYSQL_PDO_PARAM_DELIMITER.$column;
            }
        }
        $value = self::MYSQL_PDO_PARAM_DELIMITER.$identity;
        return sprintf(self::UPDATE_BY_ID, $identity, $value, implode(', ', $sqlColumns), $tableName);
    }

    public static function createInsert($columns, $tableName) {
        $values = array();
        foreach ($columns as $column) {
            $values[] = self::MYSQL_PDO_PARAM_DELIMITER.$column;
        }
        return sprintf(self::INSERT, implode(', ', $columns), implode(', ', $values), $tableName);
    }

    public static function createDeleteById($identity, $tableName) {
        $value = self::MYSQL_PDO_PARAM_DELIMITER.$identity;
        return sprintf(self::DELETE_BY_ID, $identity, $value, $tableName);
    }
}
?>