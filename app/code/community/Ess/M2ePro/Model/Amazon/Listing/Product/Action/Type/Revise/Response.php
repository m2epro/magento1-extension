<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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

        if ($this->getConfigurator()->isAllAllowed()) {
            $data['synch_status'] = Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_OK;
            $data['synch_reasons'] = NULL;
        }

        if ($this->getConfigurator()->isDetailsAllowed() || $this->getConfigurator()->isImagesAllowed()) {
            $data['defected_messages'] = null;
        }

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendQtyValues($data);
        $data = $this->appendPriceValues($data);

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
        if ($this->getConfigurator()->isAllAllowed()) {
            // M2ePro_TRANSLATIONS
            // Item was successfully Revised
            return 'Item was successfully Revised';
        }

        $sequenceString = '';

        if ($this->getConfigurator()->isQtyAllowed()) {

            $params = $this->getParams();

            if (!empty($params['switch_to']) &&
                $params['switch_to'] ===
                    Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Qty::FULFILLMENT_MODE_AFN) {

                // M2ePro_TRANSLATIONS
                // Item was successfully switched to AFN
                return 'Item was successfully switched to AFN';
            }

            if (!empty($params['switch_to']) &&
                $params['switch_to'] ===
                    Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Qty::FULFILLMENT_MODE_MFN) {

                // M2ePro_TRANSLATIONS
                // Item was successfully switched to MFN
                return 'Item was successfully switched to MFN';
            }

            // M2ePro_TRANSLATIONS
            // QTY
            $sequenceString .= 'QTY,';
        }

        if ($this->getConfigurator()->isPriceAllowed()) {
            // M2ePro_TRANSLATIONS
            // Price
            $sequenceString .= 'Price,';
        }

        if ($this->getConfigurator()->isDetailsAllowed()) {
            // M2ePro_TRANSLATIONS
            // details
            $sequenceString .= 'details,';
        }

        if ($this->getConfigurator()->isImagesAllowed()) {
            // M2ePro_TRANSLATIONS
            // images
            $sequenceString .= 'images,';
        }

        if (empty($sequenceString)) {
            // M2ePro_TRANSLATIONS
            // Item was successfully Revised
            return 'Item was successfully Revised';
        }

        // M2ePro_TRANSLATIONS
        // was successfully Revised
        return ucfirst(trim($sequenceString,',')).' was successfully Revised';
    }

    //########################################

    protected function appendQtyValues($data)
    {
        $params = $this->getParams();

        if (!empty($params['switch_to']) &&
            $params['switch_to'] === Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Qty::FULFILLMENT_MODE_AFN) {

            $data['is_afn_channel'] = Ess_M2ePro_Model_Amazon_Listing_Product::IS_AFN_CHANNEL_YES;
            $data['online_qty'] = null;
            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;

            return $data;
        }

        if (!empty($params['switch_to']) &&
            $params['switch_to'] === Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Qty::FULFILLMENT_MODE_MFN) {

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