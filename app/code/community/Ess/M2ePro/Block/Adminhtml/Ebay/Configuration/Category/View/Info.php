<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_View_Info extends
    Ess_M2ePro_Block_Adminhtml_Widget_Info
{
    //########################################

    protected function _prepareLayout()
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_Category $template */
        $template = Mage::getModel('M2ePro/Ebay_Template_Category')->load($this->getData('template_id'));

        $mode = $template->getData('category_mode');
        $category = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
            $template->getData('category_id'), $template->getData('marketplace_id')
        );
        $category .= ' (' . $template->getData('category_id') . ')';

        if ($mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            $category = Mage::helper('M2ePro')->__('Magento Attribute') .' > '.
                Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($template->getData('category_attribute'));
        }

        $this->setInfo(
            array(
                array(
                    'label' => $this->__('Marketplace'),
                    'value' => $template->getMarketplace()->getTitle()
                ),
                array(
                    'label' => $this->__('Category'),
                    'value' => $category
                )
            )
        );

        return parent::_prepareLayout();
    }

    //########################################

    /*
     * To get "Category" block in center of screen
     */
    public function getInfoPartWidth($index)
    {
        if ($index === 0) {
            return '33%';
        }

        return '66%';
    }

    public function getInfoPartAlign($index)
    {
        return 'left';
    }

    //########################################
}
