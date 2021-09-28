<?php

namespace GlobalPayments\PaymentGatewayProvider\Requests;

abstract class RequestArg
{
    public const AMOUNT           = 'AMOUNT';
    public const BILLING_ADDRESS  = 'BILLING_ADDRESS';
    public const CARD_DATA        = 'CARD_DATA';
    public const CARD_HOLDER_NAME = 'CARD_HOLDER_NAME';
    public const CURRENCY         = 'CURRENCY';
    public const SERVICES_CONFIG  = 'SERVICES_CONFIG';
    public const SHIPPING_ADDRESS = 'SHIPPING_ADDRESS';
    public const TXN_TYPE         = 'TXN_TYPE';
    public const GATEWAY_ID       = 'GATEWAY_ID';
    public const DESCRIPTION      = 'DESCRIPTION';
    public const AUTH_AMOUNT      = 'AUTH_AMOUNT';
    public const REQUEST_MULTI_USE_TOKEN = 'REQUEST_MULTI_USE_TOKEN';
    public const INVOICE_NUMBER   = 'INVOICE_NUMBER';
}
