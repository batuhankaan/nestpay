# Banca Intesa NestPay Interface

## Usage:

```php
<?php
use NestPay\NestPayInterface; 
 
NestPayInterface::getInstance()->getSettings()
    ->setMerchantId('-- your merchant id --')
    ->setStoreKey('-- your store key --')
    ->setTestMode('-- true or false depending on test mode --')
    ->setApiUser('-- your api username --')
    ->setApiPass('-- your api pass --')
    ->setDmsMode('-- true or false depending on mode --')
    ->setLogDir('-- path to your log dir --')
    ->setMySQLDbInterface();
 
// to setup tables:
NestPayInterface::getInstance()->setup();
 
// to initialize order:
// $merchantOrderId is your order id; if already in system (retrying), old one will be reused
// $amount is float amount
// $currency - currently supported is \NestPay\CURRENCY_RSD
// $lang: currently, 'en', 'tr'
// $okUrl, $failUrl - return urls
NestPayInterface::getInstance()->getOrder($merchantOrderId, $amount, $currency, $lang, $okUrl, $failUrl);
 
// get order by merchant order id
NestPayInterface::getInstance()->getOrderByMerchantOrderId($merchantOrderId);
 
// to check if order is paid:
NestPayInterface::getInstance()->isPaid($merchantOrderId);
 
// to check if order is captured:
NestPayInterface::getInstance()->isCaptured($merchantOrderId);
 
// to check if order is voided:
NestPayInterface::getInstance()->isVoided($merchantOrderId);
 
// to check if payment could be retried
NestPayInterface::getInstance()->canRetry($merchantOrderId);
 
// capture payment:
NestPayInterface::getInstance()->capture($merchantOrderId);
 
// void payment:
NestPayInterface::getInstance()->void($merchantOrderId);
 
// get redirection form:
// copmlete page - display complete page and redirect, otherwise form will be returned
// button text - if you want to display button if js redirection fails
NestPayInterface::getInstance()->goPay($merchantOrderId, $completePage = true, $buttonText = null);
 
// get transaction results on return url:
// $params - array of posted params; if omitted, $_POST will be used
// returns object of class \NestPay\Transaction
NestPayInterface::getInstance()->getResults($params = null);
 
// get last transaction for order id:
NestPayInterface::getInstance()->getLastTransaction($merchantOrderId);
```

## Test Cards
- MC Gold 	5444287397322864	10/16	538
- Visa Gold	4062224200052846	10/16	014
- Visa Classic	4183399340441747	10/16	133
- Visa Inspire	4841878737128854	10/17	644
- AMEX	377522900011006	08/16	9999

for successful transaction, enter cvc 00x
