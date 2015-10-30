<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_AccountMarketplace
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingAccountMarketplace');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing';
        $this->_mode = 'accountMarketplace';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Creating A New M2E Pro Listing');
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
        $this->_addButton('video_tutorial', array(
            'label'     => Mage::helper('M2ePro')->__('Show Video Tutorial'),
            'class'     => 'button_link',
            'onclick'   => 'VideoTutorialHandlerObj.openPopUp();'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('next', array(
            'label'     => Mage::helper('M2ePro')->__('Next Step'),
            'class'     => 'scalable next next_step_button'
        ));
        // ---------------------------------------
    }

    //########################################
}