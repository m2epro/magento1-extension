<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Stop_Request
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Request
{
    //########################################

    protected function getActionData()
    {
        $data = array_merge(
            array(
                'sku'  => $this->getWalmartListingProduct()->getSku(),
                'wpid' => $this->getWalmartListingProduct()->getWpid(),
            ),
            $this->getQtyData()
        );

        $data['qty'] = 0;

        return $data;
    }

    //########################################
}
