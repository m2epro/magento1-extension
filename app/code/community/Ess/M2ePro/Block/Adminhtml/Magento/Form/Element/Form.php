<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method setUseContainer($value)
 */
class Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form extends Varien_Data_Form
{
    const CUSTOM_CONTAINER = 'Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_CustomContainer';
    const HELP_BLOCK       = 'Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_HelpBlock';
    const MESSAGES         = 'Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Messages';
    const SELECT           = 'Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Select';
    const SEPARATOR        = 'Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Separator';
    const STORE_SWITCHER   = 'Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_StoreSwitcher';

    protected $_customAttributes = array();

    //########################################

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);

        Varien_Data_Form::setFieldsetElementRenderer(
            Mage::app()->getLayout()->createBlock('M2ePro/adminhtml_magento_form_renderer_element')
        );

        Varien_Data_Form::setFieldsetRenderer(
            Mage::app()->getLayout()->createBlock('M2ePro/adminhtml_magento_form_renderer_fieldset')
        );
    }

    //########################################

    public function addFieldset($elementId, $config, $after = false)
    {
        $fieldSet = parent::addFieldset($elementId, $config, $after);

        $fieldSet->addType(self::CUSTOM_CONTAINER, self::CUSTOM_CONTAINER);
        $fieldSet->addType(self::HELP_BLOCK, self::HELP_BLOCK);
        $fieldSet->addType(self::MESSAGES, self::MESSAGES);
        $fieldSet->addType(self::SELECT, self::SELECT);
        $fieldSet->addType(self::SEPARATOR, self::SEPARATOR);
        $fieldSet->addType(self::STORE_SWITCHER, self::STORE_SWITCHER);

        return $fieldSet;
    }

    //########################################

    public function addCustomAttribute($attribute)
    {
        $this->_customAttributes[] = $attribute;
    }

    public function getHtmlAttributes()
    {
        return array_merge(
            array('id', 'name', 'method', 'action', 'enctype', 'class', 'onsubmit'),
            $this->_customAttributes
        );
    }

    //########################################
}
