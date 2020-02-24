<?php

namespace NestPay;

use NestPay\Model\Log;
use NestPay\Model\NestPayException;
use NestPay\Model\Order;
use NestPay\Model\OrderAddress;
use NestPay\Model\OrderItem;
use NestPay\Model\Settings;
use NestPay\Model\Transaction;

const CURRENCY_RSD = 941;

/**
 * Class NestPayInterface
 *
 * @package NestPay
 */
class NestPayInterface {

    /**
     * Get order
     *
     * This will get new or previously created order
     * with given merchant order id
     *
     * @param string $merchantOrderId
     * @param float $amount
     * @param int $currency
     * @param string $lang
     * @param string $okUrl
     * @param string $failUrl
     * @return Order|null
     */
    public function getOrder($merchantOrderId, $amount, $currency, $lang, $okUrl, $failUrl) {
        try {
            return Order::getNewOrder($merchantOrderId, $amount, $currency, $lang, $okUrl, $failUrl);
        } catch (NestPayException $e) {
            Log::getInstance()->logError(
                'Failed getting order for ' . $merchantOrderId . ' with exception: ' . $e->getMessage()
            );
            return null;
        }
    }

    /**
     * Get order by merchant order id
     *
     * @param  string $merchantOrderId
     * @return Order|null
     */
    public function getOrderByMerchantOrderId($merchantOrderId) {
        return Order::getOrderByMerchantOrderId($merchantOrderId);
    }

    /**
     * Go to payment
     *
     * Displays or returns form
     * that redirects to NestPay
     *
     * @param string $merchantOrderId
     * @param bool|true $completePage - if complete page should be displayed
     * @param string|null $buttonText - text for fail-safe button (if JS redirection fails)
     * @return string
     */
    public function goPay($merchantOrderId, $completePage = true, $buttonText = null) {
        try {
            $order = Order::getOrderByMerchantOrderId($merchantOrderId);
            if (!$order)
                throw new NestPayException("order doesn't exist: " . $merchantOrderId);
            else if ($this->isPaid($merchantOrderId))
                throw new NestPayException("order is already paid: " . $merchantOrderId);
            else if (!$this->canRetry($merchantOrderId))
                throw new NestPayException("number of tries exceeded: " . $merchantOrderId);

            $form = $order->getRedirectionForm($completePage, $buttonText);

            if ($completePage) {
                ob_clean();
                header("Content-type: text/html; charset=utf-8");
                echo $form;
                exit(0);
            } else
                return $form;
        } catch (NestPayException $e) {
            Log::getInstance()->logError(
                'failed redirecting to payment for '. $merchantOrderId .': ' . $e->getMessage()
            );
            return null;
        }
    }

    /**
     * Get transaction from posted results
     *
     * @param  array|null $params - array of posted params; if omitted, $_POST will be used
     * @return Transaction
     */
    public function getResults($params = null) {
        try {
            return Transaction::readTransaction($params);
        } catch (NestPayException $e) {
            Log::getInstance()->logError('failed getting transaction details: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get last transaction for order id
     *
     * @param  string $merchantOrderId
     * @return Transaction|null
     */
    public function getLastTransaction($merchantOrderId) {
        return Transaction::getLastForOrderNr($merchantOrderId);
    }

    /**
     * Check if order is paid
     *
     * @param  string $orderId - merchant order ID
     * @return bool
     */
    public function isPaid($orderId) {
        return Transaction::getSuccessfulForOrderNr($orderId) ? true : false;
    }

    /**
     * Check if order is captured
     *
     * @param  string $orderId - merchant order ID
     * @return bool
     */
    public function isCaptured($orderId) {
        $t = Transaction::getSuccessfulForOrderNr($orderId);
        return $t && $t->getStatus() == \NestPay\Model\STATUS_CAPTURED;
    }

    /**
     * Check if order is voided
     *
     * @param  string $orderId - merchant order ID
     * @return bool
     */
    public function isVoided($orderId) {
        $t = Transaction::getSuccessfulForOrderNr($orderId);
        return $t && $t->getStatus() == \NestPay\Model\STATUS_VOIDED;
    }

    /**
     * Check if payment can be retried
     *
     * @param  string $orderId
     * @return bool
     */
    public function canRetry($orderId) {
        return count(Transaction::getAllForOrderNr($orderId)) < Settings::getInstance()->getMaxTries();
    }

    /**
     * Capture order
     *
     * @param  string $merchantOrderId
     * @return bool
     */
    public function capture($merchantOrderId) {
        return $this->captureOrVoid($merchantOrderId, $void=false);
    }

    /**
     * Void order
     *
     * @param  string $merchantOrderId
     * @return bool
     */
    public function void($merchantOrderId) {
        return $this->captureOrVoid($merchantOrderId, $void=true);
    }

    /**
     * Setup database tables
     *
     * @throws NestPayException
     */
    public function setup() {
        $db = Settings::getInstance()->getDbInterface();
        foreach (
            array(
                Transaction::getTableDef(),
                OrderItem::getTableDef(),
                OrderAddress::getTableDef(),
                Order::getTableDef()
            )
            as $tableDef
        )
            $db->execute($db->getCreateSql($tableDef));
    }

    /**
     * Get settings
     *
     * @return Settings
     */
    public function getSettings() {
        return Settings::getInstance();
    }


    /**
     * Capture or void order
     *
     * @param string $merchantOrderId
     * @param bool|false $void
     * @return bool
     */
    private function captureOrVoid($merchantOrderId, $void = false) {
        $order = Order::getOrderByMerchantOrderId($merchantOrderId);
        if ($order) {
            try {
                $status = $order->captureOrVoid($void);
                Log::getInstance()->logMsg(
                    ($void ? 'void' : 'capture').
                    ' '. ($status ? 'successful' : 'failed') .
                    ' for ' . $merchantOrderId
                );
            } catch (NestPayException $e) {
                $status = false;
                Log::getInstance()->logError(
                    ($void ? 'void' : 'capture').
                    ' for ' . $merchantOrderId . 'failed with error: ' .
                    $e->getMessage()
                );
            }
        } else {
            $status = false;
            Log::getInstance()->logMsg(
                ($void ? 'void' : 'capture').
                ' failed for ' . $merchantOrderId . '(order not found)'
            );
        }
        return $status;
    }

    /**
     * Get singleton instance
     *
     * @return NestPayInterface
     */
    public static function getInstance() {
        if (static::$instance == null)
            static::$instance = new NestPayInterface();
        return static::$instance;
    }

    /** @var NestPayInterface */
    private static $instance = null;

    /**
     * NestPayInterface constructor.
     */
    private function __construct() { }
}