<?php

namespace NestPay\Model;

use NestPay\Model\Db\DbInterface;

/**
 * Class Settings
 *
 * @package NestPay\Model
 */
class Settings {

    /**
     * Set merchant ID
     *
     * @param string $merchantId
     * @return $this
     */
    public function setMerchantId($merchantId) {
        $this->merchantId = $merchantId;
        return $this;
    }

    /**
     * Set store key
     *
     * @param string $storeKey
     * @return $this
     */
    public function setStoreKey($storeKey) {
        $this->storeKey = $storeKey;
        return $this;
    }

    /**
     * Set test mode
     *
     * @param boolean $testMode
     * @return $this
     */
    public function setTestMode($testMode=true) {
        $this->testMode = $testMode;
        return $this;
    }


    /**
     * Set API username
     *
     * @param string $apiUser
     * @return $this
     */
    public function setApiUser($apiUser) {
        $this->apiUser = $apiUser;
        return $this;
    }

    /**
     * Set API password
     *
     * @param string $apiPass
     * @return $this
     */
    public function setApiPass($apiPass) {
        $this->apiPass = $apiPass;
        return $this;
    }

    /**
     * Set DMS mode
     *
     * @param boolean $dmsMode
     * @return $this
     */
    public function setDmsMode($dmsMode) {
        $this->dmsMode = $dmsMode;
        return $this;
    }

    /**
     * Set log files directory
     *
     * @param string $logDir
     * @return $this
     */
    public function setLogDir($logDir) {
        $this->logDir = $logDir;
        return $this;
    }

    /**
     * Set MySQL db interface
     *
     * @return $this
     */
    public function setMySQLDbInterface() {
        $this->db = new Db\MySQL\DbInterface();
        return $this;
    }

    /**
     * Set MySQLi db interface
     *
     * @param \mysqli $cursor
     * @return $this
     */
    public function setMySQLiDbInterface(\mysqli $cursor) {
        $this->db = new Db\MySQLi\DbInterface($cursor);
        return $this;
    }

    /**
     * Get singleton instance
     *
     * @return Settings
     */
    public static function getInstance() {
        if (static::$instance == null)
            static::$instance = new Settings();
        return static::$instance;
    }

    // optional parameters, covered by default values

    /**
     * Set max tries for one order
     *
     * @param int $maxTries
     * @return $this
     */
    public function setMaxTries($maxTries) {
        $this->maxTries = $maxTries;
        return $this;
    }

    /**
     * Set refresh time
     *
     * @param int $refreshTime
     * @return $this
     */
    public function setRefreshTime($refreshTime) {
        $this->refreshTime = $refreshTime;
        return $this;
    }

    /**
     * Set API test URL
     *
     * @param string $apiTestUrl
     * @return $this
     */
    public function setApiTestUrl($apiTestUrl) {
        $this->apiTestUrl = $apiTestUrl;
        return $this;
    }

    /**
     * Set API URL
     *
     * @param string $apiUrl
     * @return $this
     */
    public function setApiUrl($apiUrl) {
        $this->apiUrl = $apiUrl;
        return $this;
    }

    /**
     * Set transactions table name
     *
     * @param string $transactionsTableName
     * @return $this
     */
    public function setTransactionsTableName($transactionsTableName) {
        $this->transactionsTableName = $transactionsTableName;
        return $this;
    }

    /**
     * Set orders table name
     *
     * @param string $ordersTableName
     * @return $this
     */
    public function setOrdersTableName($ordersTableName) {
        $this->ordersTableName = $ordersTableName;
        return $this;
    }

    /**
     * Set 3D hosted page test URL
     *
     * @param string $hosted3dTestUrl
     * @return $this
     */
    public function setHosted3dTestUrl($hosted3dTestUrl) {
        $this->hosted3dTestUrl = $hosted3dTestUrl;
        return $this;
    }

    /**
     * Set 3D hosted page URL
     *
     * @param string $hosted3dUrl
     * @return $this
     */
    public function setHosted3dUrl($hosted3dUrl) {
        $this->hosted3dUrl = $hosted3dUrl;
        return $this;
    }




    /**
     * Get Merchant ID
     *
     * @return string
     * @throws NestPayException
     */
    public function getMerchantId() {
        if (empty($this->merchantId))
            throw new NestPayException('merchant id not set');
        return $this->merchantId;
    }

