<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Transferring_FailedProducts
    extends Ess_M2ePro_Block_Adminhtml_Listing_Moving_FailedProducts
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/ebay/listing/transferring/failedProducts.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $data = array(
            'id'    => 'confirm_button_failed_products',
            'label' => Mage::helper('M2ePro')->__('Confirm'),
            'class' => 'submit'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_button',$buttonBlock);
        //------------------------------

        //------------------------------

        $this->setChild(
            'failedProducts_grid',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_listing_moving_failedProducts_grid','',
                array('grid_url' => $this->getData('grid_url'))
            )
        );
        //------------------------------

        parent::_beforeToHtml();
    }

    // ####################################
}