<?php

namespace NestPay\Model\Db;

use NestPay\Model\NestPayException;

/**
 * Class DbInterface
 *
 * @package NestPay\Model\Db
 */
abstract class DbInterface {

    /**
     * Get create table SQL
     *
     * @param  DbTable $table
     * @return string
     * @throws NestPayException
     */
    public abstract function getCreateSql(DbTable $table);

    /**
     * Get insert SQL
     *
     * @param DbTable $table
     * @param array $fieldValues
     * @return string
     * @throws NestPayException
     */
    public abstract function getInsertSql(DbTable $table, $fieldValues=array());

    /**
     * Get update SQL
     *
     * @param DbTable $table
     * @param array $fieldValues
     * @return string
     * @throws NestPayException
     */
    public abstract function getUpdateSql(DbTable $table, $fieldValues=array());

    /**
     * Execute SQL statement
     *
     * @param  string $sql
     * @return bool
     */
    public abstract function execute($sql);

    /**
     * Get rows
     *
     * @param  string $sql
     * @return array
     */
    public abstract function getRows($sql);

    /**
     * Escape field value for query
     *
     * @param  string $val
     * @return string
     */
    public abstract function escape($val);

    /**
     * Get last insert id
     *
     * @return int
     */
    public abstract function getInsertId();

}