<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Revise_Response
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
            // M2ePro_TRANSLATIONS
            // QTY
            $sequenceString .= 'QTY,';
        }

        if ($this->getConfigurator()->isPriceAllowed()) {
            // M2ePro_TRANSLATIONS
            // Price
            $sequenceString .= 'Price,';
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
}