<?php

namespace GlobalPayments\PaymentGatewayProvider\Gateways;

use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\PaymentGatewayProvider\Plugin;
use GlobalPayments\PaymentGatewayProvider\Requests;
use GlobalPayments\PaymentGatewayProvider\Clients\ClientInterface;
use GlobalPayments\PaymentGatewayProvider\Clients\SdkClient;
use GlobalPayments\PaymentGatewayProvider\Data\Order;
use GlobalPayments\PaymentGatewayProvider\Handlers\HandlerInterface;

/**
 * Shared gateway method implementations
 */
abstract class AbstractGateway implements GatewayInterface
{
    /**
     * Gateway provider. Should be overriden by individual gateway implementations
     *
     * @var string
     */
    public $gatewayProvider;

    /**
     * Action to perform on checkout
     *
     * Possible actions:
     *
     * - `authorize` - authorize the card without auto capturing
     * - `sale` - authorize the card with auto capturing
     * - `verify` - verify the card without authorizing
     *
     * @var string
     */
    public $paymentAction;

    /**
     * Error response handlers
     *
     * @var HandlerInterface[]
     */
    public $errorHandlers = array();

    /**
     * Success response handlers
     *
     * @var HandlerInterface[]
     */
    public $successHandlers = array();

    /**
     * Gateway HTTP client
     *
     * @var ClientInterface
     */
    protected $client;

    public function __construct()
    {
        $this->client = new SdkClient();
    }

    /**
     * Required options for proper client-side configuration.
     *
     * @return Array<string,string>
     */
    abstract public function getFrontendGatewayOptions();

    /**
     * Required options for proper server-side configuration.
     *
     * @return Array<string,string>
     */
    abstract public function getBackendGatewayOptions();

    /**
     * Email address of the first-line support team
     *
     * @return string
     */
    abstract public function getFirstLineSupportEmail();

    /**
     * Get the current gateway provider
     *
     * @return string
     */
    public function getGatewayProvider()
    {
        if (!$this->gatewayProvider) {
            // this shouldn't happen outside of our internal development
            throw new ApiException('Missing gateway provider configuration');
        }

        return $this->gatewayProvider;
    }

    /**
     * Configuration for the secure payment fields. Used on server- and
     * client-side portions of the integration.
     *
     * @return mixed[]
     */
    public function securePaymentFieldsConfiguration()
    {
        return array(
            'card-number-field' => array(
                'class'       => 'card-number',
                'label'       => 'Credit Card Number',
                'placeholder' => '•••• •••• •••• ••••',
                'messages'    => array(
                    'validation' => 'Please enter a valid Credit Card Number',
                ),
            ),
            'card-expiry-field' => array(
                'class'       => 'card-expiration',
                'label'       => 'Credit Card Expiration Date',
                'placeholder' => 'MM / YYYY',
                'messages'    => array(
                    'validation' => 'Please enter a valid Credit Card Expiration Date',
                ),
            ),
            'card-cvv-field'    => array(
                'class'       => 'card-cvv',
                'label'       => 'Credit Card Security Code',
                'placeholder' => '•••',
                'messages'    => array(
                    'validation' => 'Please enter a valid Credit Card Security Code',
                ),
            ),
        );
    }

    /**
     * The HTML template string for a secure payment field
     *
     * Format directives:
     *
     * 1) Gateway ID
     * 2) Field CSS class
     * 3) Field label
     * 4) Field validation message
     *
     * @return string
     */
    public function securePaymentFieldHtmlFormat()
    {
        return (
            '<div class="globalpayments %1$s %2$s">
				<label for="%1$s-%2$s">%3$s&nbsp;<span class="required">*</span></label>
				<div id="%1$s-%2$s"></div>
				<ul class="validation-error" style="display: none;">
					<li>%4$s</li>
				</ul>
			</div>'
        );
    }

    /**
     * Handle payment functions
     *
     * @param Order $order
     *
     * @throws ApiException
     *
     * @return Transaction
     */
    public function processPayment(Order $order)
    {
        $request  = $this->prepareRequest($this->paymentAction, $order);
        $response = $this->submitRequest($request);

        if (!($response instanceof Transaction)) {
            throw new ApiException("Unexpected transaction response");
        }

        $this->handleResponse($request, $response);

        return $response;
    }

