<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Magento_Product_Rule_Custom_WalmartStartDate
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'walmart_start_date';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Start Date');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        $date = $product->getData('online_start_date');
        if (empty($date)) {
            return null;
        }

        /** @var Ess_M2ePro_Helper_Data $helper */
        $helper = Mage::helper('M2ePro');
        $date = $helper->createGmtDateTime($date);

        return (int)$helper->createGmtDateTime($date->format('Y-m-d'))->format('U');
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'date';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'date';
    }

    //########################################
}
