<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_Edit_Other
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayConfigurationCategoryEditOther');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_configuration_category_edit';
        $this->_mode = 'other';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Edit') . ' ' .
            $this->getCategoryTitle($this->getRequest()->getParam('type')) . ' ' .
            Mage::helper('M2ePro')->__('Category');
        //------------------------------

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
            'onclick'   => 'EbayConfigurationCategoryHandlerObj.save_click(\'other\', true)',
            'class'     => 'save'
        ));

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'EbayConfigurationCategoryHandlerObj.save_click(\'other\', false)',
            'class'     => 'save'
        ));
    }

    // ########################################

    protected function getCategoryTitle($type)
    {
        $titles = array(
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN => Mage::helper('M2ePro')->__('eBay Primary'),
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_SECONDARY
                                                                => Mage::helper('M2ePro')->__('eBay Secondary'),
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN => Mage::helper('M2ePro')->__('Store Primary'),
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_SECONDARY
                                                                => Mage::helper('M2ePro')->__('Store Secondary'),

        );

        return $titles[(int)$type];
    }

    // ########################################
}