    /**
     * Handle adding new cards
     *
     * @param Order $order
     *
     * @throws ApiException
     *
     * @return Transaction
     */
    public function addPaymentMethod(Order $order)
    {
        $request  = $this->prepareRequest(Requests\TransactionType::VERIFY, $order);
        $response = $this->submitRequest($request);

        if (!($response instanceof Transaction)) {
            throw new ApiException("Unexpected transaction response");
        }

        $this->handleResponse($request, $response);

        return $response;
    }

    /**
     * Handle online refund requests
     *
     * @param Order $order
     *
     * @throws ApiException
     *
     * @return Transaction
     */
    public function processRefund(Order $order)
    {
        $details            = $this->getTransactionDetails($order);
        $isOrderTxnIdActive = $this->isTransactionActive($details);
        $txnType            = $isOrderTxnIdActive ? Requests\TransactionType::REVERSAL : Requests\TransactionType::REFUND;

        $request  = $this->prepareRequest($txnType, $order);
        $response = $this->submitRequest($request);

        if (!($response instanceof Transaction)) {
            throw new ApiException("Unexpected transaction response");
        }

        $this->handleResponse($request, $response);

        return $response;
    }

    /**
     * Get transaction details
     *
     * @param Order $order
     *
     * @throws ApiException
     *
     * @return TransactionSummary
     */
    public function getTransactionDetails(Order $order)
    {
        $request  = $this->prepareRequest(Requests\TransactionType::REPORT_TXN_DETAILS, $order);
        $response = $this->submitRequest($request);

        if (!($response instanceof TransactionSummary)) {
            throw new ApiException("Unexpected transaction response");
        }

        return $response;
    }

    /**
     * Creates the necessary request based on the transaction type
     *
     * @param string $txnType
     * @param Order $order
     *
     * @return Requests\RequestInterface
     */
    protected function prepareRequest($txnType, Order $order)
    {
        $map = array(
            Requests\TransactionType::AUTHORIZE              => Requests\AuthorizationRequest::class,
            Requests\TransactionType::CREATE_MANIFEST        => Requests\CreateManifestRequest::class,
            Requests\TransactionType::CREATE_TRANSACTION_KEY => Requests\CreateTransactionKeyRequest::class,
            Requests\TransactionType::REFUND                 => Requests\RefundRequest::class,
            Requests\TransactionType::REVERSAL               => Requests\ReversalRequest::class,
            Requests\TransactionType::SALE                   => Requests\SaleRequest::class,
            Requests\TransactionType::REPORT_TXN_DETAILS     => Requests\TransactionDetailRequest::class,
            Requests\TransactionType::VERIFY                 => Requests\VerifyRequest::class,
        );

        if (!isset($map[$txnType])) {
            throw new ApiException('Cannot perform transaction');
        }

        $order->transactionType = $txnType;

        $request = $map[$txnType];
        return new $request(
            $order,
            array_merge(
                array('gatewayProvider' => $this->getGatewayProvider()),
                $this->getBackendGatewayOptions()
            )
        );
    }

    /**
     * Executes the prepared request
     *
     * @param Requests\RequestInterface $request
     *
     * @return Transaction|TransactionSummary|string
     */
    protected function submitRequest(Requests\RequestInterface $request)
    {
        return $this->client->setRequest($request)->execute();
    }

    /**
     * Reacts to the transaction response
     *
     * @param Requests\RequestInterface $request
     * @param Transaction|TransactionSummary|string $response
     *
     * @return bool
     */
    protected function handleResponse(Requests\RequestInterface $request, $response)
    {
        if (!($response instanceof Transaction)) {
            throw new ApiException("Unexpected transaction response");
        }

        /**
         * @var HandlerInterface[]
         */
        $handlers = $this->successHandlers;

        if ('00' !== $response->responseCode) {
            $handlers = $this->errorHandlers;
        }

        foreach ($handlers as $handler) {
            /**
             * Current handler
             *
             * @var HandlerInterface $h
             */
            $h = new $handler($request, $response);
            $h->handle();
        }

        return true;
    }

    /**
     * Should be overridden by each gateway implementation
     *
     * @return bool
     */
    protected function isTransactionActive(TransactionSummary $details)
    {
        return false;
    }

    /**
     * Should be overridden by each gateway implementation
     *
     * @return string
     */
    public function getDeclineMessage(string $responseCode)
    {
        return 'An error occurred while processing the card.';
    }
}
