<?php

namespace NestPay\Model\Db;

/**
 * Class DbTable
 *
 * @package NestPay\Model\Db
 */
class DbTable {

    /** @var string */
    private $name;
    /** @var DbField[] */
    private $fields = array();

    /**
     * Constructor
     *
     * @param $name
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * Add DB field definition
     *
     * @param DbField $f
     * @return $this
     */
    public function addField(DbField $f) {
        $this->fields[] = $f;
        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return DbField[]
     */
    public function getFields() {
        return $this->fields;
    }

}