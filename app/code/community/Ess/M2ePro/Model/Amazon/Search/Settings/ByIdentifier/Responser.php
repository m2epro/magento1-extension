<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Settings_ByIdentifier_Responser
    extends Ess_M2ePro_Model_Connector_Amazon_Search_ByIdentifier_ItemsResponser
{
    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->getObjectByParam('Listing_Product', 'listing_product_id');
    }

    // ########################################

    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        $this->getListingProduct()->deleteObjectLocks(NULL, $processingRequest->getHash());
        $this->getListingProduct()->getListing()->deleteObjectLocks(NULL, $processingRequest->getHash());
    }

    public function eventFailedExecuting($message)
    {
        parent::eventFailedExecuting($message);

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

        $logModel->addProductMessage(
            $this->getListingProduct()->getListingId(),
            $this->getListingProduct()->getProductId(),
            $this->getListingProduct()->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
            NULL,
            Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN,
            $message,
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );

        $this->getListingProduct()->setData('search_settings_status', null);
        $this->getListingProduct()->setData('search_settings_data', null);
        $this->getListingProduct()->save();
    }

    // ########################################

    protected function processResponseData($response)
    {
        /** @var Ess_M2ePro_Model_Amazon_Search_Settings $settingsSearch */
        $settingsSearch = Mage::getModel('M2ePro/Amazon_Search_Settings');
        $settingsSearch->setListingProduct($this->getListingProduct());
        $settingsSearch->setStep($this->params['step']);
        if (!empty($response)) {
            $settingsSearch->setStepData(array(
                'params' => $this->params,
                'result' => $response,
            ));
        }

        $settingsSearch->process();
    }

    // ########################################
}