<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_WarningPopup extends Mage_Adminhtml_Block_Template
{
    public $categoryGridJsHandler;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingCategoryWarningPopup');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/category/product/warning_popup.phtml');
    }

    //########################################

    public function setCategoryGridJsHandler($handler)
    {
        $this->categoryGridJsHandler = $handler;
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $data = array(
            'class'   => 'next',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'onclick' => "{$this->categoryGridJsHandler}.categoryNotSelectedWarningPopupContinueClick();"
        );
        $this->setChild('continue_button',$this->getLayout()->createBlock('adminhtml/widget_button')->setData($data));

        return $this;
    }

    //########################################
}