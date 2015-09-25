<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Relist_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Request
{
    // ########################################

    public function getActionData()
    {
        $data = array(
            'item_id' => $this->getEbayListingOther()->getItemId()
        );

        return array_merge(
            $data,
            $this->getRequestSelling()->getData(),
            $this->getRequestDescription()->getData()
        );
    }

    // ########################################
}