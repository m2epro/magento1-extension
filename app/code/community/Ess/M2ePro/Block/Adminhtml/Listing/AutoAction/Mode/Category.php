<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Category extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingAutoActionModeCategory');
        //------------------------------

        $this->setTemplate('M2ePro/listing/auto_action/mode/category.phtml');
    }

    // ####################################

    protected function prepareGroupsGrid()
    {
        $groupGrid = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_listing_autoAction_mode_category_group_grid');
        $groupGrid->prepareGrid();
        $this->setChild('group_grid', $groupGrid);

        return $groupGrid;
    }

    // ####################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        //------------------------------
        $groupGrid = $this->prepareGroupsGrid();
        //------------------------------

        //------------------------------
        $data = array(
            'id'      => 'confirm_button',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Save'),
            'onclick' => 'ListingAutoActionHandlerObj.confirm();',
            'style'   => 'display: none;'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_button', $buttonBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'id'      => 'close_button',
            'class'   => 'close_button',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'Windows.getFocusedWindow().close();'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('close_button', $buttonBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'id'      => 'continue_button',
            'class'   => 'continue_button next',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'style'   => 'display: none;',
            'onclick' => ''
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('continue_button', $buttonBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'id'      => 'add_button',
            'class'   => 'add_button add',
            'label'   => Mage::helper('M2ePro')->__('Add New Rule'),
            'onclick' => 'ListingAutoActionHandlerObj.categoryStepOne();',
            'style'   => $groupGrid->getCollection()->getSize() == 0 ? 'display: none;' : ''
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('add_button', $buttonBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'id'      => 'add_first_button',
            'class'   => 'add_first_button add',
            'label'   => Mage::helper('M2ePro')->__('Add New Rule'),
            'onclick' => 'ListingAutoActionHandlerObj.categoryStepOne();'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('add_first_button', $buttonBlock);
        //------------------------------
    }

    // ####################################
}
