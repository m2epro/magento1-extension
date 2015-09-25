<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Product_Variation_Manage
    extends Ess_M2ePro_Block_Adminhtml_Common_Listing_Product_Variation
{
    //##############################################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingProductVariationEdit');
        //------------------------------

        $this->setTemplate('M2ePro/common/listing/product/variation/manage.phtml');
    }

    //##############################################################

    protected function _beforeToHtml()
    {
        $this->_prepareButtons();

        return parent::_beforeToHtml();
    }

    //##############################################################

    protected function _prepareButtons()
    {
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('M2ePro')->__('Add Another Variation'),
                'onclick' => '',
                'class' => 'add',
                'id' => 'add_more_variation_button'
            ));
        $this->setChild('add_more_variation_button', $buttonBlock);

        //------------------------------

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('M2ePro')->__('Confirm'),
                'onclick' => '',
                'class' => 'confirm',
                'id' => 'variation_manage_confirm'
            ));
        $this->setChild('variation_manage_confirm', $buttonBlock);

        //------------------------------

        $onClick = 'ListingProductVariationHandlerObj.manageGenerateAction(false);';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('M2ePro')->__('Generate All Variations'),
                'onclick' => $onClick,
                'class' => 'button',
                'id' => 'variation_manage_generate_all'
            ));
        $this->setChild('variation_manage_generate_all', $buttonBlock);

        $onClick = 'ListingProductVariationHandlerObj.manageGenerateAction(true);';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('M2ePro')->__('Generate Non-Existing Variations'),
                'onclick' => $onClick,
                'class' => 'button',
                'id' => 'variation_manage_generate_unique'
            ));
        $this->setChild('variation_manage_generate_unique', $buttonBlock);
    }

    //##############################################################

    public function getComponentTitle()
    {
        $component = $this->getComponent();
        return Mage::helper('M2ePro/Component_' . ucfirst($component))->getChannelTitle();
    }

    //##############################################################
}