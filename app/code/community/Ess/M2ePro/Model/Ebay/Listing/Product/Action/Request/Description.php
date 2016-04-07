<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Description
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
{
    const PRODUCT_DETAILS_DOES_NOT_APPLY = 'Does Not Apply';
    const PRODUCT_DETAILS_UNBRANDED = 'Unbranded';

    const UPLOAD_IMAGES_MODE_AUTO = 1;
    const UPLOAD_IMAGES_MODE_SELF = 2;
    const UPLOAD_IMAGES_MODE_EPS  = 3;

    /**
     * @var Ess_M2ePro_Model_Template_Description
     */
    private $descriptionTemplate = NULL;

    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        $data = array();

        if ($this->getConfigurator()->isGeneralAllowed()) {

            $data = array_merge(
                array(
                    'hit_counter'          => $this->getEbayDescriptionTemplate()->getHitCounterType(),
                    'listing_enhancements' => $this->getEbayDescriptionTemplate()->getEnhancements(),
                    'item_condition_note'  => $this->getConditionNoteData(),
                    'product_details'      => $this->getProductDetailsData()
                ),
                $this->getConditionData()
            );
        }

        return array_merge(
            $data,
            $this->getTitleData(),
            $this->getSubtitleData(),
            $this->getDescriptionData(),
            $this->getImagesData()
        );
    }

    //########################################

    /**
     * @return array
     */
    public function getTitleData()
    {
        if (!$this->getConfigurator()->isTitleAllowed()) {
            return array();
        }

        $this->searchNotFoundAttributes();
        $data = $this->getDescriptionSource()->getTitle();
        $this->processNotFoundAttributes('Title');

        return array(
            'title' => $data
        );
    }

    /**
     * @return array
     */
    public function getSubtitleData()
    {
        if (!$this->getConfigurator()->isSubtitleAllowed()) {
            return array();
        }

        $this->searchNotFoundAttributes();
        $data = $this->getDescriptionSource()->getSubTitle();
        $this->processNotFoundAttributes('Subtitle');

        return array(
            'subtitle' => $data
        );
    }

    /**
     * @return array
     */
    public function getDescriptionData()
    {
        if (!$this->getConfigurator()->isDescriptionAllowed()) {
            return array();
        }

        $this->searchNotFoundAttributes();

        $data = $this->getDescriptionSource()->getDescription();
        $data = $this->getEbayListingProduct()->getDescriptionRenderer()->parseTemplate($data);

        $this->processNotFoundAttributes('Description');

        return array(
            'description' => $data
        );
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getImagesData()
    {
        if (!$this->getConfigurator()->isImagesAllowed()) {
            return array();
        }

        $this->searchNotFoundAttributes();

        $data = array(
            'gallery_type' => $this->getEbayDescriptionTemplate()->getGalleryType(),
            'images'       => $this->getDescriptionSource()->getGalleryImages(),
            'supersize'    => $this->getEbayDescriptionTemplate()->isUseSupersizeImagesEnabled()
        );

        $this->processNotFoundAttributes('Main Image / Gallery Images');

        return array(
            'images' => $data
        );
    }

    //########################################

    /**
     * @return array
     */
    public function getProductDetailsData()
    {
        if ($this->getIsVariationItem()) {
            return array();
        }

        $data = array();

        foreach (array('isbn','epid','upc','ean','brand','mpn') as $tempType) {

            if ($this->getEbayDescriptionTemplate()->isProductDetailsModeNone($tempType)) {
                continue;
            }

            if ($this->getEbayDescriptionTemplate()->isProductDetailsModeDoesNotApply($tempType)) {
                $data[$tempType] = ($tempType == 'brand') ? self::PRODUCT_DETAILS_UNBRANDED :
                                                            self::PRODUCT_DETAILS_DOES_NOT_APPLY;
                continue;
            }

            $this->searchNotFoundAttributes();
            $tempValue = $this->getDescriptionSource()->getProductDetail($tempType);

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

        $data['include_description'] = $this->getEbayDescriptionTemplate()->isProductDetailsIncludeDescription();
        $data['include_image'] = $this->getEbayDescriptionTemplate()->isProductDetailsIncludeImage();

        return $data;
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getConditionData()
    {
        $this->searchNotFoundAttributes();
        $data = $this->getDescriptionSource()->getCondition();

        if (!$this->processNotFoundAttributes('Condition')) {
            return array();
        }

        return array(
            'item_condition' => $data
        );
    }

    /**
     * @return string
     */
    public function getConditionNoteData()
    {
        $this->searchNotFoundAttributes();
        $data = $this->getDescriptionSource()->getConditionNote();
        $this->processNotFoundAttributes('Seller Notes');

        return $data;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    private function getDescriptionTemplate()
    {
        if (is_null($this->descriptionTemplate)) {
            $this->descriptionTemplate = $this->getListingProduct()
                                              ->getChildObject()
                                              ->getDescriptionTemplate();
        }
        return $this->descriptionTemplate;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Description
     */
    private function getEbayDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Description_Source
     */
    private function getDescriptionSource()
    {
        return $this->getEbayListingProduct()->getDescriptionTemplateSource();
    }

    //########################################

    private function deleteMPNifBrandIsNotSelected(array $data)
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

    private function deleteNotAllowedIdentifier(array $data)
    {
        if (empty($data)) {
            return $data;
        }

        $categoryId = $this->getEbayListingProduct()->getCategoryTemplateSource()->getMainCategory();
        $marketplaceId = $this->getMarketplace()->getId();
        $categoryFeatures = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
                                   ->getFeatures($categoryId, $marketplaceId);

        if (empty($categoryFeatures)) {
            return $data;
        }

        $statusDisabled = Ess_M2ePro_Helper_Component_Ebay_Category_Ebay::PRODUCT_IDENTIFIER_STATUS_DISABLED;

        foreach (array('ean','upc','isbn') as $identifier) {

            $key = $identifier.'_enabled';
            if (!isset($categoryFeatures[$key]) || $categoryFeatures[$key] != $statusDisabled) {
                continue;
            }

            if (isset($data[$identifier])) {

                unset($data[$identifier]);

                // M2ePro_TRANSLATIONS
                // The value of %type% was no sent because it is not allowed in this Category
                $this->addWarningMessage(
                    Mage::helper('M2ePro')->__(
                        'The value of %type% was no sent because it is not allowed in this Category',
                        Mage::helper('M2ePro')->__(strtoupper($identifier))
                    )
                );
            }
        }

        return $data;
    }

    //########################################
}