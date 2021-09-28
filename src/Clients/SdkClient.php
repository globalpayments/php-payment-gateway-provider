<?php

namespace GlobalPayments\PaymentGatewayProvider\Clients;

use GlobalPayments\Api\Builders\TransactionBuilder;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\Enums\CardType;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Gateways\IPaymentGateway;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\AcceptorConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\GatewayConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\GeniusConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransitConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\PaymentGatewayProvider\Data\PaymentTokenData;
use GlobalPayments\PaymentGatewayProvider\Requests\RequestArg;
use GlobalPayments\PaymentGatewayProvider\Requests\RequestInterface;
use GlobalPayments\PaymentGatewayProvider\Requests\TransactionType;

class SdkClient implements ClientInterface
{
    /**
     * Current request arguments
     *
     * @var Array<string,mixed>
     */
    protected $arguments = array();

    /**
     * Prepared builder arguments
     *
     * @var mixed[]
     */
    protected $builderArgs = array();

    /**
     * @var string[]
     */
    protected $authTransactions = array(
        TransactionType::AUTHORIZE,
        TransactionType::SALE,
        TransactionType::VERIFY,
    );

    /**
     * @var string[]
     */
    protected $clientTransactions = array(
        TransactionType::CREATE_TRANSACTION_KEY,
        TransactionType::CREATE_MANIFEST,
    );

    /**
     * @var string[]
     */
    protected $refundTransactions = array(
        TransactionType::REFUND,
        TransactionType::REVERSAL,
        TransactionType::VOID,
    );

    /**
     * Card data
     *
     * @var CreditCardData
     */
    protected $cardData = null;

    /**
     * Previous transaction
     *
     * @var Transaction
     */
    protected $previousTransaction = null;

    public function setRequest(RequestInterface $request)
    {
        $this->arguments = $request->getArguments();
        $this->prepareRequestObjects();
        return $this;
    }

    public function execute()
    {
        $this->configureSdk();
        $builder = $this->getTransactionBuilder();

        if (!($builder instanceof TransactionBuilder)) {
            return $builder->{$this->getArgument(RequestArg::TXN_TYPE)}();
        }

        if ('transactionDetail' === $this->arguments['TXN_TYPE']) {
            return $builder->execute();
        }

        $this->prepareBuilder($builder);
        $response = $builder->execute();

        if ($response instanceof Transaction && $response->token) {
            $this->cardData->token = $response->token;
            $this->cardData->updateTokenExpiry();
        }

        return $response;
    }

    /**
     * @return void
     */
    protected function prepareBuilder(TransactionBuilder $builder)
    {
        foreach ($this->builderArgs as $name => $arguments) {
            $method = 'with' . ucfirst($name);

            if (!method_exists($builder, $method)) {
                continue;
            }

            /**
             * @var callable
             */
            $callable = array($builder, $method);
            call_user_func_array($callable, $arguments);
        }
    }

    /**
     * Gets required builder for the transaction
     *
     * @return TransactionBuilder|IPaymentGateway
     */
    protected function getTransactionBuilder()
    {
        $result = null;

        if (in_array($this->getArgument(RequestArg::TXN_TYPE), $this->clientTransactions, true)) {
            $result = ServicesContainer::instance()->getClient('default'); // this value should always be safe here
        } elseif ($this->getArgument(RequestArg::TXN_TYPE) === 'transactionDetail') {
            $result = ReportingService::transactionDetail($this->getArgument('GATEWAY_ID'));
        } elseif (in_array($this->getArgument(RequestArg::TXN_TYPE), $this->refundTransactions, true)) {
            $subject = Transaction::fromId($this->getArgument('GATEWAY_ID'));
            $result = $subject->{$this->getArgument(RequestArg::TXN_TYPE)}();
        } else {
            $subject =
                in_array($this->getArgument(RequestArg::TXN_TYPE), $this->authTransactions, true)
                ? $this->cardData : $this->previousTransaction;
                $result = $subject->{$this->getArgument(RequestArg::TXN_TYPE)}();
        }

        return $result;
    }

