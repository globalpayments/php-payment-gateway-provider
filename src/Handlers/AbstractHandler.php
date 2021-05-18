<?php

namespace GlobalPayments\PaymentGatewayProvider\Handlers;

use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\PaymentGatewayProvider\Requests\RequestInterface;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * Current request
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Current response
     *
     * @var Transaction
     */
    protected $response;

    /**
     * Instantiates a new request
     *
     * @param RequestInterface $request
     * @param Transaction $response
     */
    public function __construct(RequestInterface $request, Transaction $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }
}
