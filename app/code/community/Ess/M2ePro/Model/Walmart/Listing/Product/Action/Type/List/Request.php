<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_List_Request
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Request
{
    const LIST_TYPE_EXIST = 'exist';
    const LIST_TYPE_NEW   = 'new';

    const PARENTAGE_PARENT = 'parent';
    const PARENTAGE_CHILD  = 'child';

    //########################################

    protected function getActionData()
    {
        $params = $this->getParams();

        $data = array(
            'sku' => $params['sku'],
        );

        $data = array_merge(
            $data,
            $this->getPriceData(),
            $this->getDetailsData()
        );

        return $data;
    }

    //########################################
}