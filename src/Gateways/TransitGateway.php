<?php

namespace GlobalPayments\PaymentGatewayProvider\Gateways;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\PaymentGatewayProvider\Data\Order;
use GlobalPayments\PaymentGatewayProvider\Requests;

class TransitGateway extends AbstractGateway
{
    public $gatewayProvider = GatewayProvider::TRANSIT;

    /**
     * Merchant location's Merchant ID
     *
     * @var string
     */
    public $merchantId;

    /**
     * Merchant location's User ID
     *
     * Note: only needed to create transation key
     *
     * @var string
     */
    public $userId;

    /**
     * Merchant location's Password
     *
     * Note: only needed to create transation key
     *
     * @var string
     */
    public $password;

    /**
     * Merchant location's Device ID
     *
     * @var string
     */
    public $deviceId;

    /**
     * Device ID for TSEP entity specifically
     *
     * @var string
     */
    public $tsepDeviceId;

    /**
     * Merchant location's Transaction Key
     *
     * @var string
     */
    public $transactionKey;

    /**
     * Should live payments be accepted
     *
     * @var bool
     */
    public $isProduction;

    /**
     * Integration's Developer ID
     *
     * @var string
     */
    public $developerId;

    public function getFirstLineSupportEmail()
    {
        return '';
    }

    public function getFrontendGatewayOptions()
    {
        return array(
            'deviceId' => $this->tsepDeviceId,
            'manifest' => $this->createManifest(),
            'env'       => $this->isProduction ? 'production' : 'sandbox',
        );
    }

    public function getBackendGatewayOptions()
    {
        return array(
            'merchantId'     => $this->merchantId,
            'username'       => $this->userId, // only needed to create transation key
            'password'       => $this->password, // only needed to create transation key
            'transactionKey' => $this->transactionKey,
            'tsepDeviceId'   => $this->tsepDeviceId,
            'deviceId'       => $this->deviceId,
            'developerId'    => $this->developerId, // provided during certification
            'environment'    => $this->isProduction ? Environment::PRODUCTION : Environment::TEST,
        );
    }

    /**
     * Creates a TransIT transaction key
     *
     * @return string
     */
    public function createTransactionKey()
    {
        $request  = $this->prepareRequest(Requests\TransactionType::CREATE_TRANSACTION_KEY, new Order());
        $response = $this->submitRequest($request);

        // TODO: update Transaction type to declare transactionKey property
        // @phpstan-ignore-next-line
        return $response->transactionKey;
    }

    /**
     * Creates a TransIT manifest string
     *
     * @return string
     */
    public function createManifest()
    {
        $request = $this->prepareRequest(Requests\TransactionType::CREATE_MANIFEST, new Order());
        $manifest = $this->submitRequest($request);

        if (!is_string($manifest)) {
            throw new ApiException("Unexpected transaction response");
        }

        return $manifest;
    }
}
