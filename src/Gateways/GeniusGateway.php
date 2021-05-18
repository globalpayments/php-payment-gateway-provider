<?php

namespace GlobalPayments\PaymentGatewayProvider\Gateways;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;

class GeniusGateway extends AbstractGateway
{
    public $gatewayProvider = GatewayProvider::GENIUS;

    /**
     * Merchant location's Merchant Name
     *
     * @var string
     */
    public $merchantName;

    /**
     * Merchant location's Site ID
     *
     * @var string
     */
    public $merchantSiteId;

    /**
     * Merchant location's Merchant Key
     *
     * @var string
     */
    public $merchantKey;

    /**
     * Merchant location's Web API Key
     *
     * @var string
     */
    public $webApiKey;

    /**
     * Should live payments be accepted
     *
     * @var bool
     */
    public $isProduction;

    public function getFirstLineSupportEmail()
    {
        return '';
    }

    public function getFrontendGatewayOptions()
    {
        return array(
            'webApiKey' => $this->webApiKey,
            'env'       => $this->isProduction ? 'production' : 'sandbox',
        );
    }

    public function getBackendGatewayOptions()
    {
        return array(
            'merchantName'   => $this->merchantName,
            'merchantSiteId' => $this->merchantSiteId,
            'merchantKey'    => $this->merchantKey,
            'environment'    => $this->isProduction ? Environment::PRODUCTION : Environment::TEST,
        );
    }
}
