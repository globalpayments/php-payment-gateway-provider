<?php

namespace GlobalPayments\PaymentGatewayProvider\Requests;

use GlobalPayments\PaymentGatewayProvider\Data\Order;

abstract class AbstractRequest implements RequestInterface
{
    /**
     * Request data
     *
     * @var Array<string,mixed>
     */
    protected $data = array();

    /**
     * @param Order $order
     * @param Array<string,mixed> $config
     */
    public function __construct(Order $order, $config)
    {
        $this->data = $order->asArray();
        $this->data[RequestArg::SERVICES_CONFIG] = $config;
    }

    public function getArgument($key)
    {
        return $this->data[$key] ?: null;
    }

    public function getArguments()
    {
        return $this->data;
    }

    public function setArguments(array $data)
    {
        $argumentList = array_merge($this->getDefaultArgumentList(), $this->getArgumentList());

        foreach ($data as $key => $value) {
            if (!in_array($key, $argumentList)) {
                continue;
            }

            $this->data[$key] = $value;
        }
    }

    /**
     * @return string[]
     */
    public function getDefaultArgumentList()
    {
        return array(
            RequestArg::SERVICES_CONFIG,
            RequestArg::TXN_TYPE,
            RequestArg::BILLING_ADDRESS,
            RequestArg::CARD_HOLDER_NAME,
        );
    }
}
