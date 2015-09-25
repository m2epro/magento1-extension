<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Website
    extends Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Abstract
{
    //####################################

    public function synchWithAddedWebsiteId($websiteId)
    {
        if ($websiteId == 0) {
            $storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID);
        } else {
            /** @var $websiteObject Mage_Core_Model_Website */
            $websiteObject = Mage::getModel('core/website')->load((string)$websiteId);
            $storeIds = (array)$websiteObject->getStoreIds();
        }

        if (count($storeIds) <= 0) {
            return;
        }

        /** @var Mage_Core_Model_Mysql4_Collection_Abstract $collection */
        $collection = Mage::getModel('M2ePro/Listing')->getCollection();

        $collection->addFieldToFilter('auto_mode',Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE);
        $collection->addFieldToFilter('auto_website_adding_mode',
                                      array('neq'=>Ess_M2ePro_Model_Listing::ADDING_MODE_NONE));
        $collection->addFieldToFilter('store_id',array('in'=>$storeIds));

        foreach ($collection->getItems() as $listing) {

            /** @var Ess_M2ePro_Model_Listing $listing */

            $this->getListingObject($listing)->addProductByWebsiteListing($this->getProduct(), $listing);
        }
    }

    public function synchWithDeletedWebsiteId($websiteId)
    {
        /** @var $websiteObject Mage_Core_Model_Website */
        $websiteObject = Mage::getModel('core/website')->load((string)$websiteId);
        $storeIds = (array)$websiteObject->getStoreIds();

        if (count($storeIds) <= 0) {
            return;
        }

        /** @var Mage_Core_Model_Mysql4_Collection_Abstract $collection */
        $collection = Mage::getModel('M2ePro/Listing')->getCollection();

        $collection->addFieldToFilter('auto_mode',Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE);
        $collection->addFieldToFilter('auto_website_deleting_mode',
                                      array('neq'=>Ess_M2ePro_Model_Listing::DELETING_MODE_NONE));

        $collection->addFieldToFilter('store_id',array('in'=>$storeIds));

        foreach ($collection->getItems() as $listing) {

            /** @var Ess_M2ePro_Model_Listing $listing */

            $this->getListingObject($listing)->deleteProduct(
                $this->getProduct(),
                $listing->getAutoWebsiteDeletingMode()
            );
        }
    }

    //####################################
}