    /**
     * @return void
     */
    protected function prepareRequestObjects()
    {
        if ($this->hasArgument(RequestArg::AMOUNT)) {
            $this->builderArgs['amount'] = array($this->getArgument(RequestArg::AMOUNT));
        }

        if ($this->hasArgument(RequestArg::CURRENCY)) {
            $this->builderArgs['currency'] = array($this->getArgument(RequestArg::CURRENCY));
        }

        if ($this->hasArgument(RequestArg::CARD_DATA)) {
            $token = $this->getArgument(RequestArg::CARD_DATA);
            $this->prepareCardData($token);

            if (null !== $token && $this->hasArgument(RequestArg::CARD_HOLDER_NAME)) {
                $this->cardData->cardHolderName = $this->getArgument(RequestArg::CARD_HOLDER_NAME);
            }

            if ($this->hasArgument(RequestArg::REQUEST_MULTI_USE_TOKEN)) {
                $this->builderArgs['requestMultiUseToken'] = array(true);
            }
        }

        if ($this->hasArgument(RequestArg::BILLING_ADDRESS)) {
            $this->prepareAddress(AddressType::BILLING, $this->getArgument(RequestArg::BILLING_ADDRESS));
        }

        if ($this->hasArgument(RequestArg::SHIPPING_ADDRESS)) {
            $this->prepareAddress(AddressType::SHIPPING, $this->getArgument(RequestArg::SHIPPING_ADDRESS));
        }

        if ($this->hasArgument(RequestArg::DESCRIPTION)) {
            $this->builderArgs['description'] = array($this->getArgument(RequestArg::DESCRIPTION));
        }

        if ($this->hasArgument(RequestArg::AUTH_AMOUNT)) {
            $this->builderArgs['authAmount'] = array($this->getArgument(RequestArg::AUTH_AMOUNT));
        }

        if ($this->hasArgument(RequestArg::INVOICE_NUMBER)) {
            $this->builderArgs['invoiceNumber'] = array($this->getArgument(RequestArg::INVOICE_NUMBER));
        }
    }

    /**
     * @return void
     */
    protected function prepareCardData(\stdClass $token = null)
    {
        if (null === $token) {
            return;
        }

        $this->cardData           = new CreditCardData();
        $this->cardData->token    = $token->paymentReference;

        if (isset($token->details->expiryYear)) {
            $this->cardData->expYear  = $token->details->expiryYear;
        }

        if (isset($token->details->expiryMonth)) {
            $this->cardData->expMonth = $token->details->expiryMonth;
        }

        if (isset($token->details->cardSecurityCode)) {
            $this->cardData->cvn = $token->details->cardSecurityCode;
        }

        if (isset($token->details->cardType)) {
            switch ($token->details->cardType) {
                case 'visa':
                    $this->cardData->cardType = CardType::VISA;
                    break;
                case 'mastercard':
                    $this->cardData->cardType = CardType::MASTERCARD;
                    break;
                case 'amex':
                    $this->cardData->cardType = CardType::AMEX;
                    break;
                case 'diners':
                case 'discover':
                case 'jcb':
                    $this->cardData->cardType = CardType::DISCOVER;
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * @param string $addressType
     * @param Array<string,string> $data
     *
     * @return void
     */
    protected function prepareAddress($addressType, array $data)
    {
        $address       = new Address();
        $address->type = $addressType;
        $address       = $this->setObjectData($address, $data);

        $this->builderArgs['address'] = array($address, $addressType);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function hasArgument($name)
    {
        return isset($this->arguments[$name]);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function getArgument($name)
    {
        return $this->arguments[$name];
    }

    /**
     * @return void
     */
    protected function configureSdk()
    {
        $gatewayConfig = null;

        switch ($this->arguments['SERVICES_CONFIG']['gatewayProvider']) {
            case GatewayProvider::PORTICO:
                $gatewayConfig = new PorticoConfig();
                break;
            case GatewayProvider::TRANSIT:
                $gatewayConfig = new TransitConfig();
                // @phpstan-ignore-next-line
                $gatewayConfig->acceptorConfig = new AcceptorConfig(); // defaults should work here
                break;
            case GatewayProvider::GENIUS:
                $gatewayConfig = new GeniusConfig();
                break;
            default:
                break;
        }

        if (null === $gatewayConfig) {
            return;
        }

        $config = $this->setObjectData(
            $gatewayConfig,
            $this->arguments[RequestArg::SERVICES_CONFIG]
        );

        ServicesContainer::configureService($config);
    }

    /**
     * @param object $obj
     * @param Array<string,mixed> $data
     *
     * @return object
     */
    protected function setObjectData($obj, array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($obj, $key)) {
                $obj->{$key} = $value;
            }
        }
        return $obj;
    }
}
