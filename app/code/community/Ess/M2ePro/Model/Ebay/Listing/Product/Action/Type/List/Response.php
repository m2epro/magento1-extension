<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_List_Response
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
{
    //########################################

    public function processSuccess(array $response, array $responseParams = array())
    {
        $this->prepareMetadata();

        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
            'ebay_item_id' => $this->createEbayItem($response['ebay_item_id'])->getId(),
        );

        $data = $this->appendStatusHiddenValue($data);
        $data = $this->appendStatusChangerValue($data, $responseParams);

        $data = $this->appendOnlineBidsValue($data);
        $data = $this->appendOnlineQtyValues($data);
        $data = $this->appendOnlinePriceValues($data);
        $data = $this->appendOnlineInfoDataValues($data);

        $data = $this->appendItemFeesValues($data, $response);
        $data = $this->appendStartDateEndDateValues($data, $response);
        $data = $this->appendGalleryImagesValues($data, $response);

        $data = $this->appendSpecificsReplacementValues($data);
        $data = $this->appendWithoutVariationMpnIssueFlag($data);
        $data = $this->appendIsVariationMpnFilledValue($data);

        $data = $this->appendIsVariationValue($data);
        $data = $this->appendIsAuctionType($data);

        $data = $this->appendDescriptionValues($data);
        $data = $this->appendImagesValues($data);
        $data = $this->appendCategoriesValues($data);
        $data = $this->appendPartsValues($data);
        $data = $this->appendPaymentValues($data);
        $data = $this->appendShippingValues($data);
        $data = $this->appendReturnValues($data);
        $data = $this->appendOtherValues($data);

        if (isset($data['additional_data'])) {
            $data['additional_data'] = Mage::helper('M2ePro')->jsonEncode($data['additional_data']);
        }

        $this->getListingProduct()->addData($data)->save();

        $this->updateVariationsValues(false);
    }

    //########################################

    protected function appendSpecificsReplacementValues($data)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        $tempKey = 'variations_specifics_replacements';
        unset($data['additional_data'][$tempKey]);

        $requestMetaData = $this->getRequestMetaData();
        if (!isset($requestMetaData[$tempKey])) {
            return $data;
        }

        $data['additional_data'][$tempKey] = $requestMetaData[$tempKey];
        return $data;
    }

    protected function appendWithoutVariationMpnIssueFlag($data)
    {
        $requestData = $this->getRequestData()->getData();
        if (empty($requestData['without_mpn_variation_issue'])) {
            return $data;
        }

        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        $data['additional_data']['without_mpn_variation_issue'] = true;

        return $data;
    }

    //########################################
}
