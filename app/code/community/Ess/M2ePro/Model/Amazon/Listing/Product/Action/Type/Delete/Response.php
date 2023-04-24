<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Delete_Response
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response
{
    //########################################

    /**
     * @param array $params
     */
    public function processSuccess($params = array())
    {
        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
            'general_id' => null,
            'is_general_id_owner' => Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO,
            'template_description_id' => null,
            'online_qty' => 0,
        );

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendIsStoppedManually($data, false);

        $this->getListingProduct()->addData($data);
        $this->getListingProduct()->save();
    }

    //########################################
}
