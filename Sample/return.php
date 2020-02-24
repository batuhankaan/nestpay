<?php

use NestPay\NestPayInterface;

require_once __DIR__ . '/config.inc.php';

$t = NestPayInterface::getInstance()->getResults();

if ($t) {
    $merchantOrderId = $t->getOid();
    echo "<h2>Transaction</h2><pre>", print_r($t), "</pre>";


    echo "<div>is paid: " .
        (($isPaid = NestPayInterface::getInstance()->isPaid($merchantOrderId)) ? 'Y' : 'N') .
        "</div>";

    echo "<div>is captured: " .
        (($isCaptured = NestPayInterface::getInstance()->isCaptured($merchantOrderId)) ? 'Y' : 'N') .
        "</div>";

    echo "<div>is voided: " .
        (($isVoided = NestPayInterface::getInstance()->isVoided($merchantOrderId)) ? 'Y' : 'N') .
        "</div>";

    echo "<div>can retry: " .
        (($canRetry = NestPayInterface::getInstance()->canRetry($merchantOrderId)) ? 'Y' : 'N') .
        "</div>";

    if ($isPaid && !$isCaptured && !$isVoided) {
        echo "<div><a href=\"".URL_NEST_PAY_SAMPLE.'/capture.php?oid='.$merchantOrderId."\">capture</a></div>";
        echo "<div><a href=\"".URL_NEST_PAY_SAMPLE.'/void.php?oid='.$merchantOrderId."\">void</a></div>";
    } else if (!$isPaid && $canRetry) {
        echo "<div><a href=\"".URL_NEST_PAY_SAMPLE.'/retry.php?oid='.$merchantOrderId."\">retry</a></div>";
    }

} else
    echo "Transaction not found";