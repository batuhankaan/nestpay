<?php

namespace NestPay\Model;

use NestPay\Model\Db\DbField;
use NestPay\Model\Db\DbTable;

/**
 * Class OrderItem
 *
 * @package NestPay\Model
 */
class OrderItem {

    /**
     * Saves transaction to db
     *
     * @throws NestPayException
     */
    public function save() {
        $db = Settings::getInstance()->getDbInterface();
        if (empty($this->id)) {
            $sql = $db->getInsertSql(static::getTableDef(), $this->mapToArray());
            $db->execute($sql);
            $this->id = $db->getInsertId();
        } else {
            $sql = $db->getUpdateSql(static::getTableDef(), $this->mapToArray());
            $db->execute($sql);
        }
    }

    /**
     * Get new OrderItem instance
     *
     * @param int $nr
     * @param string $itemId
     * @param string $itemNumber
     * @param string $productCode
     * @param float $qty
     * @param string $description
     * @param float $unitPrice
     * @return OrderItem
     */
    public static function getNew($nr, $itemId, $itemNumber, $productCode, $qty, $description, $unitPrice) {
        $i = new OrderItem();
        $i->nr = $nr;
        $i->itemId = $itemId;
        $i->itemNumber = $itemNumber;
        $i->productCode = $productCode;
        $i->qty = $qty;
        $i->description = $description;
        $i->unitPrice = $unitPrice;
        return $i;
    }

    /**
     * Get parameters for submitting to 3d host page
     *
     * @return array
     */
    public function getParamsForOrder() {
        return array(
            'id' . $this->nr => $this->itemId,
            'itemnumber' . $this->nr => $this->itemNumber,
            'productcode' . $this->nr => $this->productCode,
            'desc' . $this->nr => $this->description,
            'qty' . $this->nr => $this->qty,
            'price' . $this->nr => $this->unitPrice,
            'total' . $this->nr => $this->qty * $this->unitPrice,
        );
    }

    /**
     * Get items for given order id
     *
     * @param  int $orderId - internal order id
     * @return OrderItem[]
     * @throws NestPayException
     */
    public static function getItemsForOrder($orderId) {
        $sql = sprintf(
            'SELECT'.' * FROM '.static::getTableDef()->getName().' WHERE orderId=%d ORDER BY nr',
            $orderId
        );
        return static::getForSql($sql);
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getOrderId() {
        return $this->orderId;
    }

    /**
     * @return int
     */
    public function getNr() {
        return $this->nr;
    }

    /**
     * @return string
     */
    public function getItemId() {
        return $this->itemId;
    }

    /**
     * @return string
     */
    public function getItemNumber() {
        return $this->itemNumber;
    }

    /**
     * @return string
     */
    public function getProductCode() {
        return $this->productCode;
    }

    /**
     * @return float
     */
    public function getQty() {
        return $this->qty;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return float
     */
    public function getUnitPrice() {
        return $this->unitPrice;
    }

    /**
     * Get all items for sql
     *
     * @param  string $sql
     * @return OrderItem[]
     * @throws NestPayException
     */
    private static function getForSql($sql) {
        /** @var OrderItem[] */
        $items = array();
        $db = Settings::getInstance()->getDbInterface();
        foreach ($db->getRows($sql) as $row)
            $items[] = static::getFromArray($row);
        return $items;
    }

    /**
     * Get table def
     *
     * @return DbTable
     */
    public static function getTableDef() {
        static::initTableDef();
        return static::$tableDef;
    }

    /** @var int - local id */
    private $id = null;
    /** @var int - local order id  */
    private $orderId = null;
    /** @var int - item number */
    private $nr = null;

    /** @var string - idl - Id of item #l, required for item #l, Maximum 128 characters */
    private $itemId = null;
    /** @var string - itemnumberl - Item number of item #l, Maximum 128 characters */
    private $itemNumber = null;
    /** @var string - productcodel - Product code of item #l, Maximum 64 characters */
    private $productCode = null;
    /** @var float - qtyl - Quantity of item #l, Maximum 32 characters */
    private $qty = null;
    /** @var string - descl - Description of item #l, Maximum 128 characters */
    private $description = null;
    /** @var float - pricel - Price of item #l, Maximum 32 characters */
    private $unitPrice = null;

    /**
     * @param int $orderId
     */
    public function setOrderId($orderId) {
        $this->orderId = $orderId;
    }

    /**
     * Map object to array
     *
     * @return array
     */
    private function mapToArray() {
        return array(
            'id' => $this->id,
            'orderId' => $this->orderId,
            'nr' => $this->nr,
            'itemId' => $this->itemId,
            'itemNumber' => $this->itemNumber,
            'productCode' => $this->productCode,
            'qty' => $this->qty,
            'description' => $this->description,
            'unitPrice' => $this->unitPrice,
        );
    }

    /**
     * Get object from array
     *
     * @param  array $params
     * @return OrderItem
     */
    private static function getFromArray($params = array()) {
        $t = new OrderItem();
        $t->id = isset($params['id']) ? $params['id'] : null;
        $t->orderId = isset($params['orderId']) ? $params['orderId'] : null;
        $t->nr = isset($params['nr']) ? $params['nr'] : null;
        $t->itemId = isset($params['itemId']) ? $params['itemId'] : null;
        $t->itemNumber = isset($params['itemNumber']) ? $params['itemNumber'] : null;
        $t->productCode = isset($params['productCode']) ? $params['productCode'] : null;
        $t->qty = isset($params['qty']) ? $params['qty'] : null;
        $t->description = isset($params['description']) ? $params['description'] : null;
        $t->unitPrice = isset($params['unitPrice']) ? $params['unitPrice'] : null;
        return $t;
    }

    /**
     * Init table def
     */
    private static function initTableDef() {
        if (static::$tableDef == null) {
            static::$tableDef = new DbTable(Settings::getInstance()->getOrdersTableName() . '_items');
            static::$tableDef
                ->addField(
                    new DbField('id', \NestPay\Model\Db\TYPE_INTEGER, 11, null, false, true, false)
                )
                ->addField(
                    new DbField('orderId', \NestPay\Model\Db\TYPE_INTEGER, 11, null, false, false, true)
                )
                ->addField(
                    new DbField('nr', \NestPay\Model\Db\TYPE_INTEGER, 6)
                )
                ->addField(
                    new DbField('itemId', \NestPay\Model\Db\TYPE_VARCHAR, 128)
                )
                ->addField(
                    new DbField('itemNumber', \NestPay\Model\Db\TYPE_VARCHAR, 128)
                )
                ->addField(
                    new DbField('productCode', \NestPay\Model\Db\TYPE_VARCHAR, 64)
                )
                ->addField(
                    new DbField('qty', \NestPay\Model\Db\TYPE_DECIMAL, '20,2')
                )
                ->addField(
                    new DbField('description', \NestPay\Model\Db\TYPE_VARCHAR, 128)
                )
                ->addField(
                    new DbField('unitPrice', \NestPay\Model\Db\TYPE_DECIMAL, '20,2')
                )
            ;
        }
    }

    /** @var DbTable */
    private static $tableDef = null;

}