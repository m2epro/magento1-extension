<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Other
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract
{
    //########################################

    public function getData()
    {
        $data = array_merge(
            $this->getConditionData(),
            $this->getConditionNoteData(),
            $this->getVatTaxData(),
            $this->getBestOfferData(),
            $this->getCharityData()
        );

        return $data;
    }

    //########################################

    /**
     * @return array
     */
    private function getConditionData()
    {
        $this->searchNotFoundAttributes();
        $data = $this->getEbayListingProduct()->getDescriptionTemplateSource()->getCondition();

        if (!$this->processNotFoundAttributes('Condition')) {
            return array();
        }

        return array(
            'item_condition' => $data
        );
    }

    /**
     * @return array
     */
    private function getConditionNoteData()
    {
        $this->searchNotFoundAttributes();
        $data = $this->getEbayListingProduct()->getDescriptionTemplateSource()->getConditionNote();
        $this->processNotFoundAttributes('Seller Notes');

        return array(
            'item_condition_note' => $data
        );
    }

    /**
     * @return array
     */
    private function getVatTaxData()
    {
        $data = array(
            'tax_category' => $this->getEbayListingProduct()->getSellingFormatTemplateSource()->getTaxCategory()
        );

        if ($this->getEbayMarketplace()->isVatEnabled()) {
            $data['vat_percent'] = $this->getEbayListingProduct()->getEbaySellingFormatTemplate()->getVatPercent();
        }

        if ($this->getEbayMarketplace()->isTaxTableEnabled()) {
            $data['use_tax_table'] = $this->getEbayListingProduct()
                ->getEbaySellingFormatTemplate()
                ->isTaxTableEnabled();
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getBestOfferData()
    {
        $data = array(
            'bestoffer_mode' => $this->getEbayListingProduct()->getEbaySellingFormatTemplate()->isBestOfferEnabled(),
        );

        if ($data['bestoffer_mode']) {
            $data['bestoffer_accept_price'] = $this->getEbayListingProduct()->getBestOfferAcceptPrice();
            $data['bestoffer_reject_price'] = $this->getEbayListingProduct()->getBestOfferRejectPrice();
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getCharityData()
    {
        $charity = $this->getEbayListingProduct()->getEbaySellingFormatTemplate()->getCharity();

        if (is_null($charity)) {
            return array();
        }

        return array(
            'charity_id'      => $charity['id'],
            'charity_percent' => $charity['percentage']
        );
    }

    //########################################
}