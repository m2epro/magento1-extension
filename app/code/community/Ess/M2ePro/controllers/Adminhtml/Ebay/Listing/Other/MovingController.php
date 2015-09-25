<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

// move from listing to listing

class Ess_M2ePro_Adminhtml_Ebay_Listing_Other_MovingController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //#############################################

    public function moveToListingGridAction()
    {
        Mage::helper('M2ePro/Data_Global')->setValue(
            'componentMode', $this->getRequest()->getParam('componentMode')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'accountId', $this->getRequest()->getParam('accountId')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'marketplaceId', $this->getRequest()->getParam('marketplaceId')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'ignoreListings', json_decode($this->getRequest()->getParam('ignoreListings'))
        );

        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_moving_grid','',
            array(
                'grid_url' => $this->getUrl(
                    '*/adminhtml_ebay_listing_other_moving/moveToListingGrid',array('_current'=>true)
                ),
                'moving_handler_js' => 'EbayListingOtherGridHandlerObj.movingHandler',
            )
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################
}