<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Stop_Response
    extends Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Response
{
    //########################################

    public function processSuccess(array $response, array $responseParams = array())
    {
        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED
        );

        $data = $this->appendStatusChangerValue($data, $responseParams);
        $data = $this->appendStartDateEndDateValues($data, $response);

        if (isset($data['additional_data'])) {
            $data['additional_data'] = json_encode($data['additional_data']);
        }

        $this->getListingOther()->addData($data)->save();
    }

    public function processAlreadyStopped(array $response, array $responseParams = array())
    {
        $responseParams['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;
        $this->processSuccess($response,$responseParams);
    }

    //########################################
}