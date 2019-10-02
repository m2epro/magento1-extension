<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Product_ScheduledAction_Manager
{
    //########################################

    public function addAction(Ess_M2ePro_Model_Listing_Product_ScheduledAction $scheduledAction)
    {
        $scheduledAction->save();
    }

    public function updateAction(Ess_M2ePro_Model_Listing_Product_ScheduledAction $scheduledAction)
    {
        $scheduledActionCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActionCollection->addFieldToFilter('listing_product_id', $scheduledAction->getListingProductId());

        /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction $existedScheduledAction */
        $existedScheduledAction = $scheduledActionCollection->getFirstItem();
        $existedScheduledAction->addData($scheduledAction->getData());
        $existedScheduledAction->save();
    }

    public function deleteAction(Ess_M2ePro_Model_Listing_Product_ScheduledAction $scheduledAction)
    {
        if (!$scheduledAction->getId()) {
            return;
        }

        $scheduledAction->deleteInstance();
    }

    //########################################
}
