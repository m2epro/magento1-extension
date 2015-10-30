<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Request
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Request
{
    //########################################

    protected function getActionData()
    {
        $data = array(
            'sku'        => $this->validatorsData['sku'],
            'product_id' => $this->validatorsData['general_id'],
        );

        $data = array_merge(
            $data,
            $this->getRequestDetails()->getData(),
            $this->getRequestSelling()->getData(),
            $this->getRequestShipping()->getData()
        );

        return $data;
    }

    //########################################
}