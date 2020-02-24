<?php

namespace NestPay\Model;

use NestPay\Model\Db\DbField;
use NestPay\Model\Db\DbTable;
use Purli\Purli;

/**
 * Class Order
 *
 * @package NestPay\Model
 */
class Order {

    /**
     * Get order by (merchant) order id
     *
     * @param  string $orderId - merchant order id
     * @return Order|null
     * @throws NestPayException
     */
    public static function getOrderByMerchantOrderId($orderId) {
        $sql = sprintf(
            'SELECT'.' * FROM '.static::getTableDef()->getName().' WHERE oid="%s"',
            Settings::getInstance()->getDbInterface()->escape($orderId)
        );
        $orders = static::getForSql($sql);
        return count($orders) ? array_shift($orders) : null;
    }

    /**
     * Get order by (merchant) order id
     *
     * @param  int $orderId - local order id
     * @return Order|null
     * @throws NestPayException
     */
    public static function getOrderByLocalOrderId($orderId) {
        $sql = sprintf(
            'SELECT'.' * FROM '.static::getTableDef()->getName().' WHERE id=%d',
            $orderId
        );
        $orders = static::getForSql($sql);
        return count($orders) ? array_shift($orders) : null;
    }

    /**
     * Saves order to db
     *
     * @throws NestPayException
     */
    public function save() {
        $db = Settings::getInstance()->getDbInterface();

        // first save addresses
        if ($this->billingAddress)
            $this->billingAddress->save();
        if ($this->shippingAddress)
            $this->shippingAddress->save();

        if (empty($this->id)) {
            $sql = $db->getInsertSql(static::getTableDef(), $this->mapToArray());
            $db->execute($sql);
            $this->id = $db->getInsertId();
        } else {
            $sql = $db->getUpdateSql(static::getTableDef(), $this->mapToArray());
            $db->execute($sql);
        }

        // save order items
        foreach ($this->items as $item) {
            $item->setOrderId($this->id);
            $item->save();
        }
    }

    /**
     * Get redirection form
     *
     * @param  boolean $completePage - get complete page
     * @param  string $buttonText - if backup button should be displayed (JS fails)
     * @return string
     */
    public function getRedirectionForm($completePage=true, $buttonText = null) {
        $form = '';
        if ($completePage)
            $form .= '<html>' . "\n" .
                '<head>' . "\n" .
                '<title>Redirecting to NestPay...</title>' . "\n" .
                '<meta http-equiv="Content-Language" content="en">' . "\n" .
                '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . "\n" .
                '</head>' . "\n" .
                '<body>' . "\n";

        $form .= sprintf(
            '<form name="nestpayForm" action="%s" method="post">',
            Settings::getInstance()->getHosted3dUrl()
        );

        foreach ($this->getOrderParams() as $k => $v)
            $form .= sprintf(
                '<input type="hidden" name="%s" value="%s" />' . "\n",
                $k,
                preg_replace('/\"/', "'", $v)
            );

        if ($buttonText)
            $form .= sprintf(
                '<input type="submit" name="send" value="%s" />' . "\n",
                $buttonText
            );

        $form .= '</form>' . "\n";
        $form .= '<script>document.forms["nestpayForm"].submit();</script>' . "\n";
        if ($completePage)
            $form .= '</body></html>';
        Log::getInstance()->logMsg(
            'redirection to ' . Settings::getInstance()->getHosted3dUrl() . ' with parameters: ' .
            Log::getInstance()->serializeForLog($this->getOrderParams())
        );
        return $form;
    }

