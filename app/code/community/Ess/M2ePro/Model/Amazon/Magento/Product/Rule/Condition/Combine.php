<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Magento_Product_Rule_Condition_Combine
    extends Ess_M2ePro_Model_Magento_Product_Rule_Condition_Combine
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setType('M2ePro/Amazon_Magento_Product_Rule_Condition_Combine');
    }

    //########################################

    protected function getConditionCombine()
    {
        return $this->getType() . '|amazon|';
    }

    // ---------------------------------------

    protected function getCustomLabel()
    {
        return Mage::helper('M2ePro')->__('Amazon Values');
    }

    protected function getCustomOptions()
    {
        $attributes = $this->getCustomOptionsAttributes();
        return !empty($attributes) ?
               $this->getOptions('M2ePro/Amazon_Magento_Product_Rule_Condition_Product', $attributes, array('amazon'))
               : array();
    }

    protected function getCustomOptionsAttributes()
    {
        return array(
            'amazon_sku' => Mage::helper('M2ePro')->__('SKU'),
            'amazon_general_id' => Mage::helper('M2ePro')->__('ASIN/ISBN Value'),
            'amazon_general_id_state' => Mage::helper('M2ePro')->__('ASIN/ISBN Status'),
            'amazon_online_qty' => Mage::helper('M2ePro')->__('QTY'),
            'amazon_online_price' => Mage::helper('M2ePro')->__('Price'),
            'amazon_online_sale_price' => Mage::helper('M2ePro')->__('Sale Price'),
            'amazon_is_afn_chanel' => Mage::helper('M2ePro')->__('Fulfillment'),
            'amazon_status' => Mage::helper('M2ePro')->__('Status')
        );
    }

    //########################################
}