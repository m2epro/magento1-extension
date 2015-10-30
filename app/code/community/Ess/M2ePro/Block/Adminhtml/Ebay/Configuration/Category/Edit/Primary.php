<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_Edit_Primary
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayConfigurationCategoryEditPrimary');
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Edit eBay Primary Category');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/configuration/category/primary.phtml');

        $this->removeButton('save');
        $this->removeButton('reset');
        $this->removeButton('back');

        $backUrl = $this->getUrl('*/adminhtml_ebay_category/index');

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'setLocation(\''.$backUrl.'\');',
            'class'     => 'back'
        ));

        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'EbayConfigurationCategoryHandlerObj.save_click(\'primary\', true)',
            'class'     => 'save'
        ));

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'EbayConfigurationCategoryHandlerObj.save_click(\'primary\', false)',
            'class'     => 'save'
        ));
    }

    protected function _toHtml()
    {
        $tabsBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_configuration_category_edit_primary_tabs');
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_configuration_category_edit_primary_help');

        return parent::_toHtml() . $helpBlock->toHtml() . $tabsBlock->toHtml() . '<div id="tabs_container"></div>';
    }

    //########################################
}