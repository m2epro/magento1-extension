<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Listing_Product_Delete_Before extends Ess_M2ePro_Observer_Abstract
{
    //########################################

    public function process()
    {
        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = $this->getEvent()->getData('object');

        /** @var Ess_M2ePro_Model_Listing_Product_Indexer_VariationParent_Manager $manager */
        $manager = Mage::getModel(
            'M2ePro/Listing_Product_Indexer_VariationParent_Manager',
            array($listingProduct->getListing())
        );
        $manager->markInvalidated();
    }

    //########################################
}
