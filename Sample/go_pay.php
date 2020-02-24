<?php

use NestPay\NestPayInterface;

require_once __DIR__ . '/config.inc.php';

$merchantOrderId = 'TEST_ORDER_'.time();
$order = NestPayInterface::getInstance()->getOrder(
    $merchantOrderId,
    $_POST['amount'],
    \NestPay\CURRENCY_RSD,
    'en',
    URL_NEST_PAY_SAMPLE . '/return.php',
    URL_NEST_PAY_SAMPLE . '/return.php'
);
if ($order)
    NestPayInterface::getInstance()->goPay($merchantOrderId);
else
    echo "order failed";
