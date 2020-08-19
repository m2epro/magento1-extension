<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Create_Search
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonListingCreateSearch');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_listing_create';
        $this->_mode = 'search';

        $this->_headerText = Mage::helper('M2ePro')->__("Creating A New Amazon M2E Pro Listing");

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl(
            '*/adminhtml_amazon_listing_create/index', array(
                '_current' => true,
                'step'     => '2'
            )
        );
        $this->addButton(
            'back', array(
                'label'   => Mage::helper('M2ePro')->__('Previous Step'),
                'onclick' => 'CommonObj.back_click(\'' . $url . '\')',
                'class'   => 'back'
            )
        );

        $url = $this->getUrl(
            '*/adminhtml_amazon_listing_create/index', array(
                '_current' => true
            )
        );
        $this->addButton(
            'save_and_next', array(
                'id' => 'save_and_next',
                'label'   => Mage::helper('M2ePro')->__('Next Step'),
                'onclick' => 'CommonObj.save_click(\'' . $url . '\')',
                'class'   => 'next'
            )
        );
    }

    //########################################

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addClassConstants('Ess_M2ePro_Model_Amazon_Listing');

        return parent::_prepareLayout();
    }

    //########################################
}
