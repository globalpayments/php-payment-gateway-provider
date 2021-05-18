<?php

namespace GlobalPayments\PaymentGatewayProvider\Requests;

class VerifyRequest extends AbstractRequest
{
    public function getTransactionType()
    {
        return TransactionType::VERIFY;
    }

    /**
     * @return string[]
     */
    public function getArgumentList()
    {
        return array(RequestArg::CARD_DATA);
    }
}
