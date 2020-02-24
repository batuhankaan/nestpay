<?php

namespace NestPay\Model\Db\MySQLCommon;

use NestPay\Model\Db\DbTable;
use NestPay\Model\NestPayException;

/**
 * Class DbInterface
 *
 * @package NestPay\Model\Db\MySQLCommon
 */
abstract class DbInterface extends \NestPay\Model\Db\DbInterface {

    /**
     * Get create table SQL
     *
     * @param  DbTable $table
     * @return string
     * @throws NestPayException
     */
    public function getCreateSql(DbTable $table) {
        $header = sprintf("CREATE"." TABLE IF NOT EXISTS `%s` ( ", $table->getName());
        $fields = array();
        $indexes = array();
        foreach ($table->getFields() as $f) {
            $name = $f->getName();
            $nillable = $f->isNullable() ? "NULL" : "NOT NULL";
            $autoincrement = $f->isPk() ? "AUTO_INCREMENT" : "";
            switch ($f->getType()) {
                case \NestPay\Model\Db\TYPE_VARCHAR:
                    $type = sprintf('varchar(%s)', $f->getLen());
                    break;
                case \NestPay\Model\Db\TYPE_TEXT:
                    $type = 'text';
                    break;
                case \NestPay\Model\Db\TYPE_DECIMAL:
                    $type = sprintf('decimal(%s)', $f->getLen());
                    break;
                case \NestPay\Model\Db\TYPE_INTEGER:
                    $type = sprintf('int(%s)', $f->getLen());
                    break;
                case \NestPay\Model\Db\TYPE_TIMESTAMP:
                    $type = 'timestamp';
                    break;
                default:
                    throw new NestPayException(
                        sprintf(
                            'Undefined type: %s for field %s',
                            $f->getType(),
                            $f->getName()
                        )
                    );
            }
            if ($f->getDefault())
                $default = sprintf("DEFAULT '%s'", $f->getDefault());
            else if ($f->isNullable())
                $default = "DEFAULT NULL";
            else
                $default = "";

            $fields[] = sprintf("`%s` %s %s %s %s", $name, $type, $nillable, $default, $autoincrement);

            if ($f->isPk())
                $indexes[] = sprintf("PRIMARY KEY (`%s`)", $f->getName());
            else if ($f->isIndex())
                $indexes[] = sprintf("KEY `%s` (`%s`)", $f->getName(), $f->getName());

        }
        $footer = ") ENGINE=InnoDB DEFAULT CHARSET=utf8 ";
        return $header . "\n " . implode(",\n", array_merge($fields, $indexes)) . "\n" . $footer;
    }


    /**
     * Get insert SQL
     *
     * @param DbTable $table
     * @param array $fieldValues
     * @return string
     * @throws NestPayException
     */
    public function getInsertSql(DbTable $table, $fieldValues = array()) {
        $fields = array();
        $values = array();
        foreach ($table->getFields() as $f) {
            if (!$f->isPk()) {
                $fields[] = sprintf('`%s`', $f->getName());
                switch ($f->getType()) {
                    case \NestPay\Model\Db\TYPE_TEXT:
                    case \NestPay\Model\Db\TYPE_VARCHAR:
                    case \NestPay\Model\Db\TYPE_TIMESTAMP:
                        $values[] = !empty($fieldValues[$f->getName()])
                            ? sprintf('"%s"', $this->escape($fieldValues[$f->getName()]))
                            : 'NULL';
                        break;
                    case \NestPay\Model\Db\TYPE_DECIMAL:
                        $values[] = !empty($fieldValues[$f->getName()])
                            ? sprintf('%f', $fieldValues[$f->getName()])
                            : 'NULL';
                        break;
                    case \NestPay\Model\Db\TYPE_INTEGER:
                        $values[] = !empty($fieldValues[$f->getName()])
                            ? sprintf('%d', $fieldValues[$f->getName()])
                            : 'NULL';
                        break;
                    default:
                        throw new NestPayException(
                            sprintf(
                                'Undefined type: %s for field %s',
                                $f->getType(),
                                $f->getName()
                            )
                        );
                }
            }
        }
        return sprintf(
            'INSERT'.' INTO `%s` (%s) VALUES (%s)',
            $table->getName(),
            implode(', ', $fields),
            implode(', ', $values)
        );
    }


    /**
     * Get update SQL
     *
     * @param DbTable $table
     * @param array $fieldValues
     * @return string
     * @throws NestPayException
     */
    public function getUpdateSql(DbTable $table, $fieldValues = array()) {

        $pairs = array();
        $where = null;

        foreach ($table->getFields() as $f) {
            switch ($f->getType()) {
                case \NestPay\Model\Db\TYPE_TEXT:
                case \NestPay\Model\Db\TYPE_VARCHAR:
                case \NestPay\Model\Db\TYPE_TIMESTAMP:
                    $pair = !empty($fieldValues[$f->getName()])
                        ? sprintf(
                            '`%s`="%s"',
                            $f->getName(),
                            $this->escape($fieldValues[$f->getName()])
                        )
                        : sprintf(
                            '`%s`=NULL',
                            $f->getName()
                        );
                    break;
                case \NestPay\Model\Db\TYPE_DECIMAL:
                    $pair = !empty($fieldValues[$f->getName()])
                        ? sprintf(
                            '`%s`=%f',
                            $f->getName(),
                            $fieldValues[$f->getName()]
                        )
                        : sprintf(
                            '`%s`=NULL',
                            $f->getName()
                        );
                    break;
                case \NestPay\Model\Db\TYPE_INTEGER:
                    $pair = !empty($fieldValues[$f->getName()])
                        ? sprintf(
                            '`%s`=%d',
                            $f->getName(),
                            $fieldValues[$f->getName()]
                        )
                        : sprintf(
                            '`%s`=NULL',
                            $f->getName()
                        );
                    break;
                default:
                    throw new NestPayException(
                        sprintf(
                            'Undefined type: %s for field %s',
                            $f->getType(),
                            $f->getName()
                        )
                    );
            }
            if ($f->isPk())
                $where = $pair;
            else
                $pairs[] = $pair;
        }
        return sprintf(
            'UPDATE'.' `%s` SET %s WHERE %s',
            $table->getName(),
            implode(', ', $pairs),
            $where
        );
    }
}