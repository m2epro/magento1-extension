<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_SourceMode
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingSourceMode');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing';
        $this->_mode = 'sourceMode';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Add Products');
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

        if (!$this->getRequest()->getParam('listing_creation', false)) {
            $url = $this->getUrl('*/adminhtml_ebay_listing/view',array(
                'id' => $this->getRequest()->getParam('listing_id')
            ));
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'setLocation(\''.$url.'\')',
                'class'     => 'back'
            ));
        }

        // ---------------------------------------
        $url = $this->getUrl('*/*/*',array('_current' => true));
        $this->_addButton('next', array(
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'onclick'   => 'CommonHandlerObj.submitForm(\''.$url.'\');',
            'class'     => 'scalable next'
        ));
        // ---------------------------------------
    }

    protected function _toHtml()
    {
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing', $this->getRequest()->getParam('listing_id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header','',
            array('listing' => $listing)
        );

        $this->setChild('view_header', $viewHeaderBlock);

        return parent::_toHtml();
    }

    //########################################
}