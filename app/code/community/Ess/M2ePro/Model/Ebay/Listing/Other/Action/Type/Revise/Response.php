<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Revise_Response
    extends Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Response
{
    //########################################

    public function processSuccess(array $response, array $responseParams = array())
    {
        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED
        );

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

    public function processAlreadyStopped(array $response, array $responseParams = array())
    {
        $responseParams['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED
        );

        $data = $this->appendStatusChangerValue($data, $responseParams);
        $data = $this->appendStartDateEndDateValues($data, $response);

        $this->getListingOther()->addData($data)->save();
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

        if ($this->getRequestData()->hasQty()) {
            // M2ePro_TRANSLATIONS
            // QTY
            $sequenceString .= 'QTY,';
        }

        if ($this->getRequestData()->hasPrice()) {
            // M2ePro_TRANSLATIONS
            // Price
            $sequenceString .= 'Price,';
        }

        if ($this->getRequestData()->hasTitle()) {
            // M2ePro_TRANSLATIONS
            // Title
            $sequenceString .= 'Title,';
        }

        if ($this->getRequestData()->hasSubtitle()) {
            // M2ePro_TRANSLATIONS
            // Subtitle
            $sequenceString .= 'Subtitle,';
        }

        if ($this->getRequestData()->hasDescription()) {
            // M2ePro_TRANSLATIONS
            // Description
            $sequenceString .= 'Description,';
        }

        if (empty($sequenceString)) {
            // M2ePro_TRANSLATIONS
            // Item was Successfully Revised
            return 'Item was successfully Revised';
        }

        // M2ePro_TRANSLATIONS
        // was Successfully Revised
        return ucfirst(trim($sequenceString,',')).' was successfully Revised';
    }

    //########################################

    protected function appendOnlineQtyValues($data)
    {
        $data = parent::appendOnlineQtyValues($data);

        $data['online_qty_sold'] = (int)$this->getEbayListingOther()->getOnlineQtySold();
        isset($data['online_qty']) && $data['online_qty'] += $data['online_qty_sold'];

        return $data;
    }

    //########################################
}