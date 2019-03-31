<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Add_StepOne extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct($attributes)
    {
        parent::__construct();

        $this->setData($attributes);

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingAddStepOne');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_listing';
        $this->_mode = 'add';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Amazon')->getTitle();

            $this->_headerText = Mage::helper('M2ePro')->__("%component_name% / Creating A New M2E Pro Listing",
                $componentName
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__("Creating A New M2E Pro Listing");
        }
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/adminhtml_amazon_listing_create/index', array(
            '_current' => true
        ));
        $this->_addButton('save_and_next', array(
            'label'     => Mage::helper('M2ePro')->__('Next Step'),
            'onclick'   => 'AmazonListingSettingsHandlerObj.save_click(\'' . $url . '\')',
            'class'     => 'next'
        ));
        // ---------------------------------------
    }

    //########################################
}