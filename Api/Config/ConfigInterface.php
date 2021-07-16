<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Config;

use Magento\Framework\Exception\NoSuchEntityException;

interface ConfigInterface
{
    /**
     * XML Paths of configuration settings
     */
    public const XML_PATH_GENERAL_ENABLED = 'payment/cm_payments_general/enabled';
    public const XML_PATH_GENERAL_CURRENT_VERSION = 'payment/cm_payments_general/current_version';
    public const XML_PATH_GENERAL_MERCHANT_KEY = 'payment/cm_payments_general/merchant_key';
    public const XML_PATH_GENERAL_MERCHANT_NAME = 'payment/cm_payments_general/merchant_name';
    public const XML_PATH_GENERAL_MERCHANT_PASSWORD = 'payment/cm_payments_general/merchant_password';
    public const XML_PATH_GENERAL_MODE = 'payment/cm_payments_general/mode';
    public const XML_PATH_PAYMENT_PROFILE = 'payment/cm_payments_methods/profile';
    public const XML_PATH_PAYMENT_CREDIT_CARD_PROFILE = 'payment/cm_payments_creditcard/profile';
    public const XML_PATH_PAYMENT_BANCONTACT_PROFILE = 'payment/cm_payments_bancontact/profile';

    /**
     * Checks that extension is enabled
     *
     * @return ?bool
     * @throws NoSuchEntityException
     */
    public function isEnabled(): ?bool;

    /**
     * Get Current Version
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getCurrentVersion(): ?string;

    /**
     * Get Merchant Key
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getMerchantKey(): ?string;

    /**
     * Get Merchant Name
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getMerchantName(): ?string;

    /**
     * Get Merchant Password
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getMerchantPassword(): ?string;

    /**
     * Get Payment Profile
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getPaymentProfile(): ?string;

    /**
     * Get mode
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getMode(): ?string;

    /**
     * Checks that payment method is active
     *
     * @param string $paymentMethodCode
     * @return ?bool
     * @throws NoSuchEntityException
     */
    public function isPaymentMethodActive(string $paymentMethodCode): ?bool;

    /**
     * Get Payment Profile for Credit Card Method
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getCreditCardPaymentProfile(): ?string;

    /**
     * Get Payment Profile for BanContact Method
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getBanContactPaymentProfile(): ?string;
}
