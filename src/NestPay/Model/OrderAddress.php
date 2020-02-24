<?php

namespace NestPay\Model;

use NestPay\Model\Db\DbField;
use NestPay\Model\Db\DbTable;

/**
 * Class OrderAddress
 *
 * @package NestPay\Model
 */
class OrderAddress {

    /**
     * Get new billing address
     *
     * @param string $company
     * @param string $name
     * @param string $street1
     * @param string $street2
     * @param string $city
     * @param string $stateProv
     * @param string $postalCode
     * @param string $country
     * @return OrderAddress
     */
    public static function makeBillingAddress($company, $name, $street1, $street2, $city,
                                $stateProv, $postalCode, $country) {

        return new OrderAddress($company, $name, $street1, $street2, $city,
            $stateProv, $postalCode, $country, false);
    }

    /**
     * Get new shipping address
     *
     * @param string $company
     * @param string $name
     * @param string $street1
     * @param string $street2
     * @param string $city
     * @param string $stateProv
     * @param string $postalCode
     * @param string $country
     * @return OrderAddress
     */
    public static function makeShippingAddress($company, $name, $street1, $street2, $city,
                                              $stateProv, $postalCode, $country) {

        return new OrderAddress($company, $name, $street1, $street2, $city,
            $stateProv, $postalCode, $country, true);
    }

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
     * Get parameters for submitting to 3d host page
     *
     * @return array
     */
    public function getParamsForOrder() {
        return array(
            ($this->isShipping) ? 'ShipToCompany' : 'BillToCompany' => $this->company,
            ($this->isShipping) ? 'ShipToName' : 'BillToName' => $this->name,
            ($this->isShipping) ? 'ShipToStreet1' : 'BillToStreet1' => $this->street1,
            ($this->isShipping) ? 'ShipToStreet2' : 'BillToStreet2' => $this->street2,
            ($this->isShipping) ? 'ShipToCity' : 'BillToCity' => $this->city,
            ($this->isShipping) ? 'ShipToStateProv' : 'BillToStateProv' => $this->stateProv,
            ($this->isShipping) ? 'ShipToPostalCode' : 'BillToPostalCode' => $this->postalCode,
            ($this->isShipping) ? 'ShipToCountry' : 'BillToCountry' => $this->country,
        );
    }

    /**
     * Get local id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCompany() {
        return $this->company;
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
    public function getStreet1() {
        return $this->street1;
    }

    /**
     * @return string
     */
    public function getStreet2() {
        return $this->street2;
    }

    /**
     * @return string
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getStateProv() {
        return $this->stateProv;
    }

    /**
     * @return string
     */
    public function getPostalCode() {
        return $this->postalCode;
    }

    /**
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * @return boolean
     */
    public function isShipping() {
        return $this->isShipping;
    }


    /**
     * Get items for given order id
     *
     * @param  int $id - internal id
     * @return OrderAddress|null
     * @throws NestPayException
     */
    public static function getById($id) {
        $sql = sprintf(
            'SELECT'.' * FROM '.static::getTableDef()->getName().' WHERE id=%d',
            $id
        );
        $addr = static::getForSql($sql);
        return count($addr) ? array_shift($addr) : null;
    }

