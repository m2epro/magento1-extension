<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Template_Description_Specific extends Ess_M2ePro_Model_Component_Abstract
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
    const TYPE_BOOLEAN  = 'boolean';

    /**
     * @var Ess_M2ePro_Model_Template_Description
     */
    protected $_descriptionTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Description_Specific_Source[]
     */
    protected $_descriptionSpecificSourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_Description_Specific');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->_descriptionTemplateModel = null;
        $temp && $this->_descriptionSpecificSourceModels = array();
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Template_Description
     * @throws Exception
     */
    public function getDescriptionTemplate()
    {
        if ($this->_descriptionTemplateModel === null) {
            $this->_descriptionTemplateModel = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
                'Template_Description', $this->getTemplateDescriptionId(), null, array('template')
            );
        }

        return $this->_descriptionTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Description $instance
     */
    public function setDescriptionTemplate(Ess_M2ePro_Model_Template_Description $instance)
    {
        $this->_descriptionTemplateModel = $instance;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description
     * @throws Exception
     */
    public function getAmazonDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Amazon_Template_Description_Specific_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->_descriptionSpecificSourceModels[$productId])) {
            return $this->_descriptionSpecificSourceModels[$productId];
        }

        $this->_descriptionSpecificSourceModels[$productId] = Mage::getModel(
            'M2ePro/Amazon_Template_Description_Specific_Source'
        );
        $this->_descriptionSpecificSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->_descriptionSpecificSourceModels[$productId]->setDescriptionSpecificTemplate($this);

        return $this->_descriptionSpecificSourceModels[$productId];
    }

    //########################################

    /**
     * @return int
     */
    public function getTemplateDescriptionId()
    {
        return (int)$this->getData('template_description_id');
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

    public function getRecommendedValue()
    {
        return $this->getData('recommended_value');
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

    public function isTypeBoolean()
    {
        $value = $this->getData($this->getMode());

        if ($this->getType() == self::TYPE_BOOLEAN || is_bool($value)) {
            return true;
        }

        if (is_string($value) && in_array($value, array('true', 'false'), true)) {
            return true;
        }

        return false;
    }

    //########################################
}
