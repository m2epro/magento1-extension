<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Revise_Response
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response
{
    //########################################

    /**
     * @param array $params
     */
    public function processSuccess($params = array())
    {
        $data = array();

        if ($this->getConfigurator()->isDetailsAllowed()) {
            $data['defected_messages'] = null;
            $data['is_details_data_changed'] = 0;
        }

        if ($this->getConfigurator()->isImagesAllowed()) {
            $data['defected_messages'] = null;
            $data['is_images_data_changed'] = 0;
        }

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendQtyValues($data);
        $data = $this->appendRegularPriceValues($data);
        $data = $this->appendBusinessPriceValues($data);
        $data = $this->appendGiftSettingsStatus($data);
        $data = $this->appendDetailsValues($data);
        $data = $this->appendImagesValues($data);

        if (isset($data['additional_data'])) {
            $data['additional_data'] = Mage::helper('M2ePro')->jsonEncode($data['additional_data']);
        }

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
            $params = $this->getParams();

            if (!empty($params['switch_to']) &&
                $params['switch_to'] ===
                Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Qty::FULFILLMENT_MODE_AFN) {
                // M2ePro_TRANSLATIONS
                // Item was successfully switched to AFN
                return 'Item was successfully switched to AFN';
            }

            if (!empty($params['switch_to']) &&
                $params['switch_to'] ===
                Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Qty::FULFILLMENT_MODE_MFN) {
                // M2ePro_TRANSLATIONS
                // Item was successfully switched to MFN
                return 'Item was successfully switched to MFN';
            }

            // M2ePro_TRANSLATIONS
            // QTY
            $sequenceStrings[] = 'QTY';
        }

        if ($this->getConfigurator()->isRegularPriceAllowed()) {
            // M2ePro_TRANSLATIONS
            // Price
            $sequenceStrings[] = 'Price';
        }

        if ($this->getConfigurator()->isBusinessPriceAllowed()) {
            // M2ePro_TRANSLATIONS
            // Business Price
            $sequenceStrings[] = 'Business Price';
        }

        if ($this->getConfigurator()->isDetailsAllowed()) {
            // M2ePro_TRANSLATIONS
            // details
            $sequenceStrings[] = 'Details';
            $isPlural = true;
        }

        if ($this->getConfigurator()->isImagesAllowed()) {
            // M2ePro_TRANSLATIONS
            // images
            $sequenceStrings[] = 'Images';
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

    protected function appendQtyValues($data)
    {
        $params = $this->getParams();

        if (!empty($params['switch_to']) &&
            $params['switch_to']
                === Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Qty::FULFILLMENT_MODE_AFN) {
            $data['is_afn_channel'] = Ess_M2ePro_Model_Amazon_Listing_Product::IS_AFN_CHANNEL_YES;
            $data['online_qty'] = null;
            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;

            return $data;
        }

        if (!empty($params['switch_to']) &&
            $params['switch_to']
                === Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Qty::FULFILLMENT_MODE_MFN) {
            $data['is_afn_channel'] = Ess_M2ePro_Model_Amazon_Listing_Product::IS_AFN_CHANNEL_NO;
        }

        return parent::appendQtyValues($data);
    }

    // ---------------------------------------

    protected function setLastSynchronizationDates()
    {
        parent::setLastSynchronizationDates();

        $params = $this->getParams();
        if (!isset($params['switch_to'])) {
            return;
        }

        $additionalData = $this->getListingProduct()->getAdditionalData();

        $additionalData['last_synchronization_dates']['fulfillment_switching']
                = Mage::helper('M2ePro')->getCurrentGmtDate();

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
    }

    //########################################
}
