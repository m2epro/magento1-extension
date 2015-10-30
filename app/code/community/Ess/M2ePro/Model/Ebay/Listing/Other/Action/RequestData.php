<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_RequestData extends Ess_M2ePro_Model_Ebay_Listing_Action_RequestData
{
    /**
     * @var Ess_M2ePro_Model_Listing_Other
     */
    private $listingOther = NULL;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Other $object
     */
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

    //########################################
}