<?php

namespace NestPay\Model\Db;

const TYPE_VARCHAR = 'varchar';
const TYPE_INTEGER = 'integer';
const TYPE_DECIMAL = 'decimal';
const TYPE_TIMESTAMP = 'timestamp';
const TYPE_TEXT = 'text';

/**
 * Class DbField
 *
 * @package NestPay\Model\Db
 */
class DbField {

    /** @var string - db field name */
    private $name;
    /** @var string - TYPE_VARCHAR | TYPE_INTEGER | TYPE_DECIMAL | TYPE_TIMESTAMP | TYPE_TEXT */
    private $type;
    /** @var string - length (eg. "20,2" for decimal, "16" for varchar) */
    private $len;
    /** @var string - default value */
    private $default;
    /** @var boolean - if nullabe */
    private $nullable;
    /** @var boolean - if primary key; autoincrement is assumed */
    private $isPk;
    /** @var boolean - if should be indexed */
    private $isIndex;

    /**
     * DbField constructor.
     * @param string $name
     * @param string $type
     * @param string $len
     * @param string $default
     * @param bool $nullable
     * @param bool $isPk
     * @param bool $isIndex
     */
    public function __construct($name, $type, $len,
                    $default=null, $nullable=true, $isPk=false, $isIndex=false) {
        $this->name = $name;
        $this->type = $type;
        $this->len = $len;
        $this->default = $default;
        $this->nullable = $nullable;
        $this->isPk = $isPk;
        $this->isIndex = $isIndex;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLen() {
        return $this->len;
    }

    /**
     * @return string
     */
    public function getDefault() {
        return $this->default;
    }

    /**
     * @return boolean
     */
    public function isNullable() {
        return $this->nullable;
    }

    /**
     * @return boolean
     */
    public function isPk() {
        return $this->isPk;
    }

    /**
     * @return boolean
     */
    public function isIndex() {
        return $this->isIndex;
    }

}