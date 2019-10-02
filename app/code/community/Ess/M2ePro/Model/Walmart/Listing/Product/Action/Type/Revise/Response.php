<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Revise_Request as ReviseRequest;

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Revise_Response
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Response
{
    //########################################

    /**
     * @param array $params
     */
    public function processSuccess($params = array())
    {
        $data = array();

        if ($this->getRequestData()->getIsNeedProductIdUpdate()) {
            $data['wpid'] = $params['wpid'];
        }

        if ($this->getConfigurator()->isPriceAllowed()) {
            $data['is_online_price_invalid'] = 0;
        }

        if ($this->getConfigurator()->isDetailsAllowed()) {
            $data['is_details_data_changed'] = 0;
        }

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendQtyValues($data);
        $data = $this->appendPriceValues($data);
        $data = $this->appendPromotionsValues($data);
        $data = $this->appendDetailsValues($data);
        $data = $this->appendStartDate($data);
        $data = $this->appendEndDate($data);
        $data = $this->appendChangedSku($data);
        $data = $this->appendProductIdsData($data);

        $this->getListingProduct()->addData($data);

        $this->setLastSynchronizationDates();

        $this->getListingProduct()->save();
    }

    //########################################

    /**
     * @return string
     */
    public function getSuccessfulMessage()
    {
        if ($this->getConfigurator()->isExcludingMode()) {
            // M2ePro_TRANSLATIONS
            // Item was successfully Revised
            return 'Item was successfully Revised';
        }

        $sequenceStrings = array();
        $isPlural = false;

        if ($this->getConfigurator()->isQtyAllowed()) {
            // M2ePro_TRANSLATIONS
            // QTY
            $sequenceStrings[] = 'QTY';
        }

        if ($this->getConfigurator()->isPriceAllowed()) {
            // M2ePro_TRANSLATIONS
            // Price
            $sequenceStrings[] = 'Price';
        }

        if ($this->getConfigurator()->isPromotionsAllowed()) {
            // M2ePro_TRANSLATIONS
            // Promotions
            $sequenceStrings[] = 'Promotions';
        }

        if ($this->getConfigurator()->isDetailsAllowed()) {
            if ($this->getRequestData()->getIsNeedSkuUpdate()) {
                // M2ePro_TRANSLATIONS
                // SKU
                $sequenceStrings[] = 'SKU';
            }

            if ($this->getRequestData()->getIsNeedProductIdUpdate()) {
                $idsMetadata = $this->getRequestMetaData(ReviseRequest::PRODUCT_ID_UPDATE_METADATA_KEY);
                !empty($idsMetadata) && $sequenceStrings[] = strtoupper($idsMetadata['type']);
            }

            // M2ePro_TRANSLATIONS
            // Details
            $sequenceStrings[] = 'Details';
            $isPlural = true;
        }

        if (empty($sequenceStrings)) {
            // M2ePro_TRANSLATIONS
            // Item was successfully Revised
            return 'Item was successfully Revised';
        }

        if (count($sequenceStrings) == 1) {
            $verb = 'was';
            if ($isPlural) {
                $verb = 'were';
            }

            return ucfirst($sequenceStrings[0]).' '.$verb.' successfully Revised';
        }

        // M2ePro_TRANSLATIONS
        // was successfully Revised
        return ucfirst(implode(', ', $sequenceStrings)).' were successfully Revised';
    }

    //########################################
}
