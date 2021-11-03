<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Categories
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    protected $_categoryTemplate = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    protected $_categorySecondaryTemplate = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_StoreCategory
     */
    protected $_storeCategoryTemplate = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_StoreCategory
     */
    protected $_storeCategorySecondaryTemplate = null;

    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        $data = $this->getCategoriesData();
        $data['item_specifics'] = $this->getItemSpecificsData();

        return $data;
    }

    //########################################

    /**
     * @return array
     */
    public function getCategoriesData()
    {
        $data = array(
            'category_main_id'            => $this->getCategorySource()->getCategoryId(),
            'category_secondary_id'       => 0,
            'store_category_main_id'      => 0,
            'store_category_secondary_id' => 0
        );

        if ($this->getCategorySecondaryTemplate() !== null) {
            $data['category_secondary_id'] = $this->getCategorySecondarySource()->getCategoryId();
        }

        if ($this->getStoreCategoryTemplate() !== null) {
            $data['store_category_main_id'] = $this->getStoreCategorySource()->getCategoryId();
        }

        if ($this->getStoreCategorySecondaryTemplate() !== null) {
            $data['store_category_secondary_id'] = $this->getStoreCategorySecondarySource()->getCategoryId();
        }

        return $data;
    }

    /**
     * @return array
     */
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
                'name'  => $tempAttributeLabel,
                'value' => $values
            );
        }

        return $data;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    protected function getCategoryTemplate()
    {
        if ($this->_categoryTemplate === null) {
            $this->_categoryTemplate = $this->getListingProduct()->getChildObject()
                ->getCategoryTemplate();
        }

        return $this->_categoryTemplate;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    protected function getCategorySecondaryTemplate()
    {
        if ($this->_categorySecondaryTemplate === null) {
            $this->_categorySecondaryTemplate = $this->getListingProduct()->getChildObject()
                ->getCategorySecondaryTemplate();
        }

        return $this->_categorySecondaryTemplate;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_StoreCategory
     */
    protected function getStoreCategoryTemplate()
    {
        if ($this->_storeCategoryTemplate === null) {
            $this->_storeCategoryTemplate = $this->getListingProduct()->getChildObject()
                ->getStoreCategoryTemplate();
        }

        return $this->_storeCategoryTemplate;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_StoreCategory
     */
    protected function getStoreCategorySecondaryTemplate()
    {
        if ($this->_storeCategorySecondaryTemplate === null) {
            $this->_storeCategorySecondaryTemplate = $this->getListingProduct()->getChildObject()
                ->getStoreCategorySecondaryTemplate();
        }

        return $this->_storeCategorySecondaryTemplate;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category_Source
     */
    protected function getCategorySource()
    {
        return $this->getEbayListingProduct()->getCategoryTemplateSource();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category_Source
     */
    protected function getCategorySecondarySource()
    {
        return $this->getEbayListingProduct()->getCategorySecondaryTemplateSource();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_StoreCategory_Source
     */
    protected function getStoreCategorySource()
    {
        return $this->getEbayListingProduct()->getStoreCategoryTemplateSource();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_StoreCategory_Source
     */
    protected function getStoreCategorySecondarySource()
    {
        return $this->getEbayListingProduct()->getStoreCategorySecondaryTemplateSource();
    }

    //########################################
}
