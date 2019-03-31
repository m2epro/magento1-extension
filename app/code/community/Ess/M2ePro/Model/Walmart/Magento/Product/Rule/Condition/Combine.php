<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Magento_Product_Rule_Condition_Combine
    extends Ess_M2ePro_Model_Magento_Product_Rule_Condition_Combine
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setType('M2ePro/Walmart_Magento_Product_Rule_Condition_Combine');
    }

    //########################################

    protected function getConditionCombine()
    {
        return $this->getType() . '|walmart|';
    }

    // ---------------------------------------

    protected function getCustomLabel()
    {
        return Mage::helper('M2ePro')->__('Walmart Values');
    }

    protected function getCustomOptions()
    {
        $attributes = $this->getCustomOptionsAttributes();
        return !empty($attributes) ?
               $this->getOptions('M2ePro/Walmart_Magento_Product_Rule_Condition_Product', $attributes, array('walmart'))
               : array();
    }

    protected function getCustomOptionsAttributes()
    {
        return array(
            'walmart_sku'                  => Mage::helper('M2ePro')->__('SKU'),
            'walmart_gtin'                 => Mage::helper('M2ePro')->__('GTIN'),
            'walmart_upc'                  => Mage::helper('M2ePro')->__('UPC'),
            'walmart_ean'                  => Mage::helper('M2ePro')->__('EAN'),
            'walmart_isbn'                 => Mage::helper('M2ePro')->__('ISBN'),
            'walmart_wpid'                 => Mage::helper('M2ePro')->__('Walmart ID'),
            'walmart_item_id'              => Mage::helper('M2ePro')->__('Item ID'),
            'walmart_online_qty'           => Mage::helper('M2ePro')->__('QTY'),
            'walmart_online_price'         => Mage::helper('M2ePro')->__('Price'),
            'walmart_start_date'           => Mage::helper('M2ePro')->__('Start Date'),
            'walmart_end_date'             => Mage::helper('M2ePro')->__('End Date'),
            'walmart_status'               => Mage::helper('M2ePro')->__('Status'),
            'walmart_details_data_changed' => Mage::helper('M2ePro')->__('Item Details need to be updated'),
            'walmart_online_price_invalid' => Mage::helper('M2ePro')->__('Pricing Rules violated'),
        );
    }

    //########################################
}