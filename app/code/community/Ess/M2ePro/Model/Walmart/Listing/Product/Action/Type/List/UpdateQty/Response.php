<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_List_UpdateQty_Response
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Response
{
    //########################################

    /**
     * @param array $params
     */
    public function processSuccess($params = array())
    {
        $data = array();

        $data = $this->appendQtyValues($data);
        $this->getListingProduct()->addData($data);

        $this->setLastSynchronizationDates();
        $this->getListingProduct()->save();
    }

    //########################################
}