<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

// move from listing to listing

class Ess_M2ePro_Adminhtml_Ebay_Listing_Other_MovingController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    public function moveToListingGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_moving_grid', '',
            array(
                'grid_url' => $this->getUrl(
                    '*/adminhtml_ebay_listing_other_moving/moveToListingGrid', array('_current'=>true)
                ),
                'moving_handler_js' => 'EbayListingOtherGridObj.movingHandler',
            )
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################
}