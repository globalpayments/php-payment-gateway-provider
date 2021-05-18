<?php

namespace GlobalPayments\PaymentGatewayProvider\Requests;

class TransactionDetailRequest extends AbstractRequest
{
    public function getTransactionType()
    {
        return TransactionType::REPORT_TXN_DETAILS;
    }

    /**
     * @return string[]
     */
    public function getArgumentList()
    {
        return array(RequestArg::GATEWAY_ID);
    }
}
