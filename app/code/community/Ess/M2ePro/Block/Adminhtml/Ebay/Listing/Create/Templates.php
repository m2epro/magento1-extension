<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Create_Templates
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListingCreateTemplates');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_create';
        $this->_mode = 'templates';

        $this->_headerText = Mage::helper('M2ePro')->__("Creating A New Ebay M2E Pro Listing");

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl(
            '*/adminhtml_ebay_listing_create/index',
            array('_current' => true, 'step' => 1)
        );
        $this->_addButton(
            'back', array(
                'label'     => Mage::helper('M2ePro')->__('Previous Step'),
                'onclick'   => 'CommonObj.back_click(\'' . $url . '\')',
                'class'     => 'back'
            )
        );

        $nextStepBtnText = 'Next Step';

        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue(
            Ess_M2ePro_Model_Ebay_Listing::CREATE_LISTING_SESSION_DATA
        );
        if (isset($sessionData['creation_mode']) && $sessionData['creation_mode'] ===
            Ess_M2ePro_Helper_View::LISTING_CREATION_MODE_LISTING_ONLY
        ) {
            $nextStepBtnText = 'Complete';
        }

        $url = $this->getUrl(
            '*/adminhtml_ebay_listing_create/index', array('_current' => true)
        );

        $this->_addButton(
            'save', array(
                'id'        => 'save',
                'label'     => Mage::helper('M2ePro')->__($nextStepBtnText),
                'onclick' => 'CommonObj.save_click(\'' . $url . '\')',
                'class'     => 'next'
            )
        );
    }

    //########################################

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addConstants(
            Mage::helper('M2ePro')->getClassConstants('Ess_M2ePro_Helper_Component_Ebay'),
            'Ess_M2ePro_Helper_Component'
        );

        return parent::_prepareLayout();
    }

    //########################################
}
