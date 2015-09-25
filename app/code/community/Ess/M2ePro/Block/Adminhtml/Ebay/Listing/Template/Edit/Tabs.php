<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingTemplateEditTabs');
        //------------------------------

        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setDestElementId('edit_form');
    }

    // ####################################

    public function getAllowedTabs()
    {
        if (!isset($this->_data['allowed_tabs']) || !is_array($this->_data['allowed_tabs'])) {
            return array();
        }

        return $this->_data['allowed_tabs'];
    }

    private function isTabAllowed($tab)
    {
        $allowedTabs = $this->getAllowedTabs();

        if (count($allowedTabs) == 0) {
            return true;
        }

        if (in_array($tab, $allowedTabs)) {
            return true;
        }

        return false;
    }

    // ####################################

    protected function _beforeToHtml()
    {
        //------------------------------
        if ($this->isTabAllowed('general')) {
            $block = $this->getLayout()
                          ->createBlock('M2ePro/adminhtml_ebay_listing_template_edit_tabs_general','',
                                        array('policy_localization' => $this->getData('policy_localization')));
            $this->addTab(
                'general',
                array(
                    'label'   => Mage::helper('M2ePro')->__('Payment and Shipping'),
                    'title'   => Mage::helper('M2ePro')->__('Payment and Shipping'),
                    'content' => $block->toHtml(),
                )
            );
        }
        //------------------------------

        //------------------------------
        if ($this->isTabAllowed('selling')) {
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_edit_tabs_selling');
            $this->addTab(
                'selling',
                array(
                    'label'   => Mage::helper('M2ePro')->__('Selling'),
                    'title'   => Mage::helper('M2ePro')->__('Selling'),
                    'content' => $block->toHtml(),
                )
            );
        }
        //------------------------------

        //------------------------------
        if ($this->isTabAllowed('synchronization') && Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $block = $this->getLayout()
                          ->createBlock('M2ePro/adminhtml_ebay_listing_template_edit_tabs_synchronization');
            $this->addTab(
                'synchronization',
                array(
                    'label'   => Mage::helper('M2ePro')->__('Synchronization'),
                    'title'   => Mage::helper('M2ePro')->__('Synchronization'),
                    'content' => $block->toHtml(),
                )
            );
        }
        //------------------------------

        //------------------------------
        $this->setActiveTab($this->getRequest()->getParam('tab', 'general'));
        //------------------------------

        return parent::_beforeToHtml();
    }

    // ####################################
}