<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Stop_Response
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response
{
    //########################################

    /**
     * @param array $params
     */
    public function processSuccess($params = array())
    {
        $data = array();

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendQtyValues($data);

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