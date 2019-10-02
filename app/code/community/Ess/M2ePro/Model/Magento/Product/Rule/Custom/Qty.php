<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_Rule_Custom_Qty
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'qty';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('QTY');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return Mage::getModel('cataloginventory/stock_item')
            ->setProductId($product->getId())
            ->setStockId(Mage::helper('M2ePro/Magento_Store')->getStockId($product->getStoreId()))
            ->loadByProduct($product)
            ->getQty();
    }

    //########################################
}
