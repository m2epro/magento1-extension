<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Category_Specific extends Ess_M2ePro_Model_Component_Abstract
{
    const MODE_ITEM_SPECIFICS = 1;
    const MODE_CUSTOM_ITEM_SPECIFICS = 3;

    const VALUE_MODE_NONE = 0;
    const VALUE_MODE_EBAY_RECOMMENDED = 1;
    const VALUE_MODE_CUSTOM_VALUE = 2;
    const VALUE_MODE_CUSTOM_ATTRIBUTE = 3;
    const VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE = 4;

    const RENDER_TYPE_TEXT = 'text';
    const RENDER_TYPE_SELECT_ONE = 'select_one';
    const RENDER_TYPE_SELECT_MULTIPLE = 'select_multiple';
    const RENDER_TYPE_SELECT_ONE_OR_TEXT = 'select_one_or_text';
    const RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT = 'select_multiple_or_text';

    //########################################

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    private $categoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category_Specific_Source[]
     */
    private $categorySpecificSourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Category_Specific');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->categoryTemplateModel = NULL;
        $temp && $this->categorySpecificSourceModels = array();
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getCategoryTemplate()
    {
        if (is_null($this->categoryTemplateModel)) {

            $this->categoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Ebay_Template_Category', $this->getTemplateCategoryId(), NULL, array('template')
            );
        }

        return $this->categoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
         $this->categoryTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Ebay_Template_Category_Specific_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->categorySpecificSourceModels[$productId])) {
            return $this->categorySpecificSourceModels[$productId];
        }

        $this->categorySpecificSourceModels[$productId] = Mage::getModel(
            'M2ePro/Ebay_Template_Category_Specific_Source'
        );
        $this->categorySpecificSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->categorySpecificSourceModels[$productId]->setCategorySpecificTemplate($this);

        return $this->categorySpecificSourceModels[$productId];
    }

    //########################################

    /**
     * @return int
     */
    public function getTemplateCategoryId()
    {
        return (int)$this->getData('template_category_id');
    }

    //########################################

    /**
     * @return int
     */
    public function getMode()
    {
        return (int)$this->getData('mode');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isItemSpecificsMode()
    {
        return $this->getMode() == self::MODE_ITEM_SPECIFICS;
    }

    /**
     * @return bool
     */
    public function isCustomItemSpecificsMode()
    {
        return $this->getMode() == self::MODE_CUSTOM_ITEM_SPECIFICS;
    }

    //########################################

    /**
     * @return int
     */
    public function getValueMode()
    {
        return (int)$this->getData('value_mode');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isNoneValueMode()
    {
        return $this->getValueMode() == self::VALUE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isEbayRecommendedValueMode()
    {
        return $this->getValueMode() == self::VALUE_MODE_EBAY_RECOMMENDED;
    }

    /**
     * @return bool
     */
    public function isCustomValueValueMode()
    {
        return $this->getValueMode() == self::VALUE_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isCustomAttributeValueMode()
    {
        return $this->getValueMode() == self::VALUE_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return bool
     */
    public function isCustomLabelAttributeValueMode()
    {
        return $this->getValueMode() == self::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE;
    }

    //########################################

    /**
     * @return array
     */
    public function getUsedAttributes()
    {
        $attributes = array();

        if ($this->isCustomAttributeValueMode() || $this->isCustomLabelAttributeValueMode()) {
            $attributes[] = $this->getData('value_custom_attribute');
        }

        return $attributes;
    }

    //########################################
}