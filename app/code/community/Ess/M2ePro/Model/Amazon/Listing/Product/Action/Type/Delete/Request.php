<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Delete_Request
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Request
{
    // ########################################

    protected function getActionData()
    {
        return array(
            'sku' => $this->getAmazonListingProduct()->getSku()
        );
    }

    // ########################################
}