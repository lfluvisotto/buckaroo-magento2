<?php
/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Model\Method;

use Magento\Payment\Model\InfoInterface;
use TIG\Buckaroo\Gateway\Http\Transaction;

abstract class AbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var \TIG\Buckaroo\Gateway\GatewayInterface
     */
    protected $_gateway;

    /**
     * @var \TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory
     */
    protected $_transactionBuilderFactory;

    /**
     * AbstractMethod constructor.
     *
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory            $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory                 $customAttributeFactory
     * @param \Magento\Payment\Helper\Data                                 $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger                         $logger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param \TIG\Buckaroo\Gateway\GatewayInterface|null                  $gateway
     * @param \TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory|null    $transactionBuilderFactory
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \TIG\Buckaroo\Gateway\GatewayInterface $gateway = null,
        \TIG\Buckaroo\Gateway\Http\TransactionBuilderFactory $transactionBuilderFactory = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->_gateway = $gateway;
        $this->_transactionBuilderFactory = $transactionBuilderFactory;
    }

    /**
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        parent::authorize($payment, $amount);

        $transaction = $this->_getAuthorizeTransaction($payment);

        if ($transaction) {
            $this->_gateway->authorize($transaction);
        }
        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(InfoInterface $payment, $amount)
    {
        parent::capture($payment, $amount);

        $transaction = $this->_getCaptureTransaction($payment);

        $response = null;
        if ($transaction) {
            $response = $this->_gateway->capture($transaction);
        }

        // SET REGISTRY BUCKAROO REDIRECT
        $this->_registry->register('buckaroo_redirect_url', 'http://google.com');

        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(InfoInterface $payment, $amount)
    {
        parent::refund($payment, $amount);

        $transaction = $this->_getRefundTransaction($payment);

        if ($transaction) {
            $this->_gateway->refund($transaction);
        }
        return $this;
    }

    /**
     * @param InfoInterface $payment
     *
     * @return Transaction|false
     */
    protected abstract function _getAuthorizeTransaction($payment);

    /**
     * @param InfoInterface $payment
     *
     * @return Transaction|false
     */
    protected abstract function _getCaptureTransaction($payment);

    /**
     * @param InfoInterface $payment
     *
     * @return Transaction|false
     */
    protected abstract function _getRefundTransaction($payment);
}