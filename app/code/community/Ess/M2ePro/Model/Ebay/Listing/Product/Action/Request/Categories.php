<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Categories
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    private $categoryTemplate = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    private $otherCategoryTemplate = NULL;

    // ########################################

    public function getData()
    {
        $data = $this->getCategoriesData();

        $data['item_specifics'] = $this->getItemSpecificsData();

        if ($this->getCompatibilityHelper()->isMarketplaceSupportsSpecific($this->getMarketplace()->getId())) {
            $tempData = $this->getPartsCompatibilityData(
                Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_SPECIFIC
            );
            $tempData !== false && $data['motors_specifics'] = $tempData;
        }

        if ($this->getCompatibilityHelper()->isMarketplaceSupportsKtype($this->getMarketplace()->getId())) {
            $tempData = $this->getPartsCompatibilityData(
                Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_KTYPE
            );
            $tempData !== false && $data['motors_ktypes'] = $tempData;
        }

        return $data;
    }

    // ########################################

    public function getCategoriesData()
    {
        $data = array(
            'category_main_id' => $this->getCategorySource()->getMainCategory(),
            'category_secondary_id' => 0,
            'store_category_main_id' => 0,
            'store_category_secondary_id' => 0
        );

        if (!is_null($this->getOtherCategoryTemplate())) {
            $data['category_secondary_id'] = $this->getOtherCategorySource()->getSecondaryCategory();
            $data['store_category_main_id'] = $this->getOtherCategorySource()->getStoreCategoryMain();
            $data['store_category_secondary_id'] = $this->getOtherCategorySource()->getStoreCategorySecondary();
        }

        return $data;
    }

    public function getPartsCompatibilityData($type)
    {
        if (!$this->isSetCompatibilityAttribute($type)) {
            return false;
        }

        $this->searchNotFoundAttributes();

        $rawData = $this->getRawCompatibilityData($type);

        if (!$this->processNotFoundAttributes('Compatibility')) {
            return false;
        }

        if ($type == Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_SPECIFIC) {
            return $this->getPreparedMotorPartsCompatibilitySpecificData($rawData);
        }

        if ($type == Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_KTYPE) {
            return $this->getPreparedMotorPartsCompatibilityKtypeData($rawData);
        }

        return NULL;
    }

    // ----------------------------------------

    public function getItemSpecificsData()
    {
        $data = array();

        foreach ($this->getCategoryTemplate()->getSpecifics(true) as $specific) {

            /** @var $specific Ess_M2ePro_Model_Ebay_Template_Category_Specific */

            $this->searchNotFoundAttributes();

            $tempAttributeLabel = $specific->getSource($this->getMagentoProduct())
                                           ->getLabel();
            $tempAttributeValues = $specific->getSource($this->getMagentoProduct())
                                            ->getValues();

            if (!$this->processNotFoundAttributes('Specifics')) {
                continue;
            }

            $values = array();
            foreach ($tempAttributeValues as $tempAttributeValue) {
                if ($tempAttributeValue == '--') {
                    continue;
                }
                $values[] = $tempAttributeValue;
            }

            $data[] = array(
                'name' => $tempAttributeLabel,
                'value' => $values
            );
        }

        return $data;
    }

    // ########################################

    private function getPreparedMotorPartsCompatibilitySpecificData($data)
    {
        $ebayAttributes = $this->getEbayMotorsSpecificsAttributes();

        $preparedData = array();
        $emptySavedEpids = array();

        foreach ($data as $epid => $epidData) {
            if (empty($epidData['info'])) {
                $emptySavedEpids[] = $epid;
                continue;
            }

            $compatibilityList = array();
            $compatibilityData = $this->buildSpecificsCompatibilityData($epidData['info']);

            foreach ($compatibilityData as $key => $value) {

                if ($value == '--') {
                    unset($compatibilityData[$key]);
                    continue;
                }

                $name = $key;

                foreach ($ebayAttributes as $ebayAttribute) {
                    if ($ebayAttribute['title'] == $key) {
                        $name = $ebayAttribute['ebay_id'];
                        break;
                    }
                }

                $compatibilityList[] = array(
                    'name'  => $name,
                    'value' => $value
                );
            }

            $preparedData[] = array(
                'list' => $compatibilityList,
                'note' => $epidData['note'],
            );
        }

        if(count($emptySavedEpids) > 0) {
            $isSingleEpid = count($emptySavedEpids) > 1;
            $msg = 'The '.implode(', ', $emptySavedEpids).' ePID'.($isSingleEpid ? 's' : '');
            $msg .= ' specified in the Compatibility Attribute';
            $msg .= ' were dropped out of the Listing because '.($isSingleEpid ? 'it was' : 'they were');
            $msg .= ' deleted from eBay Catalog of Compatible Vehicles.';
            $this->addWarningMessage($msg);
        }

        return $preparedData;
    }

    private function getPreparedMotorPartsCompatibilityKtypeData($data)
    {
        $preparedData = array();
        $emptySavedKtypes = array();

        foreach ($data as $ktype => $ktypeData) {
            if (empty($ktypeData['info'])) {
                $emptySavedKtypes[] = $ktype;
                continue;
            }

            $preparedData[] = array(
                'ktype' => $ktype,
                'note' => $ktypeData['note'],
            );
        }

        if(count($emptySavedKtypes) > 0) {
            $isSingleKtype = count($emptySavedKtypes) > 1;
            $msg = 'The '.implode(', ', $emptySavedKtypes).' kType'.($isSingleKtype ? 's' : '');
            $msg .= ' specified in the Compatibility Attribute';
            $msg .= ' were dropped out of the Listing because '.($isSingleKtype ? 'it was' : 'they were');
            $msg .= ' deleted from eBay Catalog of Compatible Vehicles.';
            $this->addWarningMessage($msg);
        }

        return $preparedData;
    }

    // ----------------------------------------

    private function isSetCompatibilityAttribute($type)
    {
        $attributeCode  = $this->getCompatibilityAttribute($type);
        return !empty($attributeCode);
    }

    private function getRawCompatibilityData($type)
    {
        $attributeValue = $this->getMagentoProduct()->getAttributeValue($this->getCompatibilityAttribute($type));
        if (empty($attributeValue)) {
            return array();
        }

        $compatibilityData = $this->getCompatibilityHelper()->parseAttributeValue($attributeValue);

        $typeIdentifier = $this->getCompatibilityHelper()->getIdentifierKey($type);

        $select = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from($this->getCompatibilityHelper()->getDictionaryTable($type))
            ->where(
                '`'.$typeIdentifier.'` IN (?)',
                array_keys($compatibilityData)
            );

        foreach ($select->query()->fetchAll() as $attributeRow) {
            $compatibilityData[$attributeRow[$typeIdentifier]]['info'] = $attributeRow;
        }

        return $compatibilityData;
    }

    // ----------------------------------------

    private function getEbayMotorsSpecificsAttributes()
    {
        $categoryId = $this->getCategorySource()->getMainCategory();
        $categoryData = $this->getEbayMarketplace()->getCategory($categoryId);

        $features = !empty($categoryData['features']) ?
                    (array)json_decode($categoryData['features'], true) : array();

        $attributes = !empty($features['parts_compatibility_attributes']) ?
                      $features['parts_compatibility_attributes'] : array();

        return $attributes;
    }

    private function buildSpecificsCompatibilityData($resource)
    {
        $compatibilityData = array(
            'Make'  => $resource['make'],
            'Model' => $resource['model'],
            'Year'  => $resource['year'],
            'Submodel' => $resource['submodel'],
        );

        if ($resource['product_type'] == Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::PRODUCT_TYPE_VEHICLE) {
            $compatibilityData['Trim'] = $resource['trim'];
            $compatibilityData['Engine'] = $resource['engine'];
        }

        return $compatibilityData;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    private function getCategoryTemplate()
    {
        if (is_null($this->categoryTemplate)) {
            $this->categoryTemplate = $this->getListingProduct()
                                           ->getChildObject()
                                           ->getCategoryTemplate();
        }
        return $this->categoryTemplate;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    private function getOtherCategoryTemplate()
    {
        if (is_null($this->otherCategoryTemplate)) {
            $this->otherCategoryTemplate = $this->getListingProduct()
                                                ->getChildObject()
                                                ->getOtherCategoryTemplate();
        }
        return $this->otherCategoryTemplate;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility
     */
    private function getCompatibilityHelper()
    {
        return Mage::helper('M2ePro/Component_Ebay_Motor_Compatibility');
    }

    private function getCompatibilityAttribute($type)
    {
        return $this->getCompatibilityHelper()->getAttribute($type);
    }

    // ########################################
    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category_Source
     */
    private function getCategorySource()
    {
        return $this->getEbayListingProduct()->getCategoryTemplateSource();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory_Source
     */
    private function getOtherCategorySource()
    {
        return $this->getEbayListingProduct()->getOtherCategoryTemplateSource();
    }

    // ########################################
}