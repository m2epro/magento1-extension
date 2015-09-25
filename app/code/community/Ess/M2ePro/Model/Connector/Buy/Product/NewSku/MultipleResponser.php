<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Buy_Product_NewSku_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Buy_Product_Responser
{
    // ########################################

    public function eventAfterExecuting()
    {
        parent::eventAfterExecuting();

        if ($this->isResponseFailed) {
            return;
        }

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Product_Dispatcher');
        $dispatcherObject->process(
            Ess_M2ePro_Model_Listing_Product::ACTION_LIST,
            $this->successfulListingProducts,
            $this->params['params']
        );
    }

    // ########################################

    protected function processResponseData($response)
    {
        foreach ($this->listingsProducts as $listingProduct) {
            if (isset($response['skus'][$listingProduct->getId().'-id'])) {
                continue;
            }

            $this->getLogger()->logListingProductMessage(
                $listingProduct,
                // M2ePro_TRANSLATIONS
                // New SKU was not added
                'New SKU was not added',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            unset($this->listingsProducts[$listingProduct->getId()]);
        }

        parent::processResponseData($response);
    }

    //----------------------------------------

    protected function getSuccessfulParams(Ess_M2ePro_Model_Listing_Product $listingProduct, $response)
    {
        foreach ($response['skus'] as $key => $generalId) {
            if ((int)$key != (int)$listingProduct->getId()) {
                continue;
            }

            return array('general_id' => $generalId);
        }

        return array();
    }

    //----------------------------------------

    protected function getSuccessfulMessage(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // M2ePro_TRANSLATIONS
        // New SKU was successfully added
        return 'New SKU was successfully added';
    }

    // ########################################
}