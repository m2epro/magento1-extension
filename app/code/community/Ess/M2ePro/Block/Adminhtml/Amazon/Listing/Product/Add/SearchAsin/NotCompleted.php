<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Add_SearchAsin_NotCompleted
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('searchAsinNotCompletedPopup');
        $this->setTemplate('M2ePro/amazon/listing/product/add/search_asin/not_completed.phtml');
    }

    protected function _beforeToHtml()
    {
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'Windows.getFocusedWindow().close();'
        );
        $closeButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('closeBtn', $closeButton);
    }

    //########################################
}
