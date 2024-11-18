<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Magento_Product_ChangeProcessor
    extends Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract
{
    const INSTRUCTION_TYPE_QTY_DATA_CHANGED       = 'magento_product_qty_data_changed';
    const INSTRUCTION_TYPE_DETAILS_DATA_CHANGED   = 'magento_product_details_data_changed';
    const INSTRUCTION_TYPE_REPRICING_DATA_CHANGED = 'magento_product_repricing_data_changed';

    //########################################

    public function getTrackingAttributes()
    {
        return array_unique(
            array_merge(
                $this->getQtyTrackingAttributes(),
                $this->getDetailsTrackingAttributes(),
                $this->getRepricingTrackingAttributes()
            )
        );
    }

    public function getInstructionsDataByAttributes(array $attributes)
    {
        if (empty($attributes)) {
            return array();
        }

        $data = array();

        if (array_intersect($attributes, $this->getQtyTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 40;
            }

            $data[] = array(
                'type'     => self::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
                'priority' => $priority,
            );
        }

        if (array_intersect($attributes, $this->getDetailsTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = array(
                'type'     => self::INSTRUCTION_TYPE_DETAILS_DATA_CHANGED,
                'priority' => $priority,
            );
        }

        if (array_intersect($attributes, $this->getRepricingTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 70;
            }

            $data[] = array(
                'type'     => self::INSTRUCTION_TYPE_REPRICING_DATA_CHANGED,
                'priority' => $priority,
            );
        }

        return $data;
    }

    //########################################

    public function getQtyTrackingAttributes()
    {
        $amazonListing = $this->getAmazonListingProduct()->getAmazonListing();

        $trackingAttributes = array_merge(
            $amazonListing->getHandlingTimeAttributes(),
            $amazonListing->getRestockDateAttributes()
        );

        return array_unique($trackingAttributes);
    }

    public function getDetailsTrackingAttributes()
    {
        $trackingAttributes = array();

        $amazonListing = $this->getAmazonListingProduct()->getAmazonListing();

        $trackingAttributes = array_merge(
            $trackingAttributes,
            $amazonListing->getConditionNoteAttributes(),
            $amazonListing->getGiftWrapAttributes(),
            $amazonListing->getGiftMessageAttributes()
        );

        if ($this->getAmazonListingProduct()->isExistProductTypeTemplate()) {
            $amazonProductTypeTemplate     = $this->getAmazonListingProduct()->getProductTypeTemplate();

            $trackingAttributes = array_merge(
                $trackingAttributes,
                $amazonProductTypeTemplate->getCustomAttributesName()
            );
        }

        if ($this->getAmazonListingProduct()->isExistProductTaxCodeTemplate()) {
            $productTaxCodeTemplate = $this->getAmazonListingProduct()->getProductTaxCodeTemplate();

            $trackingAttributes = array_merge(
                $trackingAttributes, $productTaxCodeTemplate->getProductTaxCodeAttributes()
            );
        }

        if ($sellingTemplate = $this->getAmazonListingProduct()->getSellingFormatTemplate()) {
            /** @var Ess_M2ePro_Model_Amazon_Template_SellingFormat $amazonSellingTemplate */
            $amazonSellingTemplate = $sellingTemplate->getChildObject();
            if (!$amazonSellingTemplate->isListPriceModeNone()) {
                $listPriceAttribute = $amazonSellingTemplate->getListPriceAttribute();
                $trackingAttributes = array_merge($trackingAttributes, array($listPriceAttribute));
            }
        }

        return array_unique($trackingAttributes);
    }

    public function getRepricingTrackingAttributes()
    {
        $trackingAttributes = array();

        if (!$this->getAmazonListingProduct()->isRepricingUsed()) {
            return $trackingAttributes;
        }

        $accountRepricing = $this->getAmazonListingProduct()->getRepricing()->getAccountRepricing();

        $trackingAttributes = array_merge(
            $trackingAttributes,
            $accountRepricing->getDisableAttributes(),
            $accountRepricing->getRegularPriceAttributes(),
            $accountRepricing->getMinPriceAttributes(),
            $accountRepricing->getMaxPriceAttributes()
        );

        return array_unique($trackingAttributes);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    protected function getAmazonListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    //########################################
}
