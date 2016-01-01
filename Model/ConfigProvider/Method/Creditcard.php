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
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Model\ConfigProvider\Method;

class Creditcard extends AbstractConfigProvider
{
    /**#@+
     * Creditcard service codes.
     */
    const CREDITCARD_SERVICE_CODE_MASTERCARD    = 'mastercard';
    const CREDITCARD_SERVICE_CODE_VISA          = 'visa';
    const CREDITCARD_SERVICE_CODE_AMEX          = 'amex';
    const CREDITCARD_SERVICE_CODE_MAESTRO       = 'maestro';
    const CREDITCARD_SERVICE_CODE_VPAY          = 'vpay';
    const CREDITCARD_SERVICE_CODE_VISAELECTRON  = 'visaelectron';
    const CREDITCARD_SERVICE_CODE_CARTEBLEUE    = 'cartebleuevisa';
    const CREDITCARD_SERVICE_CODE_CARTEBANCAIRE = 'cartebancaire';
    /**#@-*/

    const XPATH_CREDITCARD_PAYMENT_FEE = 'payment/tig_buckaroo_creditcard/payment_fee';

    protected $issuers = [
        [
            'name' => 'American Express',
            'code' => self::CREDITCARD_SERVICE_CODE_AMEX,
        ],
        [
            'name' => 'Carte Bancaire',
            'code' => self::CREDITCARD_SERVICE_CODE_CARTEBANCAIRE,
        ],
        [
            'name' => 'Carte Bleue',
            'code' => self::CREDITCARD_SERVICE_CODE_CARTEBLEUE,
        ],
        [
            'name' => 'Maestro',
            'code' => self::CREDITCARD_SERVICE_CODE_MAESTRO,
        ],
        [
            'name' => 'MasterCard',
            'code' => self::CREDITCARD_SERVICE_CODE_MASTERCARD,
        ],
        [
            'name' => 'VISA',
            'code' => self::CREDITCARD_SERVICE_CODE_VISA,
        ],
        [
            'name' => 'VISA Electron',
            'code' => self::CREDITCARD_SERVICE_CODE_VISAELECTRON,
        ],
        [
            'name' => 'VPay',
            'code' => self::CREDITCARD_SERVICE_CODE_VPAY,
        ],
    ];

    /**
     * Add the active flag to the creditcard list. This is used in the checkout process.
     *
     * @return array
     */
    public function formatIssuers()
    {
        $issuers = parent::formatIssuers();
        $allowed = explode(',', $this->scopeConfig->getValue('payment/tig_buckaroo_creditcard/allowed_creditcards'));

        foreach($issuers as $key => $issuer) {
            $issuers[$key]['active'] = in_array($issuer['code'], $allowed);
        }

        return $issuers;
    }

    /**
     * @return array|void
     */
    public function getConfig()
    {
        $issuers = $this->formatIssuers();

        //@TODO: get cards dynamic
        $config = [
            'payment' => [
                'buckaroo' => [
                    'creditcards' => $issuers,
                    'response' => [],
                ],
            ],
        ];

        return $config;
    }

    /**
     * @return float
     */
    public function getPaymentFee()
    {
        $paymentFee = $this->scopeConfig->getValue(
            self::XPATH_CREDITCARD_PAYMENT_FEE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $paymentFee ? $paymentFee : false;
    }

    /**
     * @param string $cardType
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getCardName($cardType)
    {
        $config = $this->getConfig();
        foreach ($config['payment']['buckaroo']['creditcards'] as $card) {
            if ($card['code'] == $cardType) {
                return $card['name'];
            }
        }

        throw new \InvalidArgumentException("No card found for card type: {$cardType}");
    }

    /**
     * @param string $cardType
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getCardCode($cardType)
    {
        $config = $this->getConfig();
        foreach ($config['payment']['buckaroo']['creditcards'] as $card) {
            if ($card['name'] == $cardType) {
                return $card['code'];
            }
        }

        throw new \InvalidArgumentException("No card found for card type: {$cardType}");
    }
}
