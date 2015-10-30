<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Magento_Product_Rule_Condition_Combine
    extends Ess_M2ePro_Model_Magento_Product_Rule_Condition_Combine
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setType('M2ePro/Buy_Magento_Product_Rule_Condition_Combine');
    }

    //########################################

    protected function getConditionCombine()
    {
        return $this->getType() . '|buy|';
    }

    // ---------------------------------------

    protected function getCustomLabel()
    {
        return Mage::helper('M2ePro')->__('Rakuten.com Values');
    }

    protected function getCustomOptions()
    {
        $attributes = $this->getCustomOptionsAttributes();
        return !empty($attributes) ?
            $this->getOptions('M2ePro/Buy_Magento_Product_Rule_Condition_Product', $attributes, array('buy'))
            : array();
    }

    protected function getCustomOptionsAttributes()
    {
        return array(
            'buy_sku' => Mage::helper('M2ePro')->__('Reference ID'),
            'buy_general_id' => Mage::helper('M2ePro')->__('SKU'),
            'buy_online_qty' => Mage::helper('M2ePro')->__('QTY'),
            'buy_online_price' => Mage::helper('M2ePro')->__('Price'),
            'buy_status' => Mage::helper('M2ePro')->__('Status')
        );
    }

    //########################################
}