<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Item_Verify_Responser
    extends Ess_M2ePro_Model_Ebay_Connector_Item_Responser
{
    //########################################

    protected function getSuccessfulMessage()
    {
        return NULL;
    }

    //########################################

    protected function processResponseMessages()
    {
        $this->getLogger()->setStoreMode(true);
        parent::processResponseMessages();
    }

    protected function prepareResponseData()
    {
        $responseData = $this->getResponse()->getData();

        if (isset($responseData['ebay_item_fees']) && is_array($responseData['ebay_item_fees'])) {
            $this->preparedResponseData = $responseData['ebay_item_fees'];
        }
    }

    protected function processResponseData() {}

    //########################################
}