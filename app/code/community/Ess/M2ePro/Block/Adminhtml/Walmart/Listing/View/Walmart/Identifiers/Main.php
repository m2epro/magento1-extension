<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_View_Walmart_Identifiers_Main
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/walmart/listing/view/walmart/identifiers/main.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'id'    => 'edit_identifier_button',
            'label' => Mage::helper('M2ePro')->__('Submit'),
            'onclick' => 'ListingGridHandlerObj.editChannelDataHandler.editIdentifier()'
        );
        $buttonBackBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('submit_button', $buttonBackBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}