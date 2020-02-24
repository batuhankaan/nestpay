<?php

namespace NestPay\Model;

use NestPay\Model\Db\DbField;
use NestPay\Model\Db\DbTable;

const STATUS_FAILED = 0;
const STATUS_AUTHORIZED = 1;
const STATUS_CAPTURED = 2;
const STATUS_VOIDED = 3;

/**
 * Class Transaction
 *
 * @package NestPay\Model
 */
class Transaction {

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
     * Get all transactions for given order id
     *
     * @param  string $orderNr
     * @return Transaction[]
     * @throws NestPayException
     */
    public static function getAllForOrderNr($orderNr) {
        $sql = sprintf(
            'SELECT'.' * FROM '.static::getTableDef()->getName().' WHERE oid="%s" ORDER BY id ASC',
            Settings::getInstance()->getDbInterface()->escape($orderNr)
        );
        return static::getForSql($sql);
    }

    /**
     * Get last transaction for given order id
     *
     * @param  string $orderNr
     * @return Transaction|null
     * @throws NestPayException
     */
    public static function getLastForOrderNr($orderNr) {
        $sql = sprintf(
            'SELECT'.' * FROM '.static::getTableDef()->getName().' WHERE oid="%s" ORDER BY id DESC',
            Settings::getInstance()->getDbInterface()->escape($orderNr)
        );
        $transactions = static::getForSql($sql);
        return count($transactions) ? array_shift($transactions) : null;
    }

    /**
     * Get successful transaction for given order id
     *
     * @param  string $orderNr
     * @return Transaction|null
     * @throws NestPayException
     */
    public static function getSuccessfulForOrderNr($orderNr) {
        $sql = sprintf(
            'SELECT'.' * FROM '.static::getTableDef()->getName().' WHERE oid="%s" AND procReturnCode="00"',
            Settings::getInstance()->getDbInterface()->escape($orderNr)
        );
        $transactions = static::getForSql($sql);
        return count($transactions) ? array_shift($transactions) : null;
    }

    /**
     * Get transaction for given oid/xid combination
     *
     * @param  string $orderNr
     * @param  string $xid
     * @return Transaction|null
     * @throws NestPayException
     */
    public static function getForOrderNrAndTransactionId($orderNr, $xid) {
        $sql = sprintf(
            'SELECT'.' * FROM '.static::getTableDef()->getName().' WHERE oid="%s" AND xid="%s"',
            Settings::getInstance()->getDbInterface()->escape($orderNr),
            Settings::getInstance()->getDbInterface()->escape($xid)
        );
        $transactions = static::getForSql($sql);
        return count($transactions) ? array_shift($transactions) : null;
    }

    /**
     * Map object to array
     *
     * @return array
     */
    public function mapToArray() {
        return array(
            'id' => $this->id,
            'orderId' => $this->orderId,
            'oid' => $this->oid,
            'authCode' => $this->authCode,
            'xid' => $this->xid,
            'response' => $this->response,
            'procReturnCode' => $this->procReturnCode,
            'transId' => $this->transId,
            'errMsg' => $this->errMsg,
            'clientIp' => $this->clientIp,
            'maskedPan' => $this->maskedPan,
            'cardBrand' => $this->cardBrand,
            'expYear' => $this->expYear,
            'expMonth' => $this->expMonth,
            'extraTrxDate' => $this->extraTrxDate,
            'mdStatus' => $this->mdStatus,
            'txstatus' => $this->txstatus,
            'iReqCode' => $this->iReqCode,
            'iReqDetail' => $this->iReqDetail,
            'vendorCode' => $this->vendorCode,
            'paResSyntaxOK' => $this->paResSyntaxOK,
            'paResVerified' => $this->paResVerified,
            'eci' => $this->eci,
            'cavv' => $this->cavv,
            'cavvAlgorithm' => $this->cavvAlgorithm,
            'md' => $this->md,
            'version' => $this->version,
            'sid' => $this->sid,
            'mdErrorMsg' => $this->mdErrorMsg,
            'status' => $this->status,
            'timeAuthorized' => $this->timeAuthorized,
            'timeCaptoredOrVoided' => $this->timeCaptoredOrVoided,
        );
    }

