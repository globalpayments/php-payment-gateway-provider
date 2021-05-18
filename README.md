#

## Installation

```sh
composer require globalpayments/payment-gateway-provider
```

## Examples

```php
use GlobalPayments\PaymentGatewayProvider\Data\Order;
use GlobalPayments\PaymentGatewayProvider\Gateways\TransitGateway;

$gateway = new TransitGateway();

// configure gateway settings
$gateway->merchantId = '';
$gateway->userId = '';
$gateway->password = '';
$gateway->deviceId = '';
$gateway->tsepDeviceId = '';
$gateway->transactionKey = '';
$gateway->isProduction = false;
$gateway->developerId = '';
$gateway->paymentAction = TransitGateway::TXN_TYPE_AUTHORIZE;

// admin
$gateway->getFrontendGatewayOptions();
$gateway->getBackendGatewayOptions();
$gateway->getFirstLineSupportEmail();
$gateway->securePaymentFieldsConfiguration();
$gateway->securePaymentFieldHtmlFormat();

// order information
$order = new Order();
$order->amount = ''; // total / original amount
$order->authorizationAmount = ''; // new authorization amount for reversals
$order->billingAddress = array();
$order->cardData = array();
$order->cardHolderName = '';
$order->currency = '';
$order->description = '';
$order->requestMultiUseToken = false;
$order->shippingAddress = array();

// submitting requests
$response = $gateway->createTransactionKey();
$response = $gateway->createManifest();
$response = $gateway->processPayment($order);
$response = $gateway->addPaymentMethod($order);
$response = $gateway->processRefund($order);
$response = $gateway->getTransactionDetails($order);
```