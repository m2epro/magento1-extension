<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListing');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_listing';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = '';
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
        $url = $this->getUrl('*/adminhtml_walmart_log/listing');
        $this->_addButton(
            'view_log', array(
            'label'     => Mage::helper('M2ePro')->__('View Log'),
            'onclick'   => 'WalmartListingObj.viewLogs(\'' . $url . '\')',
            'class'     => 'button_link'
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/adminhtml_walmart_listing_create/index', array('step' => 1, 'clear' => 'yes'));
        $this->_addButton(
            'add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Listing'),
            'onclick'   => 'WalmartListingObj.createListing(\'' . $url . '\')',
            'class'     => 'add'
            )
        );
        // ---------------------------------------
    }

    //########################################

    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_help');

        return $helpBlock->toHtml() . parent::_toHtml();
    }

    //########################################
}
