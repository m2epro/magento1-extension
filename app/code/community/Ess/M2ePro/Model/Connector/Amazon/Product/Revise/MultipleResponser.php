<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Revise_Response getResponseObject($listingProduct)
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_Revise_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Product_Responser
{
    // ########################################

    protected function getSuccessfulMessage(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        return $this->getResponseObject($listingProduct)->getSuccessfulMessage();
    }

    // ########################################
}