<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Order_Debug extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/order/debug.phtml');
    }

    protected function _beforeToHtml()
    {
        /** @var $order Ess_M2ePro_Model_Order */
        $order = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        /** @var $store Mage_Core_Model_Store */
        $store = Mage::getModel('core/store')->load($order->getStoreId());

        /** @var $storeConfigurator Ess_M2ePro_Model_Magento_Quote_Store_Configurator */
        $storeConfigurator = Mage::getModel('M2ePro/Magento_Quote_Store_Configurator');
        $storeConfigurator->init(Mage::getModel('sales/quote'), $order->getProxy());

        $this->setData(
            'product_price_includes_tax', $storeConfigurator->isPriceIncludesTax()
        );
        $this->setData(
            'shipping_price_includes_tax', $storeConfigurator->isShippingPriceIncludesTax()
        );
        $this->setData(
            'store_shipping_tax_class', $storeConfigurator->getShippingTaxClassId()
        );
        $this->setData(
            'store_tax_calculation_based_on', $storeConfigurator->getTaxCalculationBasedOn()
        );

        if (!is_null($store->getId())) {

            $this->setData(
                'store_tax_calculation_algorithm', $store->getConfig(Mage_Tax_Model_Config::XML_PATH_ALGORITHM)
            );

            // ---------------------------------------
            $request = new Varien_Object();
            $request->setProductClassId($store->getConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS));

            /** @var $taxCalculator Mage_Tax_Model_Calculation */
            $taxCalculator = Mage::getSingleton('tax/calculation');

            $this->setData('store_shipping_tax_rate', $taxCalculator->getStoreRate($request, $store));
            // ---------------------------------------
        }
    }

    //########################################
}