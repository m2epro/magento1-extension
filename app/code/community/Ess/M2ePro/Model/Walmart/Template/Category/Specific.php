<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_Category_Specific extends Ess_M2ePro_Model_Component_Abstract
{
    const DICTIONARY_TYPE_TEXT      = 1;
    const DICTIONARY_TYPE_SELECT    = 2;
    const DICTIONARY_TYPE_CONTAINER = 3;

    const DICTIONARY_MODE_RECOMMENDED_VALUE = 'recommended_value';
    const DICTIONARY_MODE_CUSTOM_VALUE      = 'custom_value';
    const DICTIONARY_MODE_CUSTOM_ATTRIBUTE  = 'custom_attribute';
    const DICTIONARY_MODE_NONE              = 'none';

    const TYPE_INT      = 'int';
    const TYPE_FLOAT    = 'float';
    const TYPE_DATETIME = 'date_time';

    const UNIT_SPECIFIC_CODE    = 'unit';
    const MEASURE_SPECIFIC_CODE = 'measure';

    /**
     * @var Ess_M2ePro_Model_Walmart_Template_Category
     */
    private $categoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Walmart_Template_Category_Specific_Source[]
     */
    private $categorySpecificSourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Template_Category_Specific');
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
     * @return Ess_M2ePro_Model_Walmart_Template_Category
     * @throws Exception
     */
    public function getCategoryTemplate()
    {
        if (is_null($this->categoryTemplateModel)) {

            $this->categoryTemplateModel = Mage::getModel('M2ePro/Walmart_Template_Category')->load(
                $this->getTemplateCategoryId()
            );
        }

        return $this->categoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Walmart_Template_Category $instance
     */
    public function setCategoryTemplate(Ess_M2ePro_Model_Walmart_Template_Category $instance)
    {
        $this->categoryTemplateModel = $instance;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_Category
     * @throws Exception
     */
    public function getWalmartCategoryTemplate()
    {
        return $this->getCategoryTemplate();
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Walmart_Template_Category_Specific_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->categorySpecificSourceModels[$productId])) {
            return $this->categorySpecificSourceModels[$productId];
        }

        $this->categorySpecificSourceModels[$productId] = Mage::getModel(
            'M2ePro/Walmart_Template_Category_Specific_Source'
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

    /**
     * @return string
     */
    public function getXpath()
    {
        return trim($this->getData('xpath'), '/');
    }

    public function getMode()
    {
        return $this->getData('mode');
    }

    public function getIsRequired()
    {
        return $this->getData('is_required');
    }

    public function getCustomValue()
    {
        return $this->getData('custom_value');
    }

    public function getCustomAttribute()
    {
        return $this->getData('custom_attribute');
    }

    public function getType()
    {
        return $this->getData('type');
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        $value = $this->getData('attributes');
        return is_string($value) ? (array)Mage::helper('M2ePro')->jsonDecode($value) : array();
    }

    // ---------------------------------------

    public function getCode()
    {
        $pathParts = explode('/', $this->getXpath());
        return preg_replace('/-[0-9]+$/', '', array_pop($pathParts));
    }

    //########################################

    public function isRequired()
    {
        return (bool)$this->getIsRequired();
    }

    //----------------------------------------

    public function isModeNone()
    {
        return $this->getMode() == self::DICTIONARY_MODE_NONE;
    }

    public function isModeCustomValue()
    {
        return $this->getMode() == self::DICTIONARY_MODE_CUSTOM_VALUE;
    }

    public function isModeCustomAttribute()
    {
        return $this->getMode() == self::DICTIONARY_MODE_CUSTOM_ATTRIBUTE;
    }

    public function isModeRecommended()
    {
        return $this->getMode() == self::DICTIONARY_MODE_RECOMMENDED_VALUE;
    }

    //----------------------------------------

    public function isTypeInt()
    {
        return $this->getType() == self::TYPE_INT;
    }

    public function isTypeFloat()
    {
        return $this->getType() == self::TYPE_FLOAT;
    }

    public function isTypeDateTime()
    {
        return $this->getType() == self::TYPE_DATETIME;
    }

    //########################################
}