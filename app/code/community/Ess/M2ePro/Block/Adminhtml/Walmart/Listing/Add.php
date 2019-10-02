<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Add
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListingAdd');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_listing';
        $this->_mode = 'add';
        // ---------------------------------------
        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Walmart')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__(
                "%component_name% / Creating A New M2E Pro Listing",
                $componentName
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__("Creating A New M2E Pro Listing");
        }

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_walmart_listing_create/index', array(
            '_current' => true
            )
        );
        $this->_addButton(
            'save_and_next', array(
            'label'     => Mage::helper('M2ePro')->__('Next Step'),
            'onclick'   => 'WalmartListingSettingsHandlerObj.save_click(\'' . $url . '\')',
            'class'     => 'next'
            )
        );
        // ---------------------------------------
    }

    //########################################
}
