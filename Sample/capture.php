<?php

use NestPay\NestPayInterface;

require_once __DIR__ . '/config.inc.php';

$merchantOrderId = $_GET['oid'];

echo "<div>is paid: " .
    (($isPaid = NestPayInterface::getInstance()->isPaid($merchantOrderId)) ? 'Y' : 'N') .
    "</div>";

echo "<div>is captured: " .
    (($isCaptured = NestPayInterface::getInstance()->isCaptured($merchantOrderId)) ? 'Y' : 'N') .
    "</div>";

echo "<div>is voided: " .
    (($isVoided = NestPayInterface::getInstance()->isVoided($merchantOrderId)) ? 'Y' : 'N') .
    "</div>";

if ($isPaid && !$isCaptured && !$isVoided) {
    $status = NestPayInterface::getInstance()->capture($merchantOrderId);
    echo "capture done, status: " . ($status ? "OK" : "FAILED");

    echo "<div>is captured: " .
        (($isCaptured = NestPayInterface::getInstance()->isCaptured($merchantOrderId)) ? 'Y' : 'N') .
        "</div>";
} else {
    echo "cannot capture.";
}