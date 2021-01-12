<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Create_General
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonListingCreateGeneral');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_listing_create';
        $this->_mode = 'general';

        $this->_headerText = Mage::helper('M2ePro')->__("Creating A New Amazon M2E Pro Listing");

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton(
            'save_and_next', array(
                'id'        => 'save_and_next',
                'label'     => Mage::helper('M2ePro')->__('Next Step'),
                'onclick'   => 'AmazonListingCreateGeneralObj.save_and_next()',
                'class'     => 'next'
            )
        );
    }

    //########################################

    protected function _toHtml()
    {
        return '<div id="progress_bar"></div>'
            . '<div id="content_container">'
            . parent::_toHtml()
            . '</div>';
    }

    //########################################
}
