<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Template_SellingFormat_Messages
    extends Ess_M2ePro_Block_Adminhtml_Template_Messages
{
    const TYPE_CURRENCY_CONVERSION = 'currency_conversion';

    //########################################

    public function getCurrencyConversionMessage($marketplaceCurrency = null)
    {
        if (is_null($this->getMarketplace())) {
            return NULL;
        }

        if (is_null($marketplaceCurrency)) {
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
            // Currency "%currency_code%" is not allowed in <a href="%url%" target="_blank">Currency Setup</a> for Store View "%store_path%" of your Magento. Currency conversion will not be performed.
            return
                Mage::helper('M2ePro')->__(
                    'Currency "%currency_code%" is not allowed in <a href="%url%" target="_blank">Currency Setup</a> '
                    . 'for Store View "%store_path%" of your Magento. '
                    . 'Currency conversion will not be performed.',
                $marketplaceCurrency,
                $currencySetupUrl,
                Mage::helper('M2ePro')->escapeHtml($storePath)
            );
        }

        $rate = Mage::getSingleton('M2ePro/Currency')
            ->getConvertRateFromBase(
                $marketplaceCurrency,
                $this->getStore(),
                4
            );

        // M2ePro_TRANSLATIONS
        // There is no rate for "%currency_from%-%currency_to%" in <a href="%url%" target="_blank">Manage Currency Rates</a> of your Magento. Currency conversion will not be performed.
        if ($rate == 0) {
            return
                Mage::helper('M2ePro')->__(
                    'There is no rate for "%currency_from%-%currency_to%" in'
                    . ' <a href="%url%" target="_blank">Manage Currency Rates</a> of your Magento.'
                    . ' Currency conversion will not be performed.',
                $this->getStore()->getBaseCurrencyCode(),
                $marketplaceCurrency,
                Mage::helper('adminhtml')->getUrl('adminhtml/system_currency')
            );
        }

        // M2ePro_TRANSLATIONS
        // There is a rate %value% for "%currency_from%-%currency_to%" in <a href="%url%" target="_blank">Manage Currency Rates</a> of your Magento. Currency conversion will be performed automatically.
        $message =
            Mage::helper('M2ePro')->__(
                'There is a rate %value% for "%currency_from%-%currency_to%" in'
                . ' <a href="%url%" target="_blank">Manage Currency Rates</a> of your Magento.'
                . ' Currency conversion will be performed automatically.'
            ,
            $rate,
            $this->getStore()->getBaseCurrencyCode(),
            $marketplaceCurrency,
            Mage::helper('adminhtml')->getUrl('adminhtml/system_currency')
        );

        return '<span style="color: #3D6611 !important;">' . $message . '</span>';
    }

    //########################################

    public function getMessages()
    {
        $messages = array();

        // ---------------------------------------
        if (!is_null($message = $this->getCurrencyConversionMessage())) {
            $messages[self::TYPE_CURRENCY_CONVERSION] = $message;
        }
        // ---------------------------------------

        $messages = array_merge($messages, parent::getMessages());

        return $messages;
    }

    //########################################

    protected function canDisplayCurrencyConversionMessage($marketplaceCurrency)
    {
        if (is_null($this->getStore())) {
            return false;
        }

        if (Mage::getSingleton('M2ePro/Currency')->isBase($marketplaceCurrency, $this->getStore())) {
            return false;
        }

        $template = $this->getTemplateModel();
        $template->addData($this->getTemplateData());

        if (!$template->usesProductOrSpecialPrice($marketplaceCurrency)) {
            return false;
        }

        return true;
    }

    //########################################

    protected function getTemplateModel()
    {
        $model = null;

        switch ($this->getComponentMode()) {
            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                $model = Mage::getModel('M2ePro/Ebay_Template_SellingFormat');
                break;
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $model = Mage::getModel('M2ePro/Amazon_Template_SellingFormat');
                break;
            case Ess_M2ePro_Helper_Component_Buy::NICK:
                $model = Mage::getModel('M2ePro/Buy_Template_SellingFormat');
                break;
        }

        if (is_null($model)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Policy model is unknown.');
        }

        return $model;
    }

    //########################################
}