<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Request
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Request
{
    // ########################################

    protected function getActionData()
    {
        $data = array(
            'sku'        => $this->validatorsData['sku'],
            'product_id' => $this->validatorsData['general_id'],
        );

        if (!empty($this->validatorsData['general_id_mode'])) {
            $data['product_id_type'] = $this->validatorsData['general_id_mode'] - 1;
        }

        $data = array_merge(
            $data,
            $this->getRequestDetails()->getData(),
            $this->getRequestSelling()->getData(),
            $this->getRequestShipping()->getData()
        );

        return $data;
    }

    // ########################################
}