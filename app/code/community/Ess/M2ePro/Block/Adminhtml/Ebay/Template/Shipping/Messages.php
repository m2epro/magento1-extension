<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Shipping_Messages
    extends Ess_M2ePro_Block_Adminhtml_Template_Messages
{
    const TYPE_CURRENCY_CONVERSION = 'currency_conversion';

    //########################################

    public function getMessages()
    {
        $messages = array();

        // ---------------------------------------
        $message = $this->getCurrencyConversionMessage();
        if ($message !== null) {
            $messages[self::TYPE_CURRENCY_CONVERSION] = $message;
        }

        // ---------------------------------------

        $messages = array_merge($messages, parent::getMessages());

        return $messages;
    }

    //########################################

    public function getCurrencyConversionMessage($marketplaceCurrency = null)
    {
        if ($this->getMarketplace() === null) {
            return null;
        }

        if ($marketplaceCurrency === null) {
            $marketplaceCurrency = $this->getMarketplace()->getChildObject()->getCurrency();
        }

        if (!$this->canDisplayCurrencyConversionMessage($marketplaceCurrency)) {
            return NULL;
        }

        $storePath = Mage::helper('M2ePro/Magento_Store')->getStorePath($this->getStore()->getId());
        $allowed = Mage::getSingleton('M2ePro/Currency')
            ->isAllowed(
                $marketplaceCurrency,
                $this->getStore()
            );

        if (!$allowed) {
            $currencySetupUrl = Mage::helper('adminhtml')->getUrl(
                'adminhtml/system_config/edit',
                array(
                    'section' => 'currency',
                    'website' => !$this->getStore()->isAdmin() ? $this->getStore()->getWebsite()->getCode() : null,
                    'store'   => !$this->getStore()->isAdmin() ? $this->getStore()->getCode() : null
                )
            );

            // M2ePro_TRANSLATIONS
            // Currency "%currency_code%" is not allowed in <a href="%url%" target="_blank">Currency Setup</a>
            // for Store View "%store_path%" of your Magento. Currency conversion will not be performed.
            $messageText =
                Mage::helper('M2ePro')->__(
                    'Currency "%currency_code%" is not allowed in <a href="%url%" target="_blank">Currency Setup</a> '
                    . 'for Store View "%store_path%" of your Magento. '
                    . 'Currency conversion will not be performed.',
                    $marketplaceCurrency,
                    $currencySetupUrl,
                    Mage::helper('M2ePro')->escapeHtml($storePath)
                );
        } else {
            $rate = Mage::getSingleton('M2ePro/Currency')
                ->getConvertRateFromBase(
                    $marketplaceCurrency,
                    $this->getStore(),
                    4
                );

            // M2ePro_TRANSLATIONS
            // There is no rate for "%currency_from%-%currency_to%" in
            // <a href="%url%" target="_blank">Manage Currency Rates</a> of your Magento.
            // Currency conversion will not be performed.
            if ($rate == 0) {
                $messageText =
                    Mage::helper('M2ePro')->__(
                        'There is no rate for "%currency_from%-%currency_to%" in'
                        . ' <a href="%url%" target="_blank">Manage Currency Rates</a> of your Magento.'
                        . ' Currency conversion will not be performed.',
                        $this->getStore()->getBaseCurrencyCode(),
                        $marketplaceCurrency,
                        Mage::helper('adminhtml')->getUrl('adminhtml/system_currency')
                    );
            } else {
                // M2ePro_TRANSLATIONS
                // There is a rate %value% for "%currency_from%-%currency_to%" in
                // <a href="%url%" target="_blank">Manage Currency Rates</a> of your Magento.
                // Currency conversion will be performed automatically.
                $message =
                    Mage::helper('M2ePro')->__(
                        'There is a rate %value% for "%currency_from%-%currency_to%" in'
                        . ' <a href="%url%" target="_blank">Manage Currency Rates</a> of your Magento.'
                        . ' Currency conversion will be performed automatically.',
                        $rate,
                        $this->getStore()->getBaseCurrencyCode(),
                        $marketplaceCurrency,
                        Mage::helper('adminhtml')->getUrl('adminhtml/system_currency')
                    );

                $messageText = '<span style="color: #3D6611 !important;">' . $message . '</span>';
            }
        }

        if ($messageText === null) {
            return NULL;
        }

        $toolTipIconSrc = $this->getSkinUrl('M2ePro/images/tool-tip-icon.png');
        $helpIconSrc = $this->getSkinUrl('M2ePro/images/help.png');

        $docUrl = 'http://www.magentocommerce.com/wiki/modules_reference/English/Mage_Adminhtml/system_currency/index';

        // M2ePro_TRANSLATIONS
        // More about Currency rate set-up can be found in the <a href="%url%" target="_blank">Magento documentation</a>
        $helpText = 'More about Currency rate set-up can be found in the ';
        $helpText .= '<a href="%url%" target="_blank">Magento documentation</a>';
        $helpText = Mage::helper('M2ePro')->__($helpText, $docUrl);

        return <<<HTML
{$messageText}
<div style="display: inline-block;">
    <img src="{$toolTipIconSrc}" class="tool-tip-image">
    <span class="tool-tip-message" style="font-size: 12px; display: none;">
        <img src="{$helpIconSrc}">
        <span>{$helpText}</span>
    </span>
</div>
HTML;
    }

    //########################################

    protected function canDisplayCurrencyConversionMessage($marketplaceCurrency)
    {
        if ($this->getStore() === null) {
            return false;
        }

        if (Mage::getSingleton('M2ePro/Currency')->isBase($marketplaceCurrency, $this->getStore())) {
            return false;
        }

        $template = Mage::getModel('M2ePro/Ebay_Template_Shipping');
        $template->addData($this->getTemplateData());

        $attributes = array();
        if ($template->getId()) {
            $services = $template->getServices(true);

            foreach ($services as $service) {
                /** @var Ess_M2ePro_Model_Ebay_Template_Shipping_Service $service */
                $attributes = array_merge($attributes, $service->getUsedAttributes());
            }
        } else {
            $shippingCostAttributes = $template->getData('shipping_cost_attribute');

            if (!empty($shippingCostAttributes)) {
                $attributes = array_merge($attributes, $shippingCostAttributes);
            }

            $shippingCostAdditionalAttributes = $template->getData('shipping_cost_additional_attribute');

            if (!empty($shippingCostAdditionalAttributes)) {
                $attributes = array_merge($attributes, $shippingCostAdditionalAttributes);
            }

            $shippingCostSurchargeAttributes = $template->getData('shipping_cost_surcharge_attribute');

            if (!empty($shippingCostSurchargeAttributes)) {
                $attributes = array_merge($attributes, $shippingCostSurchargeAttributes);
            }
        }

        $preparedAttributes = array();
        foreach (array_filter($attributes) as $attribute) {
            $preparedAttributes[] = array('code' => $attribute);
        }

        $attributes = Mage::helper('M2ePro/Magento_Attribute')->filterByInputTypes(
            $preparedAttributes, array('price')
        );

        if (count($attributes)) {
            return true;
        }

        return false;
    }

    //########################################
}
