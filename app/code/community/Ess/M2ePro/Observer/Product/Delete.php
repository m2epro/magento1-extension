<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Product_Delete extends Ess_M2ePro_Observer_Product_Abstract
{
    //########################################

    public function process()
    {
        $productId = $this->getProductId();
        if (empty($productId)) {
            return;
        }

        Mage::getModel('M2ePro/Listing')->removeDeletedProduct($this->getProduct());
        Mage::getModel('M2ePro/Listing_Other')->unmapDeletedProduct($this->getProduct());
        Mage::getModel('M2ePro/Item')->removeDeletedProduct($this->getProduct());
    }

    //########################################
}
