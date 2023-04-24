<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Qty as QtyBuilder;

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

        if ($this->getConfigurator()->isDetailsAllowed() ||
            $this->getConfigurator()->isImagesAllowed()
        ) {
            $data['defected_messages'] = null;
        }

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendQtyValues($data);
        $data = $this->appendRegularPriceValues($data);
        $data = $this->appendBusinessPriceValues($data);
        $data = $this->appendGiftSettingsStatus($data);
        $data = $this->appendDetailsValues($data);
        $data = $this->appendImagesValues($data);
        $data = $this->appendIsStoppedManually($data, false);

        if (isset($data['additional_data'])) {
            $data['additional_data'] = Mage::helper('M2ePro')->jsonEncode($data['additional_data']);
        }

        $this->getListingProduct()->addData($data);

        $this->setLastSynchronizationDates();

        $this->getListingProduct()->save();
    }

    //########################################

    protected function appendQtyValues($data)
    {
        $params = $this->getParams();

        if (!empty($params['switch_to']) && $params['switch_to'] === QtyBuilder::FULFILLMENT_MODE_AFN) {
            $data['is_afn_channel'] = Ess_M2ePro_Model_Amazon_Listing_Product::IS_AFN_CHANNEL_YES;
            $data['online_qty'] = null;
            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;

            return $data;
        }

        if (!empty($params['switch_to']) && $params['switch_to'] === QtyBuilder::FULFILLMENT_MODE_MFN) {
            $data['is_afn_channel'] = Ess_M2ePro_Model_Amazon_Listing_Product::IS_AFN_CHANNEL_NO;
            $data['online_afn_qty'] = null;
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
