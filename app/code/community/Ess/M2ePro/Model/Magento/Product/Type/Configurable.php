<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * Due to strange changes in addStoreFilter method since Magento version 1.9.x,
 * we were forced to setStore for collection manually
 */

class Ess_M2ePro_Model_Magento_Product_Type_Configurable extends Mage_Catalog_Model_Product_Type_Configurable
{
    //########################################

    /**
     * Retrieve related products collection
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Type_Configurable_Product_Collection
     */
    public function getUsedProductCollection($product = null)
    {
        $collection = parent::getUsedProductCollection($product);

        if (!is_null($this->getStoreFilter($product))) {
            $collection->setStoreId($this->getStoreFilter($product));
        }

        return $collection;
    }

    //########################################
}
