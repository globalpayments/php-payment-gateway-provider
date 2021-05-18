<?php

namespace GlobalPayments\PaymentGatewayProvider\Gateways;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;

class HeartlandGateway extends AbstractGateway
{
    public $gatewayProvider = GatewayProvider::PORTICO;

    /**
     * Merchant location public API key
     *
     * Used for single-use tokenization on frontend
     *
     * @var string
     */
    public $publicKey;

    /**
     * Merchant location secret API key
     *
     * Used for gateway transactions on backend
     *
     * @var string
     */
    public $secretKey;

    /**
     * Should live payments be accepted
     *
     * @var bool
     */
    public $isProduction;

    public function getFirstLineSupportEmail()
    {
        return 'onlinepayments@heartland.us';
    }

    public function getFrontendGatewayOptions()
    {
        return array(
            'publicApiKey' => $this->publicKey,
        );
    }

    public function getBackendGatewayOptions()
    {
        return array(
            'secretApiKey' => $this->secretKey,
            'versionNumber' => '1510',
            'developerId' => '002914'
        );
    }

    public function isTransactionActive(TransactionSummary $details)
    {
        return 'A' === $details->transactionStatus;
    }

    /**
     * returns decline message for display to customer
     *
     * @param string $responseCode
     *
     * @return string
     */
    public function getDeclineMessage(string $responseCode)
    {
        switch ($responseCode) {
            case '02':
            case '03':
            case '04':
            case '05':
            case '41':
            case '43':
            case '44':
            case '51':
            case '56':
            case '61':
            case '62':
            case '62':
            case '63':
            case '65':
            case '78':
                return 'The card was declined.';
            case '06':
            case '07':
            case '12':
            case '15':
            case '19':
            case '52':
            case '53':
            case '57':
            case '58':
            case '76':
            case '77':
            case '96':
            case 'EC':
                return 'An error occured while processing the card.';
            case '13':
                return 'Must be greater than or equal 0.';
            case '14':
                return 'The card number is incorrect.';
            case '54':
                return 'The card has expired.';
            case '55':
                return 'The pin is invalid.';
            case '75':
                return 'Maximum number of pin retries exceeded.';
            case '80':
                return 'Card expiration date is invalid.';
            case '86':
                return 'Can\'t verify card pin number.';
            case '91':
                return 'The card issuer timed-out.';
            case 'EB':
            case 'N7':
                return 'The card\'s security code is incorrect.';
            case 'FR':
                return 'Possible fraud detected.';
            default:
                return 'An error occurred while processing the card.';
        }
    }
}
