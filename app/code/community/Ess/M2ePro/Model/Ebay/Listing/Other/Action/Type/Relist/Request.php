<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Relist_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Request
{
    //########################################

    /**
     * @return array
     */
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

    //########################################
}