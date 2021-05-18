<?php

namespace GlobalPayments\PaymentGatewayProvider\Requests;

class CreateTransactionKeyRequest extends AbstractRequest
{
    public function getTransactionType()
    {
        return TransactionType::CREATE_TRANSACTION_KEY;
    }

    /**
     * @return string[]
     */
    public function getArgumentList()
    {
        return array();
    }
}