    /**
     * Get object from array
     *
     * @param  array $params
     * @return Transaction
     */
    public static function getFromArray($params = array()) {
        $t = new Transaction();
        $t->id = isset($params['id']) ? $params['id'] : null;
        $t->orderId = isset($params['orderId']) ? $params['orderId'] : null;
        $t->oid = isset($params['oid']) ? $params['oid'] : null;
        $t->authCode = isset($params['authCode']) ? $params['authCode'] : null;
        $t->xid = isset($params['xid']) ? $params['xid'] : null;
        $t->response = isset($params['response']) ? $params['response'] : null;
        $t->procReturnCode = isset($params['procReturnCode']) ? $params['procReturnCode'] : null;
        $t->transId = isset($params['transId']) ? $params['transId'] : null;
        $t->errMsg = isset($params['errMsg']) ? $params['errMsg'] : null;
        $t->clientIp = isset($params['clientIp']) ? $params['clientIp'] : null;
        $t->maskedPan = isset($params['maskedPan']) ? $params['maskedPan'] : null;
        $t->cardBrand = isset($params['cardBrand']) ? $params['cardBrand'] : null;
        $t->expYear =  isset($params['expYear']) ? $params['expYear'] : null;
        $t->expMonth =  isset($params['expMonth']) ? $params['expMonth'] : null;
        $t->extraTrxDate = isset($params['extraTrxDate']) ? $params['extraTrxDate'] : null;
        $t->mdStatus = isset($params['mdStatus']) ? $params['mdStatus'] : null;
        $t->txstatus = isset($params['txstatus']) ? $params['txstatus'] : null;
        $t->iReqCode = isset($params['iReqCode']) ? $params['iReqCode'] : null;
        $t->iReqDetail = isset($params['iReqDetail']) ? $params['iReqDetail'] : null;
        $t->vendorCode = isset($params['vendorCode']) ? $params['vendorCode'] : null;
        $t->paResSyntaxOK = isset($params['paResSyntaxOK']) ? $params['paResSyntaxOK'] : null;
        $t->paResVerified = isset($params['paResVerified']) ? $params['paResVerified'] : null;
        $t->eci = isset($params['eci']) ? $params['eci'] : null;
        $t->cavv = isset($params['cavv']) ? $params['cavv'] : null;
        $t->cavvAlgorithm = isset($params['cavvAlgorithm']) ? $params['cavvAlgorithm'] : null;
        $t->md = isset($params['md']) ? $params['md'] : null;
        $t->version = isset($params['version']) ? $params['version'] : null;
        $t->sid = isset($params['sid']) ? $params['sid'] : null;
        $t->mdErrorMsg = isset($params['mdErrorMsg']) ? $params['mdErrorMsg'] : null;
        $t->status = isset($params['status']) ? $params['status'] : null;
        $t->timeAuthorized = isset($params['timeAuthorized']) ? $params['timeAuthorized'] : null;
        $t->timeCaptoredOrVoided = isset($params['timeCaptoredOrVoided']) ? $params['timeCaptoredOrVoided'] : null;

        return $t;
    }

