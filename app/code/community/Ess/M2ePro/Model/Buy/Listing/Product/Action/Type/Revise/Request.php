<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Revise_Request
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Request
{
    // ########################################

    protected function getActionData()
    {
        $data = array_merge(
            array(
                'sku' => $this->getBuyListingProduct()->getSku()
            ),
            $this->getRequestDetails()->getData(),
            $this->getRequestSelling()->getData(),
            $this->getRequestShipping()->getData()
        );

        return $data;
    }

    // ########################################
}