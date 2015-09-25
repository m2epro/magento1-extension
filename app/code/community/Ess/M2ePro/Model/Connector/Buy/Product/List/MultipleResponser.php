<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Buy_Product_List_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Buy_Product_Responser
{
    // ########################################

    protected function getSuccessfulMessage(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // M2ePro_TRANSLATIONS
        // Item was successfully Listed
        return 'Item was successfully Listed';
    }

    // ########################################

    public function eventAfterProcessing()
    {
        parent::eventAfterProcessing();
        $this->removeSKUsFromQueue();
    }

    private function removeSKUsFromQueue()
    {
        /** @var Ess_M2ePro_Model_LockItem $lockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick('buy_list_skus_queue_' . $this->getAccount()->getId());

        if (!$lockItem->isExist()) {
            return;
        }

        $skusToRemove = array();

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($this->listingsProducts as $listingProduct) {
            $skusToRemove[] = (string)$this->getRequestDataObject($listingProduct)->getSku();
        }

        $resultSkus = array_diff($lockItem->getContentData(), $skusToRemove);

        if (empty($resultSkus)) {
            $lockItem->remove();
            return;
        }

        $lockItem->setContentData($resultSkus);
    }

    // ########################################
}