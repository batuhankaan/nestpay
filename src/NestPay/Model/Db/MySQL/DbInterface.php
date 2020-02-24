<?php

namespace NestPay\Model\Db\MySQL;

use NestPay\Model\NestPayException;

/**
 * Class DbInterface
 *
 * @package NestPay\Model\Db\MySQL
 */
class DbInterface extends \NestPay\Model\Db\MySQLCommon\DbInterface {

    /**
     * Execute SQL statement
     *
     * @param  string $sql
     * @return bool
     */
    public function execute($sql) {
        return mysql_query($sql);
    }

    /**
     * Get rows
     *
     * @param  string $sql
     * @return array
     * @throws NestPayException
     */
    public function getRows($sql) {
        $result = mysql_query($sql);
        if ($result) {
            $rows = array();
            while($row = mysql_fetch_assoc($result))
                $rows[] = $row;
            mysql_free_result($result);
            return $rows;
        } else
            throw new NestPayException(mysql_error());
    }

    /**
     * Escape field value for query
     *
     * @param  string $val
     * @return string
     */
    public function escape($val) {
        return mysql_real_escape_string($val);
    }

    /**
     * Get last insert id
     *
     * @return int
     */
    public function getInsertId() {
        return mysql_insert_id();
    }
}