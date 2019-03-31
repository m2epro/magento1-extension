<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Moving_Grid extends Ess_M2ePro_Block_Adminhtml_Listing_Moving_Grid
{
    //########################################

    protected function getNewListingBtnHtml()
    {
        // ---------------------------------------
        $newListingUrl = $this->getUrl('*/adminhtml_ebay_listing_create/index', array(
            'step' => 1,
            'clear' => 1,
            'account_id' => Mage::helper('M2ePro/Data_Global')->getValue('accountId'),
            'marketplace_id' => Mage::helper('M2ePro/Data_Global')->getValue('marketplaceId'),
            'creation_mode' => Ess_M2ePro_Helper_View::LISTING_CREATION_MODE_LISTING_ONLY
        ));

        $data = array(
            'id'    => 'listingProductMoving_addNew_listing_button',
            'label' => Mage::helper('M2ePro')->__('Add New Listing'),
            'style' => 'float: right;',
            'onclick' => $this->getData('moving_handler_js') . '.startListingCreation(\''.$newListingUrl.'\')'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        // ---------------------------------------

        return $buttonBlock->toHtml();
    }

    //########################################
}