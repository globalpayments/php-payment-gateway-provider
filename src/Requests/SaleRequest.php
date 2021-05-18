<?php

namespace GlobalPayments\PaymentGatewayProvider\Requests;

class SaleRequest extends AuthorizationRequest
{
    public function getTransactionType()
    {
        return TransactionType::SALE;
    }
}
