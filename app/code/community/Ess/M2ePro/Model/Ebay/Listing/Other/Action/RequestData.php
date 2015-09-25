<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_RequestData extends Ess_M2ePro_Model_Ebay_Listing_Action_RequestData
{
    /**
     * @var Ess_M2ePro_Model_Listing_Other
     */
    private $listingOther = NULL;

    // ########################################

    public function setListingOther(Ess_M2ePro_Model_Listing_Other $object)
    {
        $this->listingOther = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Other
     */
    protected function getListingOther()
    {
        return $this->listingOther;
    }

    // ########################################
}