    /**
     * Capture or void
     *
     * @param  bool|false $void
     * @return bool
     * @throws NestPayException
     */
    public function captureOrVoid($void=false) {
        $transaction = Transaction::getSuccessfulForOrderNr($this->oid);
        if (!$transaction)
            throw new NestPayException('capture/void failed: no successfull transaction for ' . $this->oid);

        if ($transaction->getStatus() == STATUS_VOIDED && $void)
            return true; // already done
        else if ($transaction->getStatus() == STATUS_CAPTURED && !$void)
            return true; // already done
        else if ($transaction->getStatus() != STATUS_AUTHORIZED)
            throw new NestPayException('captyre/void failed: invalid transaction status for ' . $this->oid);

        Log::getInstance()->logMsg(
            'calling ' . ($void ? 'void' : 'capture') . ' for ' . $this->oid .
            ', address: ' . Settings::getInstance()->getApiUrl() . ', data: ' .
            preg_replace('/\n/', '', $this->getXmlRequest($void ? 'Void' : 'PostAuth'))
        );

        /*
        $data = http_build_query(
            array(
                'DATA' => $this->getXmlRequest($void ? 'Void' : 'PostAuth'),
            )
        );

        $context = stream_context_create(
            array(
                'http' => array(
                    'method'  => 'POST',
                    'header'  => "Content-type: application/x-www-form-urlencoded",
                    'content' => $data,
                )
            )
        );
        $result = file_get_contents(
            Settings::getInstance()->getApiUrl(),
            false,
            $context
        );
        */

        try {
            $purl = new Purli();

            $purl->setParams($this->getXmlRequest($void ? 'Void' : 'PostAuth'));
            $purl->setProxy(Settings::getInstance()->getProxyIp(), Settings::getInstance()->getProxyPort());
            $purl->post(Settings::getInstance()->getApiUrl());
            $result = $purl->response()->asText();
            $purl->close();

        } catch (\Exception $e) {
            throw new NestPayException('capture/post failed: failed posting for ' . $this->oid . ' Error: ' . $e->getMessage());
        }

        Log::getInstance()->logMsg(
            ($void ? 'void' : 'capture') . ' response: ' . preg_replace('/\n/', '', $result)
        );

        /*
            <?xml version="1.0" encoding="ISO-8859-9"?>
            <CC5Response>
              <OrderId>TEST_ORDER_1468510974</OrderId>
              <GroupId>TEST_ORDER_1468510974</GroupId>
              <Response>Approved</Response>
              <AuthCode>497890</AuthCode>
              <HostRefNum>619617000075</HostRefNum>
              <ProcReturnCode>00</ProcReturnCode>
              <TransId>16196RrMH11703</TransId>
              <ErrMsg></ErrMsg>
              <Extra>
                <SETTLEID>59</SETTLEID>
                <TRXDATE>20160714 17:43:12</TRXDATE>
                <ERRORCODE></ERRORCODE>
                <NUMCODE>00</NUMCODE>
                <CARDBRAND>VISA</CARDBRAND>
              </Extra>
            </CC5Response>        
         */
        $status = preg_match('/<ProcReturnCode>00<\/ProcReturnCode>/ms', $result);

        echo "status: ".($status ? "OK" : "FAIL")."\n";

        if ($status) {
            $transaction->setStatus($void ? STATUS_VOIDED : STATUS_CAPTURED);
            $transaction->setTimeCaptoredOrVoided(date('Y-m-d H:i:s'));
            $transaction->save();
        }

        return $status;
    }

    /**
     * Get new order
     *
     * @param string $merchantOrderId
     * @param float $amount
     * @param int $currency
     * @param string $lang
     * @param string $okUrl
     * @param string $failUrl
     * @return Order
     * @throws NestPayException
     */
    public static function getNewOrder($merchantOrderId, $amount, $currency, $lang, $okUrl, $failUrl) {
        $o = static::getOrderByMerchantOrderId($merchantOrderId);
        if ($o) {
            if ($o->amount != $amount || $o->currency != $currency)
                throw new NestPayException('order exists with different amount');
            // reset items if exists
            $o->items = array();
        } else
            $o = new Order();

        $o->oid = $merchantOrderId;
        $o->amount = $amount;
        $o->currency = $currency;
        $o->lang = $lang;
        $o->okUrl = $okUrl;
        $o->failUrl = $failUrl;
        $o->save();
        return $o;
    }

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @param string $comments
     */
    public function setComments($comments) {
        $this->comments = $comments;
    }

    /**
     * @param int $installment
     */
    public function setInstallment($installment) {
        $this->installment = $installment;
    }

    /**
     * @param int $gracePeriod
     */
    public function setGracePeriod($gracePeriod) {
        $this->gracePeriod = $gracePeriod;
    }

    /**
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * @param string $tel
     */
    public function setTel($tel) {
        $this->tel = $tel;
    }

    /**
     * @param string $shopUrl
     */
    public function setShopUrl($shopUrl) {
        $this->shopUrl = $shopUrl;
    }

