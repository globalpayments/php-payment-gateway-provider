<?php

namespace GlobalPayments\PaymentGatewayProvider\Requests;

class AuthorizationRequest extends AbstractRequest
{
    public function getTransactionType()
    {
        return TransactionType::AUTHORIZE;
    }

    /**
     * @return string[]
     */
    public function getArgumentList()
    {
        return array(RequestArg::AMOUNT, RequestArg::CURRENCY, RequestArg::CARD_DATA);
    }
}
