<?php

namespace GlobalPayments\PaymentGatewayProvider\Requests;

class CreateManifestRequest extends AbstractRequest
{
    public function getTransactionType()
    {
        return TransactionType::CREATE_MANIFEST;
    }

    /**
     * @return string[]
     */
    public function getArgumentList()
    {
        return array();
    }
}
