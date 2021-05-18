<?php

namespace GlobalPayments\PaymentGatewayProvider\Requests;

class ReversalRequest extends AbstractRequest
{
    public function getTransactionType()
    {
        return TransactionType::REVERSAL;
    }

    /**
     * @return string[]
     */
    public function getArgumentList()
    {
        return array(RequestArg::AMOUNT, RequestArg::CURRENCY, RequestArg::GATEWAY_ID, RequestArg::DESCRIPTION);
    }
}
