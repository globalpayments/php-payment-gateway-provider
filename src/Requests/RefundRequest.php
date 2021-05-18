<?php

namespace GlobalPayments\PaymentGatewayProvider\Requests;

class RefundRequest extends AbstractRequest
{

    public function getTransactionType()
    {
        return TransactionType::REFUND;
    }

    /**
     * @return string[]
     */
    public function getArgumentList()
    {
        return array(RequestArg::AMOUNT, RequestArg::CURRENCY, RequestArg::GATEWAY_ID, RequestArg::DESCRIPTION);
    }
}
