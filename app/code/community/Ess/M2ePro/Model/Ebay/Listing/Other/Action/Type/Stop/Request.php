<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Stop_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Request
{
    // ########################################

    public function getActionData()
    {
        return array(
            'item_id' => $this->getEbayListingOther()->getItemId()
        );
    }

    // ########################################
}