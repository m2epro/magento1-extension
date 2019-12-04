<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Product_Attribute_Update_Before extends Ess_M2ePro_Observer_Abstract
{
    //########################################

    public function process()
    {
        $changedProductsIds = $this->getEventObserver()->getData('product_ids');
        if (empty($changedProductsIds)) {
            return;
        }

        /** @var Ess_M2ePro_PublicServices_Product_SqlChange $changesModel */
        $changesModel = Mage::getModel('M2ePro_PublicServices/Product_SqlChange');

        foreach ($changedProductsIds as $productId) {
            $changesModel->markProductChanged($productId);
        }

        $changesModel->applyChanges();
    }

    //########################################
}
