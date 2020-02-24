<?php

use NestPay\NestPayInterface;

require_once __DIR__ . '/config.inc.php';

NestPayInterface::getInstance()->setup();

echo "DONE";
