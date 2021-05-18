<?php

namespace GlobalPayments\PaymentGatewayProvider\Handlers;

use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\PaymentGatewayProvider\Requests\RequestInterface;

interface HandlerInterface
{
    /**
     * Instantiates a new request
     *
     * @param RequestInterface $request
     * @param Transaction $response
     */
    public function __construct(RequestInterface $request, Transaction $response);

    /**
     * Handles the response
     *
     * @return null|Array<string,string>
     */
    public function handle();
}
