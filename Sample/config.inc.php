<?php

use NestPay\NestPayInterface;

define('URL_NEST_PAY_SAMPLE', 'https://payment-test.airserbia.com/NestPay/Sample');
define('PATH_NEST_PAY', realpath(__DIR__ . '/src'));
require_once PATH_NEST_PAY . '/NestPayInterface.php';

const DB_HOST = 'localhost';
const DB_USER = 'payment_test';
const DB_PASS = 'animat0r';
const DB_NAME = 'payment_test';

mysql_connect(DB_HOST, DB_USER, DB_PASS);
mysql_select_db(DB_NAME);

NestPayInterface::getInstance()->getSettings()
    ->setMerchantId('xxxxxxx')
    ->setStoreKey('xxxxxxx')
    ->setTestMode(true)
    ->setApiUser('xxxxxxx')
    ->setApiPass('xxxxxxx')
    ->setDmsMode(true)
    ->setLogDir(__DIR__)
    ->setMySQLDbInterface();

