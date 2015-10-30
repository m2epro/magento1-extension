<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Transferring_FailedProducts
    extends Ess_M2ePro_Block_Adminhtml_Listing_Moving_FailedProducts
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/ebay/listing/transferring/failedProducts.phtml');
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'id'    => 'confirm_button_failed_products',
            'label' => Mage::helper('M2ePro')->__('Confirm'),
            'class' => 'submit'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_button',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------

        $this->setChild(
            'failedProducts_grid',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_listing_moving_failedProducts_grid','',
                array('grid_url' => $this->getData('grid_url'))
            )
        );
        // ---------------------------------------

        parent::_beforeToHtml();
    }

    //########################################
}