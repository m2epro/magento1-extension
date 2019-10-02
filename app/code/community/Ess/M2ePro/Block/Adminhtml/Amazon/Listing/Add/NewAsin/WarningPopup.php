<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Add_NewAsin_WarningPopup
    extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingAddNewAsinWarningPopup');
        // ---------------------------------------

        $this->setTemplate('M2ePro/amazon/listing/add/search_asin/warning_popup.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $url = $this->getUrl('*/*/index', array('_current' => true, 'step' => 5));

        $data = array(
            'class'   => 'next',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'onclick'   => 'setLocation(\''.$url.'\')',
        );
        $this->setChild(
            'continue_button', $this->getLayout()
                                                ->createBlock('adminhtml/widget_button')
            ->setData($data)
        );

        return $this;
    }

    //########################################
}
