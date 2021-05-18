<?php

namespace GlobalPayments\PaymentGatewayProvider\Data;

use GlobalPayments\PaymentGatewayProvider\Requests\RequestArg;

class Order
{
    /**
     * @var string|float|integer
     */
    public $amount;

    /**
     * @var string|float|integer
     */
    public $authorizationAmount;

    /**
     * @var Array<string,string>
     */
    public $billingAddress;

    /**
     * @var Array<string,string>
     */
    public $cardData;

    /**
     * @var string
     */
    public $cardHolderName;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var string
     */
    public $description;

    /**
     * @var bool
     */
    public $requestMultiUseToken;

    /**
     * @var Array<string,string>
     */
    public $shippingAddress;

    /**
     * @var string
     */
    public $transactionType;

    /**
     * @return Array<string,mixed>
     */
    public function asArray()
    {
        return array(
            RequestArg::AMOUNT           => $this->amount,
            RequestArg::AUTH_AMOUNT      => $this->authorizationAmount,
            RequestArg::BILLING_ADDRESS  => $this->billingAddress,
            RequestArg::CARD_DATA        => $this->cardData,
            RequestArg::CARD_HOLDER_NAME => $this->cardHolderName,
            RequestArg::CURRENCY         => $this->currency,
            RequestArg::DESCRIPTION      => $this->description,
            RequestArg::REQUEST_MULTI_USE_TOKEN => $this->requestMultiUseToken,
            RequestArg::SHIPPING_ADDRESS => $this->shippingAddress,
            RequestArg::TXN_TYPE         => $this->transactionType,
        );
    }
}