    /**
     * Read transaction details from POST
     *
     * @param  array $params - parameters returned from PGW (if not set, $_POST is used)
     * @return Transaction
     * @throws NestPayException
     */
    public static function readTransaction($params = null) {
        if (!$params)
            $params = $_POST;

        Log::getInstance()->logMsg(
            'Reading transaction result: ' . Log::getInstance()->serializeForLog($params)
        );

        // basic sanity check
        if (isset($params['ReturnOid']) && $params['ReturnOid'] != $params['oid'])
            throw new NestPayException('Invalid parameters - no return oid or different oids');
        if (isset($params['merchantID']) && $params['merchantID'] != Settings::getInstance()->getMerchantId())
            throw new NestPayException('Invalid parameters - invalid merchant id');

        // check returned hash
        $hashKeys = explode('|', $params['HASHPARAMS']);
        $hashElements = array();
        foreach ($hashKeys as $key)
            $hashElements[] = static::escapeForHash($params[$key]);
        $hashElements[] = static::escapeForHash(Settings::getInstance()->getStoreKey());
        $hashval = implode('|', $hashElements);
        if (static::calculateHash($hashval) != $params['HASH'])
            throw new NestPayException('returned hash is not valid');
        $order = Order::getOrderByMerchantOrderId($params['oid']);
        if (!$order)
            throw new NestPayException('no order found for oid: ' . $params['oid']);

        $t = static::getForOrderNrAndTransactionId($params['oid'], $params['xid']);
        if ($t) {
            $t->alreadyProcessed = true;
        } else {
            $t = new Transaction();
            $t->orderId = $order->getId();
            $t->oid = $params['oid'];
            $t->authCode = $params['AuthCode'];
            $t->xid = $params['xid'];
            $t->response = $params['Response'];
            $t->procReturnCode = $params['ProcReturnCode'];
            $t->transId = $params['TransId'];
            $t->errMsg = $params['ErrMsg'];
            $t->clientIp = $params['clientIp'];
            $t->maskedPan = static::applyStrongerMask($params['MaskedPan']);
            $t->cardBrand = $params['EXTRA_CARDBRAND'];
            $t->expYear = $params['Ecom_Payment_Card_ExpDate_Year'];
            $t->expMonth = $params['Ecom_Payment_Card_ExpDate_Month'];
            $t->extraTrxDate = static::convertTxDate($params['EXTRA_TRXDATE']);
            $t->mdStatus = $params['mdStatus'];
            $t->txstatus = $params['txstatus'];
            $t->iReqCode = $params['iReqCode'];
            $t->iReqDetail = $params['iReqDetail'];
            $t->vendorCode = $params['vendorCode'];
            $t->paResSyntaxOK = $params['PAResSyntaxOK'];
            $t->paResVerified = $params['PAResVerified'];
            $t->eci = $params['eci'];
            $t->cavv = $params['cavv'];
            $t->cavvAlgorithm = $params['cavvAlgorithm'];
            $t->md = $params['md'];
            $t->version = $params['version'];
            $t->sid = $params['SID'];
            $t->mdErrorMsg = $params['mdErrorMsg'];
            $t->timeAuthorized = date('Y-m-d H:i:s');
            $t->status = $params['ProcReturnCode'] == '00'
                ? (Settings::getInstance()->isDmsMode() ? STATUS_AUTHORIZED : STATUS_CAPTURED)
                : STATUS_FAILED;
            $t->save();
        }

        return $t;
    }

