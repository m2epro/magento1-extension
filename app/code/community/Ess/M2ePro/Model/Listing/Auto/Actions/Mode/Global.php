<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Global
    extends Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Abstract
{
    //########################################

    public function synch()
    {
        /** @var Mage_Core_Model_Resource_Db_Collection_Abstract $collection */
        $collection = Mage::getModel('M2ePro/Listing')->getCollection();

        $collection->addFieldToFilter('auto_mode', Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL);
        $collection->addFieldToFilter(
            'auto_global_adding_mode',
            array('neq'=>Ess_M2ePro_Model_Listing::ADDING_MODE_NONE)
        );

        foreach ($collection->getItems() as $listing) {

            /** @var Ess_M2ePro_Model_Listing $listing */

            if (!$listing->isAutoGlobalAddingAddNotVisibleYes()) {
                if ($this->getProduct()->getVisibility()
                    == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
                    continue;
                }
            }

            if ($this->existsDuplicateListingProduct($listing)) {
                continue;
            }

            $this->getListingObject($listing)->addProductByGlobalListing($this->getProduct(), $listing);
        }
    }

    //########################################
}
