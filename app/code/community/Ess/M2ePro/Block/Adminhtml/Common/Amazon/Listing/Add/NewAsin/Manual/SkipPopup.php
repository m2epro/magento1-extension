<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Add_NewAsin_Manual_SkipPopup
    extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingAddNewAsinManualPopup');
        // ---------------------------------------

        $this->setTemplate('M2ePro/common/amazon/listing/add/search_asin/manual/skip_popup.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $url = $this->getUrl('*/*/index', array('_current' => true, 'step' => 3));

        $data = array(
            'class'   => 'next',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'onclick'   => 'setLocation(\''.$url.'\')',
        );
        $this->setChild('continue_button',$this->getLayout()->createBlock('adminhtml/widget_button')->setData($data));

        return $this;
    }

    //########################################
}