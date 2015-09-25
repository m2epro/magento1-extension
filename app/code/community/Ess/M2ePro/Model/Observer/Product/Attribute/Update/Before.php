<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Product_Attribute_Update_Before extends Ess_M2ePro_Model_Observer_Abstract
{
    //####################################

    public function process()
    {
        $changedProductsIds = $this->getEventObserver()->getData('product_ids');
        $attributesData = $this->getEventObserver()->getData('attributes_data');
        $storeId = $this->getEventObserver()->getData('store_id');

        if (empty($changedProductsIds) || empty($attributesData)) {
            return;
        }

        /** @var Ess_M2ePro_Model_PublicServices_Product_SqlChange $changesModel */
        $changesModel = Mage::getModel('M2ePro/PublicServices_Product_SqlChange');

        foreach ($changedProductsIds as $productId) {
            foreach ($attributesData as $attributeName => $attributeValue) {

                $changesModel->markProductAttributeChanged($productId, $attributeName, $storeId,
                                                           Mage::helper('M2ePro')->__('Unknown'), $attributeValue);
            }
        }

        $changesModel->applyChanges();
    }

    //####################################
}