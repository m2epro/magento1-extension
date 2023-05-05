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
            $this->getCharityData(),
            $this->getLotSizeData()
        );

        return $data;
    }

    //########################################

    /**
     * @return array
     */
    protected function getConditionData()
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
    protected function getConditionNoteData()
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
    protected function getVatTaxData()
    {
        $data = array(
            'tax_category' => $this->getEbayListingProduct()->getSellingFormatTemplateSource()->getTaxCategory()
        );

        if ($this->getEbayMarketplace()->isVatEnabled()) {
            $data['vat_mode'] = (int)$this->getEbayListingProduct()->getEbaySellingFormatTemplate()->isVatModeEnabled();
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
    protected function getCharityData()
    {
        $charity = $this->getEbayListingProduct()->getEbaySellingFormatTemplate()->getCharity();

        if (empty($charity[$this->getMarketplace()->getId()])) {
            return array();
        }

        return array(
            'charity_id' => $charity[$this->getMarketplace()->getId()]['organization_id'],
            'charity_percent' => $charity[$this->getMarketplace()->getId()]['percentage']
        );
    }

    /**
     * @return array
     */
    public function getLotSizeData()
    {
        $categoryFeatures = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getFeatures(
            $this->getEbayListingProduct()->getCategoryTemplateSource()->getCategoryId(),
            $this->getMarketplace()->getId()
        );

        /**
         * lsd - "Lot Size Disabled". If lsd = 1, then this feature does not work for this category.
         */
        if (!isset($categoryFeatures['lsd']) || $categoryFeatures['lsd'] == 1) {
            return array();
        }

        return array(
            'lot_size' => $this->getEbayListingProduct()->getSellingFormatTemplateSource()->getLotSize()
        );
    }

    //########################################
}
