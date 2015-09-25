<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Buy_Product_Stop_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Buy_Product_Responser
{
    // ########################################

    protected function getSuccessfulMessage(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // M2ePro_TRANSLATIONS
        // Item was successfully Stopped
        return 'Item was successfully Stopped';
    }

    // ########################################

    public function eventAfterExecuting()
    {
        parent::eventAfterExecuting();

        if (empty($this->params['params']['remove'])) {
            return;
        }

        foreach ($this->listingsProducts as $listingProduct) {
            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct->addData(array('status'=>Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED))->save();
            $listingProduct->deleteInstance();
        }
    }

    protected function inspectProducts()
    {
        if (empty($this->params['params']['remove'])) {
            parent::inspectProducts();
        }
    }

    // ########################################
}