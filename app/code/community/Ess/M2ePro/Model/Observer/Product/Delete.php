<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Product_Delete extends Ess_M2ePro_Model_Observer_Product_Abstract
{
    //########################################

    public function process()
    {
        if ($this->getProductId() <= 0) {
            return;
        }

        Mage::getModel('M2ePro/Listing')->removeDeletedProduct($this->getProduct());
        Mage::getModel('M2ePro/Listing_Other')->unmapDeletedProduct($this->getProduct());
        Mage::getModel('M2ePro/Item')->removeDeletedProduct($this->getProduct());
        Mage::getModel('M2ePro/ProductChange')->removeDeletedProduct($this->getProduct());
    }

    //########################################
}