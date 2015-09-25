<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingTemplateEditTabsGeneral');
        //------------------------------
    }

    // ####################################

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        //------------------------------
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_edit_general_help');
        $this->setChild('help', $helpBlock);
        //------------------------------

        //------------------------------
        $parameters = array(
            'template_nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT,
            'policy_localization' => $this->getData('policy_localization')
        );
        $switcherBlock = $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_ebay_listing_template_switcher','',$parameters);

        $this->setChild('payment', $switcherBlock);
        //------------------------------

        //------------------------------
        $parameters = array(
            'template_nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
            'policy_localization' => $this->getData('policy_localization')
        );
        $switcherBlock = $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_ebay_listing_template_switcher','',$parameters);

        $this->setChild('shipping', $switcherBlock);
        //------------------------------

        //------------------------------
        $parameters = array(
            'template_nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN,
            'policy_localization' => $this->getData('policy_localization')
        );
        $switcherBlock = $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_ebay_listing_template_switcher','',$parameters);

        $this->setChild('return', $switcherBlock);
        //------------------------------

        return $this;
    }

    // ####################################

    protected function _toHtml()
    {
        return parent::_toHtml()
            . $this->getChildHtml('help')
            . $this->getChildHtml('payment')
            . $this->getChildHtml('shipping')
            . $this->getChildHtml('return')
        ;
    }

    // ####################################
}