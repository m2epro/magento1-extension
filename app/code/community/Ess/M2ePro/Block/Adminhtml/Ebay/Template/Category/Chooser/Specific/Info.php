<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Specific_Info extends
    Ess_M2ePro_Block_Adminhtml_Widget_Info
{
    //########################################

    protected function _prepareLayout()
    {
        if ($this->getData('category_mode') == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            $category = Mage::helper('M2ePro')->__('Magento Attribute') .' > '.
                Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($this->getData('category_value'));
        } else {
            $category = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                $this->getData('category_value'), $this->getData('marketplace_id')
            );
            $category .= ' (' . $this->getData('category_value') . ')';
        }

        $this->setInfo(
            array(
                array(
                    'label' => $this->__('Category'),
                    'value' => $category
                )
            )
        );

        return parent::_prepareLayout();
    }

    //########################################
}
