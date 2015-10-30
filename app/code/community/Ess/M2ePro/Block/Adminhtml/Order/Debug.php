<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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

        if (!is_null($store->getId())) {
            $this->setData(
                'store_tax_calculation_algorithm',
                $store->getConfig(Mage_Tax_Model_Config::XML_PATH_ALGORITHM)
            );
            $this->setData(
                'store_tax_calculation_based_on',
                $store->getConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON)
            );
            $this->setData(
                'store_price_includes_tax',
                $store->getConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX)
            );
            $this->setData(
                'store_shipping_price_includes_tax',
                $store->getConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX)
            );

            $taxClass = Mage::getModel('tax/class')->load(
                $store->getConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS)
            );
            $this->setData('store_shipping_tax_class', $taxClass->getClassName());

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