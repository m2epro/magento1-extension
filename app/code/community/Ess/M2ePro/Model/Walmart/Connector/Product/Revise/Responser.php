<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Revise_Response getResponseObject()
 */

class Ess_M2ePro_Model_Walmart_Connector_Product_Revise_Responser
    extends Ess_M2ePro_Model_Walmart_Connector_Product_Responser
{
    //########################################

    protected function getSuccessfulMessage()
    {
        return $this->getResponseObject()->getSuccessfulMessage();
    }

    public function eventAfterExecuting()
    {
        parent::eventAfterExecuting();

        if ($this->_isSuccess) {
            return;
        }

        $additionalData = $this->_listingProduct->getAdditionalData();
        $additionalData['need_full_synchronization_template_recheck'] = true;
        $this->_listingProduct->setSettings('additional_data', $additionalData)->save();
    }

    //########################################
}
