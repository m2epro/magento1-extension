<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Relist_Response
    extends Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Response
{
    // ########################################

    public function processSuccess(array $response, array $responseParams = array())
    {
        if ((int)$this->getListingOther()->getProductId() > 0) {
            $this->createEbayItem($response['ebay_item_id']);
        }

        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
            'item_id' => $response['ebay_item_id']
        );

        $data = $this->appendOldItems($data);

        $data = $this->appendStatusChangerValue($data, $responseParams);

        $data = $this->appendOnlineQtyValues($data);
        $data = $this->appendOnlinePriceValue($data);
        $data = $this->appendTitleValue($data);

        $data = $this->appendStartDateEndDateValues($data, $response);

        if (isset($data['additional_data'])) {
            $data['additional_data'] = json_encode($data['additional_data']);
        }

        $this->getListingOther()->addData($data)->save();
    }

    public function processAlreadyActive(array $response, array $responseParams = array())
    {
        $responseParams['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;
        $this->processSuccess($response,$responseParams);
    }

    // ########################################

    public function markAsPotentialDuplicate()
    {
        $additionalData = $this->getListingOther()->getAdditionalData();

        $additionalData['last_failed_action_data'] = array(
            'native_request_data' => $this->getRequestData()->getData(),
            'previous_status' => $this->getListingOther()->getStatus(),
            'action' => Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
            'request_time' => Mage::helper('M2ePro')->getCurrentGmtDate(),
        );

        $this->getListingOther()->addData(array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
            'additional_data' => json_encode($additionalData),
        ))->save();
    }

    // ########################################

    private function appendOldItems($data)
    {
        $newEbayOldItems = $this->getListingOther()->getData('old_items');
        is_null($newEbayOldItems) && $newEbayOldItems = '';
        $newEbayOldItems != '' && $newEbayOldItems .= ',';
        $newEbayOldItems .= $this->getListingOther()->getData('item_id');

        $data['old_items'] = $newEbayOldItems;

        return $data;
    }

    // ########################################
}