<?php

use NestPay\NestPayInterface;

require_once __DIR__ . '/config.inc.php';

$merchantOrderId = $_GET['oid'];

NestPayInterface::getInstance()->goPay($merchantOrderId);