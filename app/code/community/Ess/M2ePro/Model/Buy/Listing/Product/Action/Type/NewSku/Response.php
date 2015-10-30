<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_NewSku_Response
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Response
{
    //########################################

    public function processSuccess($params = array())
    {
        $data = array(
            'general_id' => $params['general_id'],
        );

        $this->getListingProduct()->addData($data);
        $this->getListingProduct()->save();
    }

    //########################################
}