    /**
     * Get all transactions for sql
     *
     * @param  string $sql
     * @return OrderAddress[]
     * @throws NestPayException
     */
    private static function getForSql($sql) {
        /** @var OrderAddress[] */
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

    /**
     * Map object to array
     *
     * @return array
     */
    private function mapToArray() {
        return array(
            'id' => $this->id,
            'company' => $this->company,
            'name' => $this->name,
            'street1' => $this->street1,
            'street2' => $this->street2,
            'city' => $this->city,
            'stateProv' => $this->stateProv,
            'postalCode' => $this->postalCode,
            'country' => $this->country,
            'isShipping' => $this->isShipping,
        );
    }

    /**
     * Get object from array
     *
     * @param  array $params
     * @return OrderItem
     */
    private static function getFromArray($params = array()) {
        $t = new OrderAddress();
        $t->id = isset($params['id']) ? $params['id'] : null;
        $t->company = isset($params['company']) ? $params['company'] : null;
        $t->name = isset($params['name']) ? $params['name'] : null;
        $t->street1 = isset($params['street1']) ? $params['street1'] : null;
        $t->street2 = isset($params['street2']) ? $params['street2'] : null;
        $t->city = isset($params['city']) ? $params['city'] : null;
        $t->stateProv = isset($params['stateProv']) ? $params['stateProv'] : null;
        $t->postalCode = isset($params['postalCode']) ? $params['postalCode'] : null;
        $t->country = isset($params['country']) ? $params['country'] : null;
        $t->isShipping = isset($params['isShipping']) ? $params['isShipping'] : null;
        return $t;
    }

    /**
     * Init table def
     */
    private static function initTableDef() {
        if (static::$tableDef == null) {
            static::$tableDef = new DbTable(Settings::getInstance()->getOrdersTableName() . '_address');
            static::$tableDef
                ->addField(
                    new DbField('id', \NestPay\Model\Db\TYPE_INTEGER, 11, null, false, true, false)
                )
                ->addField(
                    new DbField('company', \NestPay\Model\Db\TYPE_VARCHAR, 255)
                )
                ->addField(
                    new DbField('name', \NestPay\Model\Db\TYPE_VARCHAR, 255)
                )
                ->addField(
                    new DbField('street1', \NestPay\Model\Db\TYPE_VARCHAR, 255)
                )
                ->addField(
                    new DbField('street2', \NestPay\Model\Db\TYPE_VARCHAR, 255)
                )
                ->addField(
                    new DbField('city', \NestPay\Model\Db\TYPE_VARCHAR, 64)
                )
                ->addField(
                    new DbField('stateProv', \NestPay\Model\Db\TYPE_VARCHAR, 32)
                )
                ->addField(
                    new DbField('postalCode', \NestPay\Model\Db\TYPE_VARCHAR, 32)
                )
                ->addField(
                    new DbField('country', \NestPay\Model\Db\TYPE_VARCHAR, 3)
                )
                ->addField(
                    new DbField('isShipping', \NestPay\Model\Db\TYPE_INTEGER, 1)
                )
            ;
        }
    }

    /** @var DbTable */
    private static $tableDef = null;

    /** @var int - local id */
    private $id = null;
    /** @var string
     * BillToCompany - BillTo company name, Maximum 255 characters
     * ShipToCompany - ShipTo company,Maximum 255 characters
     */
    private $company = null;
    /** @var string
     * BillToName - BillTo name/surname, Maximum 255 characters
     * ShipToName - ShipTo name, Maximum 255 characters
     */
    private $name = null;
    /** @var string
     * BillToStreet1 - BillTo address line 1, Maximum 255 characters
     * ShipToStreet1 - ShipTo address line 1, Maximum 255 characters
     */
    private $street1 = null;
    /** @var string
     * BillToStreet2 - BillTo address line 2, Maximum 255 characters
     * ShipToStreet2 - ShipTo address line 2, Maximum 255 characters
     */
    private $street2 = null;
    /** @var string
     * BillToCity - BillTo city, Maximum 64 characters
     * ShipToCity - ShipTo city, Maximum 64 characters
     */
    private $city = null;
    /** @var string
     * BillToStateProv - BillTo state/province, Maximum 32 characters
     * ShipToStateProv - ShipTo state/province, Maximum 32 characters
     */
    private $stateProv = null;
    /** @var string
     * BillToPostalCode - BillTo postal code, Maximum 32 characters
     * ShipToPostalCode - ShipTo postal code, Maximum 32 characters
     */
    private $postalCode = null;
    /** @var string
     * BillToCountry - BillTo country code, Maximum 3 characters
     * ShipToCountry - ShipTo country code, Maximum 3 characters
     */
    private $country = null;
    /** @var bool */
    private $isShipping = false;

    /**
     * OrderAddress constructor
     *
     * @param string $company
     * @param string $name
     * @param string $street1
     * @param string $street2
     * @param string $city
     * @param string $stateProv
     * @param string $postalCode
     * @param string $country
     * @param bool $isShipping
     */
    private function __construct($company=null, $name=null, $street1=null, $street2=null,
                $city=null, $stateProv=null, $postalCode=null, $country=null, $isShipping=null) {
        $this->company = $company;
        $this->name = $name;
        $this->street1 = $street1;
        $this->street2 = $street2;
        $this->city = $city;
        $this->stateProv = $stateProv;
        $this->postalCode = $postalCode;
        $this->country = $country;
        $this->isShipping = $isShipping;
    }


}