<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Moving_FailedProducts extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/listing/moving/failedProducts.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $data = array(
            'id'    => 'failedProducts_continue_button',
            'label' => Mage::helper('M2ePro')->__('Continue'),
            'class' => 'submit'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('failedProducts_continue_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'id'    => 'failedProducts_back_button',
            'label' => Mage::helper('M2ePro')->__('Back'),
            'class' => 'scalable back',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('failedProducts_back_button',$buttonBlock);
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