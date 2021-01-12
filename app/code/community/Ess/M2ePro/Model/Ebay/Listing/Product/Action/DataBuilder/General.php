<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_General
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract
{
    const LISTING_TYPE_AUCTION  = 'Chinese';
    const LISTING_TYPE_FIXED    = 'FixedPriceItem';

    const PRODUCT_DETAILS_DOES_NOT_APPLY = 'Does Not Apply';
    const PRODUCT_DETAILS_UNBRANDED = 'Unbranded';

    //########################################

    public function getData()
    {
        $data = array(
            'duration' => $this->getEbayListingProduct()->getSellingFormatTemplateSource()->getDuration(),
            'is_private' => $this->getEbayListingProduct()->getEbaySellingFormatTemplate()->isPrivateListing(),
            'currency' => $this->getEbayMarketplace()->getCurrency(),
            'hit_counter'          => $this->getEbayListingProduct()->getEbayDescriptionTemplate()->getHitCounterType(),
            'listing_enhancements' => $this->getEbayListingProduct()->getEbayDescriptionTemplate()->getEnhancements(),
            'product_details'      => $this->getProductDetailsData()
        );

        if ($this->getEbayListingProduct()->isListingTypeFixed()) {
            $data['listing_type'] = self::LISTING_TYPE_FIXED;
        } else {
            $data['listing_type'] = self::LISTING_TYPE_AUCTION;
        }

        if ($this->getEbayListingProduct()->getEbaySellingFormatTemplate()->isRestrictedToBusinessEnabled()) {
            $data['restricted_to_business'] = $this->getEbayListingProduct()
                ->getEbaySellingFormatTemplate()
                ->isRestrictedToBusinessEnabled();
        }

        return $data;
    }

    //########################################

    /**
     * @return array
     */
    protected function getProductDetailsData()
    {
        if ($this->_isVariationItem) {
            return array();
        }

        $data = array();

        foreach (array('isbn','epid','upc','ean','brand','mpn') as $tempType) {
            if ($this->getEbayListingProduct()->getEbayDescriptionTemplate()->isProductDetailsModeNone($tempType)) {
                continue;
            }

            if ($this->getEbayListingProduct()
                     ->getEbayDescriptionTemplate()
                     ->isProductDetailsModeDoesNotApply($tempType)) {
                $data[$tempType] = ($tempType == 'brand') ? self::PRODUCT_DETAILS_UNBRANDED :
                    self::PRODUCT_DETAILS_DOES_NOT_APPLY;
                continue;
            }

            $this->searchNotFoundAttributes();
            $tempValue = $this->getEbayListingProduct()->getDescriptionTemplateSource()->getProductDetail($tempType);

            if (!$this->processNotFoundAttributes(strtoupper($tempType)) || !$tempValue) {
                continue;
            }

            $data[$tempType] = $tempValue;
        }

        $data = $this->deleteMPNifBrandIsNotSelected($data);
        $data = $this->deleteNotAllowedIdentifier($data);

        if (empty($data)) {
            return $data;
        }

        $data['include_ebay_details'] = $this->getEbayListingProduct()
            ->getEbayDescriptionTemplate()
            ->isProductDetailsIncludeEbayDetails();
        $data['include_image'] = $this->getEbayListingProduct()
            ->getEbayDescriptionTemplate()
            ->isProductDetailsIncludeImage();

        return $data;
    }

    protected function deleteMPNifBrandIsNotSelected(array $data)
    {
        if (empty($data)) {
            return $data;
        }

        if (empty($data['brand'])) {
            unset($data['mpn']);
        } else if ($data['brand'] == self::PRODUCT_DETAILS_UNBRANDED) {
            $data['mpn'] = self::PRODUCT_DETAILS_DOES_NOT_APPLY;
        } else if (empty($data['mpn'])) {
            $data['mpn'] = self::PRODUCT_DETAILS_DOES_NOT_APPLY;
        }

        return $data;
    }

    protected function deleteNotAllowedIdentifier(array $data)
    {
        if (empty($data)) {
            return $data;
        }

        $categoryId = $this->getEbayListingProduct()->getCategoryTemplateSource()->getCategoryId();
        $marketplaceId = $this->getMarketplace()->getId();
        $categoryFeatures = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
            ->getFeatures($categoryId, $marketplaceId);

        if (empty($categoryFeatures)) {
            return $data;
        }

        $statusDisabled = Ess_M2ePro_Helper_Component_Ebay_Category_Ebay::PRODUCT_IDENTIFIER_STATUS_DISABLED;

        foreach (array('ean','upc','isbn','epid') as $identifier) {
            $key = $identifier.'_enabled';
            if (!isset($categoryFeatures[$key]) || $categoryFeatures[$key] != $statusDisabled) {
                continue;
            }

            if (isset($data[$identifier])) {
                unset($data[$identifier]);

                $this->addWarningMessage(
                    Mage::helper('M2ePro')->__(
                        'The value of %type% was not sent because it is not allowed in this Category',
                        Mage::helper('M2ePro')->__(strtoupper($identifier))
                    )
                );
            }
        }

        return $data;
    }

    //########################################
}
