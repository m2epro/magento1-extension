<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Other_Synchronization_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingOtherSynchronizationEditTabs');
        //------------------------------

        $this->setTitle(Mage::helper('M2ePro')->__('Configuration'));
        $this->setDestElementId('edit_form');
    }

    // ####################################

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label'   => Mage::helper('M2ePro')->__('General'),
            'title'   => Mage::helper('M2ePro')->__('General'),
            'content' => $this->getLayout()
                ->createBlock('M2ePro/adminhtml_ebay_listing_other_synchronization_edit_tabs_general')
                ->toHtml(),
        ))
            ->addTab('revise', array(
            'label'   => Mage::helper('M2ePro')->__('Revise Rules'),
            'title'   => Mage::helper('M2ePro')->__('Revise Rules'),
            'content' => $this->getLayout()
                ->createBlock('M2ePro/adminhtml_ebay_listing_other_synchronization_edit_tabs_revise')
                ->toHtml(),
        ))
            ->addTab('relist', array(
            'label'   => Mage::helper('M2ePro')->__('Relist Rules'),
            'title'   => Mage::helper('M2ePro')->__('Relist Rules'),
            'content' => $this->getLayout()
                ->createBlock('M2ePro/adminhtml_ebay_listing_other_synchronization_edit_tabs_relist')
                ->toHtml(),
        ))
            ->addTab('stop', array(
            'label'   => Mage::helper('M2ePro')->__('Stop Rules'),
            'title'   => Mage::helper('M2ePro')->__('Stop Rules'),
            'content' => $this->getLayout()
                ->createBlock('M2ePro/adminhtml_ebay_listing_other_synchronization_edit_tabs_stop')
                ->toHtml(),
        ))
            ->setActiveTab($this->getRequest()->getParam('tab', 'general'));

        return parent::_beforeToHtml();
    }

    // ####################################
}