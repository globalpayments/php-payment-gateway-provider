<?php

namespace GlobalPayments\PaymentGatewayProvider\Requests;

use GlobalPayments\PaymentGatewayProvider\Data\Order;

interface RequestInterface
{
    /**
     * @param Order $order
     * @param Array<string,mixed> $config
     */
    public function __construct(Order $order, $config);

    /**
     * Gets transaction type for the request
     *
     * @return string
     */
    public function getTransactionType();

    /**
     * Gets a specific request argument by name
     *
     * @param string $key
     *
     * @return null|mixed
     */
    public function getArgument($key);

    /**
     * Sets request arguments
     *
     * @param Array<string,string> $data
     *
     * @return void
     */
    public function setArguments(array $data);

    /**
     * Gets request specific args
     *
     * @return string[]
     */
    public function getArguments();

    /**
     * Gets list of argument names
     *
     * @return string[]
     */
    public function getArgumentList();

    /**
     * Gets default request argument names
     *
     * @return string[]
     */
    public function getDefaultArgumentList();
}
