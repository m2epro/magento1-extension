<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Specific_Form_Renderer_Custom extends
    Mage_Adminhtml_Block_Template
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;

    //########################################

    protected function _construct()
    {
        $this->setTemplate('M2ePro/ebay/template/category/chooser/specific/form/renderer/custom.phtml');
    }

    //########################################

    public function getElement()
    {
        return $this->_element;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }

    public function getRemoveCustomSpecificButtonHtml()
    {
        $buttonBlock = Mage::getSingleton('core/layout')
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'label'   => '',
                    'onclick' => 'EbayTemplateCategorySpecificsObj.removeCustomSpecific(this);',
                    'class'   => 'scalable delete remove_custom_specific_button'
                )
            );

        return $buttonBlock->toHtml();
    }

    public function getAddCustomSpecificButtonHtml()
    {
        /** @var Mage_Adminhtml_Block_Widget_Button $buttonBlock */
        $buttonBlock = Mage::getSingleton('core/layout')
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'id'      => 'add_custom_specific_button',
                    'label'   => Mage::helper('M2ePro')->__('Add Specific'),
                    'onclick' => 'EbayTemplateCategorySpecificsObj.addCustomSpecificRow();',
                    'class'   => 'add'
                )
            );

        return $buttonBlock->toHtml();
    }

    //########################################
}
