<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Stop_Response
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Response
{
    //########################################

    public function processSuccess($params = array())
    {
        $data = array(
            'synch_status'  => Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_OK,
            'synch_reasons' => NULL,
        );

        $data = $this->appendStatusChangerValue($data);

        $data = $this->appendConditionValues($data);

        $data = $this->appendQtyValues($data);
        $data = $this->appendPriceValues($data);

        $data = $this->appendShippingValues($data);

        $this->getListingProduct()->addData($data);

        $this->setLastSynchronizationDates();

        $this->getListingProduct()->save();
    }

    //########################################

    protected function setLastSynchronizationDates()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['last_synchronization_dates']['qty'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        $this->getListingProduct()->setSettings('additional_data', $additionalData);
    }

    //########################################
}