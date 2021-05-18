<?php

namespace GlobalPayments\PaymentGatewayProvider\Gateways;

use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\PaymentGatewayProvider\Data\Order;

/**
 * Shared gateway method implementations
 */
interface GatewayInterface
{
    /**
     * Required options for proper client-side configuration.
     *
     * @return Array<string,string>
     */
    public function getFrontendGatewayOptions();

    /**
     * Required options for proper server-side configuration.
     *
     * @return Array<string,string>
     */
    public function getBackendGatewayOptions();

    /**
     * Email address of the first-line support team
     *
     * @return string
     */
    public function getFirstLineSupportEmail();

    /**
     * Get the current gateway provider
     *
     * @return string
     */
    public function getGatewayProvider();

    /**
     * Configuration for the secure payment fields. Used on server- and
     * client-side portions of the integration.
     *
     * @return mixed[]
     */
    public function securePaymentFieldsConfiguration();

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
    public function securePaymentFieldHtmlFormat();

    /**
     * Handle payment functions
     *
     * @param Order $order
     *
     * @throws ApiException
     *
     * @return Transaction
     */
    public function processPayment(Order $order);

    /**
     * Handle adding new cards
     *
     * @param Order $order
     *
     * @throws ApiException
     *
     * @return Transaction
     */
    public function addPaymentMethod(Order $order);

    /**
     * Handle online refund requests
     *
     * @param Order $order
     *
     * @throws ApiException
     *
     * @return Transaction
     */
    public function processRefund(Order $order);

    /**
     * Get transaction details
     *
     * @param Order $order
     *
     * @throws ApiException
     *
     * @return TransactionSummary
     */
    public function getTransactionDetails(Order $order);

    /**
     * Should be overridden by each gateway implementation
     *
     * @param string $responseCode
     *
     * @return string
     */
    public function getDeclineMessage(string $responseCode);
}
