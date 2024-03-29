<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Magento_Product_Rule_Custom_EbayEndDate
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'ebay_end_date';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('End Date');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        $endDate = $product->getData('end_date');
        if (empty($endDate)) {
            return null;
        }

        /** @var Ess_M2ePro_Helper_Data $helper */
        $helper = Mage::helper('M2ePro');
        $endDate = $helper->createGmtDateTime($endDate);

        return (int)$helper->createGmtDateTime(
            $endDate->format('Y-m-d')
        )->format('U');
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
