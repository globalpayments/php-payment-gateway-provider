<?php

namespace GlobalPayments\PaymentGatewayProvider\Clients;

use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\PaymentGatewayProvider\Requests\RequestInterface;

interface ClientInterface
{
    /**
     * Sets request object for gateway request. Triggers creation of SDK
     * compatible objects from request data.
     *
     * @param RequestInterface $request
     *
     * @return ClientInterface
     */
    public function setRequest(RequestInterface $request);

    /**
     * Executes desired transaction with gathered data
     *
     * @return Transaction
     */
    public function execute();
}