    /**
     * @return null|string
     * @throws NestPayException
     */
    public function getStoreKey() {
        if (empty($this->storeKey))
            throw new NestPayException('store key not set');
        return $this->storeKey;
    }

    /**
     * @return string
     */
    public function getTransactionsTableName() {
        return $this->transactionsTableName;
    }

    /**
     * @return string
     */
    public function getOrdersTableName() {
        return $this->ordersTableName;
    }

    /**
     * Get database interface
     *
     * @return DbInterface
     * @throws NestPayException
     */
    public function getDbInterface() {
        if (empty($this->db))
            throw new NestPayException('Database interface not defined');
        return $this->db;
    }

    /**
     * @return boolean
     */
    public function isDmsMode() {
        return $this->dmsMode;
    }

    /**
     * @return int
     */
    public function getRefreshTime() {
        return $this->refreshTime;
    }

    /**
     * Set log files directory
     *
     * @return string
     * @throws NestPayException
     */
    public function getLogDir() {
        if (!$this->logDir)
            throw new NestPayException('log directory not set');
        return $this->logDir;
    }

    /**
     * Get max tries for one order
     *
     * @return int
     */
    public function getMaxTries() {
        return $this->maxTries;
    }

    /**
     * @return boolean
     */
    public function isTestMode() {
        return $this->testMode;
    }

    /**
     * @return string
     */
    public function getHosted3dUrl() {
        return $this->testMode ? $this->hosted3dTestUrl : $this->hosted3dUrl;
    }

    /**
     * @return string
     */
    public function getApiUrl() {
        return $this->testMode ? $this->apiTestUrl : $this->apiUrl;
    }

    /**
     * @return string
     * @throws NestPayException
     */
    public function getApiUser() {
        if (!$this->apiUser)
            throw new NestPayException('API user not defined');
        return $this->apiUser;
    }

    /**
     * @return string
     * @throws NestPayException
     */
    public function getApiPass() {
        if (!$this->apiUser)
            throw new NestPayException('API user not defined');
        return $this->apiPass;
    }

    /**
     * @return string
     */
    public function getProxyIp() {
        return $this->proxyIp;
    }

    /**
     * @param string $proxyIp
     * @return $this
     */
    public function setProxyIp($proxyIp) {
        $this->proxyIp = $proxyIp;
        return $this;
    }

    /**
     * @return int
     */
    public function getProxyPort() {
        return $this->proxyPort;
    }

    /**
     * @param int $proxyPort
     * @return $this
     */
    public function setProxyPort($proxyPort) {
        $this->proxyPort = $proxyPort;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDisabled3d() {
        return $this->disabled3d;
    }

    /**
     * @param boolean $disable3d
     * @return $this
     */
    public function setDisabled3d($disable3d=true) {
        $this->disabled3d = $disable3d;
        return $this;
    }


    /** @var string - Merchant ID */
    private $merchantId = null;
    /** @var string - store key */
    private $storeKey = null;
    /** @var string - transaction table name */
    private $transactionsTableName = 'nestpay_transactions';
    /** @var string - orders table name */
    private $ordersTableName = 'nestpay_orders';
    /** @var DbInterface */
    private $db = null;
    /** @var bool */
    private $dmsMode = false;
    /** @var int */
    private $refreshTime = 5;
    /** @var string */
    private $logDir = null;
    /** @var int */
    private $maxTries = 3;
    /** @var bool */
    private $testMode = false;
    /** @var string */
    private $hosted3dTestUrl = 'https://testsecurepay.eway2pay.com/fim/est3Dgate';
    /** @var string */
    private $hosted3dUrl = 'https://bib.eway2pay.com/fim/est3Dgate';
    /** @var string */
    private $apiTestUrl = 'https://testsecurepay.intesasanpaolocard.com/fim/api';
    /** @var string */
    private $apiUrl = 'https://bib.eway2pay.com/fim/api';
    /** @var string */
    private $apiUser = null;
    /** @var string */
    private $apiPass = null;
    /** @var string */
    private $proxyIp = null;
    /** @var int */
    private $proxyPort = null;
    /** @var bool */
    private $disabled3d = false;

    /** @var Settings */
    private static $instance = null;

    /**
     * Settings constructor.
     */
    private function __construct() {
    }

}