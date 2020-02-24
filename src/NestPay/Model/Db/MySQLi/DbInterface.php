<?php

namespace NestPay\Model\Db\MySQLi;

use NestPay\Model\NestPayException;

/**
 * Class DbInterface
 *
 * @package NestPay\Model\Db\MySQLi
 */
class DbInterface extends \NestPay\Model\Db\MySQLCommon\DbInterface {


    /**
     * Execute SQL statement
     *
     * @param  string $sql
     * @return bool
     */
    public function execute($sql) {
        return $this->cursor->query($sql);
    }

    /**
     * Get rows
     *
     * @param  string $sql
     * @return array
     * @throws NestPayException
     */
    public function getRows($sql) {
        $rows = array();
        if ($result = $this->cursor->query($sql)) {
            while ($row = $result->fetch_assoc())
                $rows[] = $row;
            $result->free();
        } else
            throw new NestPayException('db error: ' . $this->cursor->error);
        return $rows;
    }

    /**
     * Escape field value for query
     *
     * @param  string $val
     * @return string
     */
    public function escape($val) {
        return $this->cursor->real_escape_string($val);
    }

    /**
     * Get last insert id
     *
     * @return int
     */
    public function getInsertId() {
        return $this->cursor->insert_id;
    }

    /**
     * DbInterface constructor.
     *
     * @param \mysqli $cursor
     */
    public function __construct($cursor) {
        $this->cursor = $cursor;
    }

    /**
     * @var \mysqli
     */
    private $cursor = null;

}