    /**
     * @param int $status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @param string $timeCaptoredOrVoided
     */
    public function setTimeCaptoredOrVoided($timeCaptoredOrVoided) {
        $this->timeCaptoredOrVoided = $timeCaptoredOrVoided;
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
     * @return string
     */
    public function getOid() {
        return $this->oid;
    }

    /**
     * @return string
     */
    public function getAuthCode() {
        return $this->authCode;
    }

    /**
     * @return string
     */
    public function getXid() {
        return $this->xid;
    }

    /**
     * @return string
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getProcReturnCode() {
        return $this->procReturnCode;
    }

    /**
     * @return string
     */
    public function getTransId() {
        return $this->transId;
    }

    /**
     * @return string
     */
    public function getErrMsg() {
        return $this->errMsg;
    }

    /**
     * @return string
     */
    public function getClientIp() {
        return $this->clientIp;
    }

    /**
     * @return string
     */
    public function getMaskedPan() {
        return $this->maskedPan;
    }

    /**
     * @return string
     */
    public function getCardBrand() {
        return $this->cardBrand;
    }

    /**
     * @return string
     */
    public function getExpYear() {
        return $this->expYear;
    }

    /**
     * @return string
     */
    public function getExpMonth() {
        return $this->expMonth;
    }

    /**
     * @return string
     */
    public function getExtraTrxDate() {
        return $this->extraTrxDate;
    }

    /**
     * @return string
     */
    public function getMdStatus() {
        return $this->mdStatus;
    }

    /**
     * @return string
     */
    public function getTxstatus() {
        return $this->txstatus;
    }

    /**
     * @return string
     */
    public function getIReqCode() {
        return $this->iReqCode;
    }

    /**
     * @return string
     */
    public function getIReqDetail() {
        return $this->iReqDetail;
    }

    /**
     * @return string
     */
    public function getVendorCode() {
        return $this->vendorCode;
    }

    /**
     * @return string
     */
    public function getPaResSyntaxOK() {
        return $this->paResSyntaxOK;
    }

    /**
     * @return string
     */
    public function getPaResVerified() {
        return $this->paResVerified;
    }

    /**
     * @return string
     */
    public function getEci() {
        return $this->eci;
    }

    /**
     * @return string
     */
    public function getCavv() {
        return $this->cavv;
    }

    /**
     * @return string
     */
    public function getCavvAlgorithm() {
        return $this->cavvAlgorithm;
    }

    /**
     * @return string
     */
    public function getMd() {
        return $this->md;
    }

    /**
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getSid() {
        return $this->sid;
    }

    /**
     * @return string
     */
    public function getMdErrorMsg() {
        return $this->mdErrorMsg;
    }

    /**
     * @return int
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getTimeAuthorized() {
        return $this->timeAuthorized;
    }

    /**
     * @return string
     */
    public function getTimeCaptoredOrVoided() {
        return $this->timeCaptoredOrVoided;
    }

    /**
     * @return boolean
     */
    public function isAlreadyProcessed() {
        return $this->alreadyProcessed;
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
     * Get all transactions for sql
     *
     * @param  string $sql
     * @return Transaction[]
     * @throws NestPayException
     */
    private static function getForSql($sql) {
        /** @var Transaction[] */
        $transactions = array();
        $db = Settings::getInstance()->getDbInterface();
        foreach ($db->getRows($sql) as $row)
            $transactions[] = static::getFromArray($row);
        return $transactions;
    }

    /**
     * Calculate hash value
     *
     * @param  string $hashval
     * @return string
     */
    public static function calculateHash($hashval) {
        $calculatedHashValue = hash('sha512', $hashval);
        return base64_encode (pack('H*',$calculatedHashValue));
    }

    /**
     * Escape string for hash calculation
     *
     * @param  string $str
     * @return string
     */
    public static function escapeForHash($str) {
        return str_replace("|", "\\|", str_replace("\\", "\\\\", $str));
    }

    /**
     * Apply stronger mask to pan (X********XXXX instead of XXXXXX***XXXX)
     *
     * @param  string $pan
     * @return string
     */
    private static function applyStrongerMask($pan) {
        if (strlen($pan) > 6)
            $pan = substr($pan, 0, 1).'*****'.substr($pan, 6);
        return $pan;
    }

    /**
     * Convert Ymd H:i:s to Y-m-d H:i:s
     *
     * @param  string $date
     * @return string
     */
    private static function convertTxDate($date) {
        if (strlen($date) > 8)
            $date = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6);
        return $date;
    }

    /**
     * Init table def
     */
    private static function initTableDef() {
        if (static::$tableDef == null) {
            static::$tableDef = new DbTable(Settings::getInstance()->getTransactionsTableName());
            static::$tableDef
                ->addField(
                    new DbField('id', \NestPay\Model\Db\TYPE_INTEGER, 11, null, false, true, false)
                )
                ->addField(
                    new DbField('orderId', \NestPay\Model\Db\TYPE_INTEGER, 11, null, false, false, true)
                )
                ->addField(
                    new DbField('oid', \NestPay\Model\Db\TYPE_VARCHAR, 64, null, false, false, true)
                )
                ->addField(
                    new DbField('authCode', \NestPay\Model\Db\TYPE_VARCHAR, 6)
                )
                ->addField(
                    new DbField('xid', \NestPay\Model\Db\TYPE_VARCHAR, 32)
                )
                ->addField(
                    new DbField('response', \NestPay\Model\Db\TYPE_VARCHAR, 16)
                )
                ->addField(
                    new DbField('procReturnCode', \NestPay\Model\Db\TYPE_VARCHAR, 2)
                )
                ->addField(
                    new DbField('transId', \NestPay\Model\Db\TYPE_VARCHAR, 64, null, true, false, true)
                )
                ->addField(
                    new DbField('errMsg', \NestPay\Model\Db\TYPE_VARCHAR, 255)
                )
                ->addField(
                    new DbField('clientIp', \NestPay\Model\Db\TYPE_VARCHAR, 16)
                )
                ->addField(
                    new DbField('maskedPan', \NestPay\Model\Db\TYPE_VARCHAR, 16)
                )
                ->addField(
                    new DbField('cardBrand', \NestPay\Model\Db\TYPE_VARCHAR, 16)
                )
                ->addField(
                    new DbField('expYear', \NestPay\Model\Db\TYPE_VARCHAR, 2)
                )
                ->addField(
                    new DbField('expMonth', \NestPay\Model\Db\TYPE_VARCHAR, 2)
                )
                ->addField(
                    new DbField('extraTrxDate', \NestPay\Model\Db\TYPE_TIMESTAMP, null)
                )
                ->addField(
                    new DbField('mdStatus', \NestPay\Model\Db\TYPE_VARCHAR, 1)
                )
                ->addField(
                    new DbField('txstatus', \NestPay\Model\Db\TYPE_VARCHAR, 1)
                )
                ->addField(
                    new DbField('iReqCode', \NestPay\Model\Db\TYPE_VARCHAR, 2)
                )
                ->addField(
                    new DbField('iReqDetail', \NestPay\Model\Db\TYPE_VARCHAR, 64)
                )
                ->addField(
                    new DbField('vendorCode', \NestPay\Model\Db\TYPE_VARCHAR, 255)
                )
                ->addField(
                    new DbField('paResSyntaxOK', \NestPay\Model\Db\TYPE_VARCHAR, 1)
                )
                ->addField(
                    new DbField('paResVerified', \NestPay\Model\Db\TYPE_VARCHAR, 1)
                )
                ->addField(
                    new DbField('eci', \NestPay\Model\Db\TYPE_VARCHAR, 2)
                )
                ->addField(
                    new DbField('cavv', \NestPay\Model\Db\TYPE_VARCHAR, 32)
                )
                ->addField(
                    new DbField('cavvAlgorithm', \NestPay\Model\Db\TYPE_VARCHAR, 1)
                )
                ->addField(
                    new DbField('md', \NestPay\Model\Db\TYPE_VARCHAR, 255)
                )
                ->addField(
                    new DbField('version', \NestPay\Model\Db\TYPE_VARCHAR, 3)
                )
                ->addField(
                    new DbField('sid', \NestPay\Model\Db\TYPE_VARCHAR, 255)
                )
                ->addField(
                    new DbField('mdErrorMsg', \NestPay\Model\Db\TYPE_VARCHAR, 512)
                )
                ->addField(
                    new DbField('status', \NestPay\Model\Db\TYPE_INTEGER, 1)
                )
                ->addField(
                    new DbField('timeAuthorized', \NestPay\Model\Db\TYPE_TIMESTAMP, null)
                )
                ->addField(
                    new DbField('timeCaptoredOrVoided', \NestPay\Model\Db\TYPE_TIMESTAMP, null)
                )
            ;
        }
    }

    /** @var DbTable */
    private static $tableDef = null;

    /** @var int - internal transaciton id */
    private $id = null;
    /** @var int - internal order id */
    private $orderId = null;
    /** @var string - merchant order ID */
    private $oid = null;
    /** @var string - Transaction Verification/Approval/Authorization code, 6 chars */
    private $authCode = null;
    /** @var string - Internet transaction identifier, 28 chars, base64 encoded */
    private $xid = null;
    /** @var string - Payment status; Possible values: "Approved", "Error", "Declined" */
    private $response = null;
    /** @var string - Transaction status code - Alphanumeric, 2 chars,
     * “00” for authorized transactions,
     * “99” for gateway errors,
     * others for ISO-8583 error codes
     */
    private $procReturnCode = null;
    /** @var string - Nestpay Transaction Id, Maximum 64 characters */
    private $transId = null;
    /** @var string - Error message, Maximum 255 characters */
    private $errMsg = null;
    /** @var string - IP address of the customer, Maximum 15 characters formatted as "###.###.###.###"
     * nb: in doc it's ClientIp, in actual result clientIp */
    private $clientIp = null;
    /** @var string - Masked credit card number, 12 characters, XXXXXX***XXX
     * nb: actually it's 13 chars, 4 last digits */
    private $maskedPan = null;
    /** @var string $cardBrand
     * MASTERCARD | MAESTRO | VISA | AMEX */
    private $cardBrand = null;
    /** @var string - Ecom_Payment_Card_ExpDate_Year */
    private $expYear = null;
    /** @var string - Ecom_Payment_Card_ExpDate_Month */
    private $expMonth = null;
    /** @var string Transaction Date - 17 characters, formatted as "yyyyMMdd HH:mm:ss"
     * (converted to standard timestamp) */
    private $extraTrxDate = null;
    /** @var string - Status code for the 3D transaction
     * 1=authenticated transaction
     * 2, 3, 4 = Card not participating or attempt
     * 5,6,7,8 = Authentication not available or system error
     * 0 = Authentication failed
     */
    private $mdStatus = null;
    /** @var string - 3D status for archival - Possible values "A", "N", "Y" */
    private $txstatus = null;
    /** @var string - Code provided by ACS indicating data that is formatted correctly,
     * but which invalidates the request. This element is included when business processing
     * cannot be performed for some reason.
     * 2 digits, numeric */
    private $iReqCode = null;
    /** @var string - May identify the specific data elements that caused the Invalid Request Code
     * (so never supplied if Invalid Request Code is omitted). */
    private $iReqDetail = null;
    /** @var string - Error message describing iReqDetail error. */
    private $vendorCode = null;
    /** @var string - If PARes validation is syntactically correct, the value is true.
     * Otherwise value is false.
     * "Y" or "N" */
    private $paResSyntaxOK = null;
    /** @var string - If signature validation of the return message is successful, the value is true.
     * If PARes message is not received or signature validation fails, the value is false.
     * "Y" or "N" */
    private $paResVerified = null;
    /** @var string - Electronic Commerce Indicator; 2 digits, empty for non-3D transactions */
    private $eci = null;
    /** @var string - Cardholder Authentication Verification Value, determined by ACS.
     * 28 characters, contains a 20 byte value that has been Base64 encoded, giving a 28 byte result.*/
    private $cavv = null;
    /** @var string - CAVV algorithm; Possible values "0", "1", "2", "3" */
    private $cavvAlgorithm = null;
    /** @var string - MPI data replacing card number; Alpha-numeric */
    private $md = null;
    /** @var string - MPI version information; 3 characters l(ike "2.0") */
    private $version = null;
    /** @var string */
    private $sid = null;
    /** @var string - Error Message from MPI (if any); Maximum 512 characters */
    private $mdErrorMsg = null;
    /** @var int - STATUS_FAILED | STATUS_AUTHORIZED | STATUS_CAPTURED | STATUS_VOIDED  */
    private $status = null;
    /** @var string - authorized timestamp */
    private $timeAuthorized = null;
    /** @var string - captured timestamp */
    private $timeCaptoredOrVoided = null;
    /** @var bool - when user returns with same xid (e.g. reload on browser) */
    private $alreadyProcessed = false;

}