    /**
     * Get internal order id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOid() {
        return $this->oid;
    }

    /**
     * @return float
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getLang() {
        return $this->lang;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getComments() {
        return $this->comments;
    }

    /**
     * @return int
     */
    public function getInstallment() {
        return $this->installment;
    }

    /**
     * @return int
     */
    public function getGracePeriod() {
        return $this->gracePeriod;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getTel() {
        return $this->tel;
    }

    /**
     * @return string
     */
    public function getShopUrl() {
        return $this->shopUrl;
    }

    /**
     * @return int
     */
    public function getRecurringPaymentNumber() {
        return $this->recurringPaymentNumber;
    }

    /**
     * @return string
     */
    public function getRecurringFrequencyUnit() {
        return $this->recurringFrequencyUnit;
    }

    /**
     * @return int
     */
    public function getRecurringFrequency() {
        return $this->recurringFrequency;
    }

    /**
     * @return OrderAddress
     */
    public function getBillingAddress() {
        return $this->billingAddress;
    }

    /**
     * @return OrderAddress
     */
    public function getShippingAddress() {
        return $this->shippingAddress;
    }

    /**
     * @return OrderItem[]
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * @return string
     */
    public function getOkUrl() {
        return $this->okUrl;
    }

    /**
     * @return string
     */
    public function getFailUrl() {
        return $this->failUrl;
    }

    /**
     * Get order parameters (for submitting to 3d hosted page)
     *
     * @return array
     */
    private function getOrderParams() {
        $params = array();

        // clientid - Merchant ID - Maximum 15 characters
        $params['clientid'] = Settings::getInstance()->getMerchantId();
        // storetype - Merchant payment model; Possible values: "pay_hosting", “3d_pay”, "3d", "3d_pay_hosting"
        $params['storetype'] = Settings::getInstance()->isDisabled3d() ? 'pay_hosting' : '3d_pay_hosting'; // pay_hosting
        // trantype - Transaction type; Set to "Auth" for authorization, “PreAuth” for preauthorization
        $params['trantype'] = Settings::getInstance()->isDmsMode() ? 'PreAuth' : 'Auth';

        $params['amount'] = $this->amount;
        $params['currency'] = $this->currency;
        $params['lang'] = $this->lang;
        $params['okUrl'] = $this->okUrl;
        $params['failUrl'] = $this->failUrl;

        $params['oid'] = $this->oid;
        if (!empty($this->description))
            $params['description'] = $this->description;
        if (!empty($this->comments))
            $params['comments'] = $this->comments;
        if (!empty($this->installment))
            $params['instalment'] = $this->installment;
        else
            $params['instalment'] = '';
        if (!empty($this->gracePeriod))
            $params['GRACEPERIOD'] = $this->gracePeriod;
        if (!empty($this->email))
            $params['email'] = $this->email;
        if (!empty($this->tel))
            $params['tel'] = $this->tel;
        if (!empty($this->shopUrl))
            $params['shopurl'] = $this->shopUrl;

        if (!empty($this->recurringPaymentNumber)) {
            $params['RecurringPaymentNumber'] = $this->recurringPaymentNumber;
            $params['RecurringFrequencyUnit'] = $this->recurringFrequencyUnit;
            $params['RecurringFrequency'] = $this->recurringFrequency;
        }

        // rnd - Random string, will be used for hash comparison; Fixed length, 20 characters
        $params['rnd'] = substr(base64_encode(md5($this->oid . time())), 0, 20);
        // hash - Hash value for client authentication
        $plaintext = sprintf(
            '%s|%s|%s|%s|%s|%s|%s|%s||||%s|%s',
            Transaction::escapeForHash($params['clientid']),
            Transaction::escapeForHash($params['oid']),
            Transaction::escapeForHash($params['amount']),
            Transaction::escapeForHash($params['okUrl']),
            Transaction::escapeForHash($params['failUrl']),
            Transaction::escapeForHash($params['trantype']),
            Transaction::escapeForHash($params['instalment']),
            Transaction::escapeForHash($params['rnd']),
            Transaction::escapeForHash($params['currency']),
            Transaction::escapeForHash(Settings::getInstance()->getStoreKey())
        );
        $params['hash'] = Transaction::calculateHash($plaintext);
        // hashAlgorithm - Hash version, “Ver2”
        $params['hashAlgorithm'] = 'Ver2';
        // encoding - Encoding of the posted data. Default value is “utf-8” if not sent, Maximum 32 characters
        $params['encoding'] = 'utf-8';

        if ($this->billingAddress)
            $params = array_merge($params, $this->billingAddress->getParamsForOrder());
        if ($this->shippingAddress)
            $params = array_merge($params, $this->shippingAddress->getParamsForOrder());
        foreach ($this->items as $item)
            $params = array_merge($params, $item->getParamsForOrder());

        // printBillTo - Print BillTo address fields on payment page - “true” or “false”. If not sent, billTo address details will not be printed
        $params['printBillTo'] = $this->billingAddress ? 'true' : 'false';
        // printShipTo - Print ShipTo address fields on payment page - “true” or “false”. If not sent, shipTo address details will not be printed
        $params['printShipTo'] = $this->shippingAddress ? 'true' : 'false';
        // refreshtime - Redirection counter value to okUrl or failUrl in seconds. Number
        $params['refreshtime'] = Settings::getInstance()->getRefreshTime();

        return $params;
    }

    /**
     * Add order item
     *
     * @param string $itemId
     * @param string $itemNumber
     * @param string $productCode
     * @param float $qty
     * @param string $description
     * @param float $unitPrice
     * @return $this
     */
    public function addItem($itemId, $itemNumber, $productCode, $qty, $description, $unitPrice) {
        $this->items[] = OrderItem::getNew(
            count($this->items) + 1, $itemId, $itemNumber, $productCode, $qty, $description, $unitPrice
        );
        return $this;
    }

    /**
     * Set billing address
     *
     * @param string $company
     * @param string $name
     * @param string $street1
     * @param string $street2
     * @param string $city
     * @param string $stateProv
     * @param string $postalCode
     * @param string $country
     * @return $this
     */
    public function setBillingAddress($company, $name, $street1, $street2, $city,
                                      $stateProv, $postalCode, $country) {
        $this->billingAddress = OrderAddress::makeBillingAddress(
            $company, $name, $street1, $street2, $city, $stateProv, $postalCode, $country
        );
        return $this;
    }

    /**
     * Set shipping address
     *
     * @param string $company
     * @param string $name
     * @param string $street1
     * @param string $street2
     * @param string $city
     * @param string $stateProv
     * @param string $postalCode
     * @param string $country
     * @return $this
     */
    public function setShippingAddress($company, $name, $street1, $street2, $city,
                                       $stateProv, $postalCode, $country) {
        $this->billingAddress = OrderAddress::makeShippingAddress(
            $company, $name, $street1, $street2, $city, $stateProv, $postalCode, $country
        );
        return $this;
    }

    /**
     * Set recurring payment details
     *
     * @param int $nr - number of recurring payments
     * @param string $freqUnit - frequency of recurring payments, D=Day, W=Week, M=Month, Y=Year
     * @param int $frequency
     * @return $this
     * @throws NestPayException
     */
    public function setRecurringPayment($nr, $freqUnit, $frequency) {
        if (intval($nr) <= 0
            || !in_array($freqUnit, array('D', 'W', 'M', 'Y'))
            || intval($frequency) <= 0
        )
            throw new NestPayException('Invalid recurring parameters');
        $this->recurringPaymentNumber = $nr;
        $this->recurringFrequencyUnit = $freqUnit;
        $this->recurringFrequency = $frequency;
        return $this;
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
     * Get all orders for sql
     *
     * @param  string $sql
     * @return Order[]
     * @throws NestPayException
     */
    private static function getForSql($sql) {
        /** @var Order[] */
        $items = array();
        $db = Settings::getInstance()->getDbInterface();
        foreach ($db->getRows($sql) as $row)
            $items[] = static::getFromArray($row);
        return $items;
    }

    /**
     * Get XML API request
     *
     * @param  string $type - PostAuth|Void
     * @return string
     * @throws NestPayException
     */
    private function getXmlRequest($type) {
        return sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            "<CC5Request>" . "\n" .
            "   <Name>%s</Name>" . "\n" .
            "   <Password>%s</Password> " . "\n" .
            "   <ClientId>%s</ClientId>" . "\n" .
            "   <Type>%s</Type>" . "\n" .
            "   <OrderId>%s</OrderId>" . "\n" .
            "</CC5Request>",
            Settings::getInstance()->getApiUser(),
            Settings::getInstance()->getApiPass(),
            Settings::getInstance()->getMerchantId(),
            $type,
            $this->oid
        );

    }

    /** @var int */
    private $id = null;
    /** @var string - Unique identifier of the order, Maximum 64 characters */
    private $oid = null;

    /** @var float - amount - amount transaction amount;
     * Use "." or "," as decimal separator, do not use grouping character */
    private $amount = null;
    /** @var string - currency - ISO code of transaction currency;
     * ISO 4217 numeric currency code, 3 digits */
    private $currency = null;
    /** @var string - lang - Language of the payment pages hosted by NestPay;
     * "tr" for Turkish, "en" for English */
    private $lang = null;
    /** @var string - okUrl - The return URL to which NestPay redirects the customer
     * if transaction is completed successfully; Example: http://www.test.com/ok.php */
    private $okUrl = null;
    /** @var string - failUrl -The return URL to which NestPay redirects the customer
     * if transaction is completed unsuccessfully. Example: http://www.test.com/fail.php */
    private $failUrl = null;
    /** @var string - description - Description sent to MPI; Maximum 255 characters */
    private $description = null;
    /** @var string - comments - Kept as “description” for the transaction, Maximum 255 characters */
    private $comments = null;
    /** @var int - instalment - Instalment count, Number */
    private $installment = null;
    /** @var int - GRACEPERIOD - Grace period; postpones the payment of given months; Number (months) */
    private $gracePeriod = null;
    /** @var string - email - Customer's email address; Maximum 64 characters */
    private $email = null;
    /** @var string - tel - Customer phone; Maximum 32 characters */
    private $tel = null;
    /** @var string - shopurl - The return URL which NestPay redirects customers
     * when the customer clicks the button “back to order” displayed in HPP.
     * It can be any URL. It is expected from the merchant to send the URL of its website. */
    private $shopUrl = null;


    /** @var int - RecurringPaymentNumber -
     * Total number of payments for recurring payment, Number */
    private $recurringPaymentNumber = null;
    /** @var string - RecurringFrequencyUnit - Frequency unit for recurring payment,
     * 1 char: D=Day,W=Week,M=Month, Y=Year */
    private $recurringFrequencyUnit = null;
    /** @var int - RecurringFrequency - Frequency of recurring payment, Number */
    private $recurringFrequency = null;

    /** @var OrderAddress */
    private $billingAddress = null;
    /** @var OrderAddress */
    private $shippingAddress = null;
    /** @var OrderItem[] */
    private $items = array();

    /**
     * Map object to array
     *
     * @return array
     */
    private function mapToArray() {
        return array(
            'id' => $this->id,
            'oid' => $this->oid,

            'amount' => $this->amount,
            'currency' => $this->currency,
            'lang' => $this->lang,
            'okUrl' => $this->okUrl,
            'failUrl' => $this->failUrl,
            'description' => $this->description,
            'comments' => $this->comments,
            'installment' => $this->installment,
            'gracePeriod' => $this->gracePeriod,
            'email' => $this->email,
            'tel' => $this->tel,
            'shopUrl' => $this->shopUrl,

            'recurringPaymentNumber' => $this->recurringPaymentNumber,
            'recurringFrequencyUnit' => $this->recurringFrequencyUnit,
            'recurringFrequency' => $this->recurringFrequency,
            'billingAddress' => $this->billingAddress ? $this->billingAddress->getId() : null,
            'shippingAddress' => $this->shippingAddress ? $this->shippingAddress->getId() : null,
        );
    }

    /**
     * Get object from array
     *
     * @param  array $params
     * @return Order
     */
    private static function getFromArray($params = array()) {
        $o = new Order();
        $o->id = isset($params['id']) ? $params['id'] : null;
        $o->oid = isset($params['oid']) ? $params['oid'] : null;

        $o->amount = isset($params['amount']) ? $params['amount'] : null;
        $o->currency = isset($params['currency']) ? $params['currency'] : null;
        $o->lang = isset($params['lang']) ? $params['lang'] : null;
        $o->okUrl = isset($params['okUrl']) ? $params['okUrl'] : null;
        $o->failUrl = isset($params['failUrl']) ? $params['failUrl'] : null;
        $o->description = isset($params['description']) ? $params['description'] : null;
        $o->comments = isset($params['comments']) ? $params['comments'] : null;
        $o->installment = isset($params['installment']) ? $params['installment'] : null;
        $o->gracePeriod = isset($params['gracePeriod']) ? $params['gracePeriod'] : null;
        $o->email = isset($params['email']) ? $params['email'] : null;
        $o->tel = isset($params['tel']) ? $params['tel'] : null;
        $o->shopUrl = isset($params['shopUrl']) ? $params['shopUrl'] : null;

        $o->recurringPaymentNumber = isset($params['recurringPaymentNumber']) ? $params['recurringPaymentNumber'] : null;
        $o->recurringFrequencyUnit = isset($params['recurringFrequencyUnit']) ? $params['recurringFrequencyUnit'] : null;
        $o->recurringFrequency = isset($params['recurringFrequency']) ? $params['recurringFrequency'] : null;

        if (!empty($params['billingAddress']))
            $o->billingAddress = OrderAddress::getById($params['billingAddress']);
        if (!empty($params['shippingAddress']))
            $o->shippingAddress = OrderAddress::getById($params['shippingAddress']);
        if ($o->id)
            $o->items = OrderItem::getItemsForOrder($o->id);

        return $o;
    }

    /**
     * Init table def
     */
    private static function initTableDef() {
        if (static::$tableDef == null) {
            static::$tableDef = new DbTable(Settings::getInstance()->getOrdersTableName());
            static::$tableDef
                ->addField(
                    new DbField('id', \NestPay\Model\Db\TYPE_INTEGER, 11, null, false, true, false)
                )
                ->addField(
                    new DbField('oid', \NestPay\Model\Db\TYPE_VARCHAR, 64, null, false, false, true)
                )
                ->addField(
                    new DbField('amount', \NestPay\Model\Db\TYPE_DECIMAL, '20,2')
                )
                ->addField(
                    new DbField('currency', \NestPay\Model\Db\TYPE_VARCHAR, 3)
                )
                ->addField(
                    new DbField('lang', \NestPay\Model\Db\TYPE_VARCHAR, 8)
                )
                ->addField(
                    new DbField('okUrl', \NestPay\Model\Db\TYPE_VARCHAR, 255)
                )
                ->addField(
                    new DbField('failUrl', \NestPay\Model\Db\TYPE_VARCHAR, 255)
                )
                ->addField(
                    new DbField('description', \NestPay\Model\Db\TYPE_VARCHAR, 255)
                )
                ->addField(
                    new DbField('comments', \NestPay\Model\Db\TYPE_VARCHAR, 255)
                )
                ->addField(
                    new DbField('installment', \NestPay\Model\Db\TYPE_INTEGER, 3)
                )
                ->addField(
                    new DbField('gracePeriod', \NestPay\Model\Db\TYPE_INTEGER, 3)
                )
                ->addField(
                    new DbField('email', \NestPay\Model\Db\TYPE_VARCHAR, 64)
                )
                ->addField(
                    new DbField('tel', \NestPay\Model\Db\TYPE_VARCHAR, 32)
                )
                ->addField(
                    new DbField('shopUrl', \NestPay\Model\Db\TYPE_VARCHAR, 255)
                )
                ->addField(
                    new DbField('recurringPaymentNumber', \NestPay\Model\Db\TYPE_INTEGER, 6)
                )
                ->addField(
                    new DbField('recurringFrequencyUnit', \NestPay\Model\Db\TYPE_VARCHAR, 1)
                )
                ->addField(
                    new DbField('recurringFrequency', \NestPay\Model\Db\TYPE_INTEGER, 6)
                )
                ->addField(
                    new DbField('billingAddress', \NestPay\Model\Db\TYPE_INTEGER, 11)
                )
                ->addField(
                    new DbField('shippingAddress', \NestPay\Model\Db\TYPE_INTEGER, 11)
                )
            ;
        }
    }

    /** @var DbTable */
    private static $tableDef = null;

}