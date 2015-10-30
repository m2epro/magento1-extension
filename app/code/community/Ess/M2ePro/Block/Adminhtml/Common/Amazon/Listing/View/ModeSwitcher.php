<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_View_ModeSwitcher
    extends Ess_M2ePro_Block_Adminhtml_Listing_View_ModeSwitcher_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingViewModeSwitcher');
        // ---------------------------------------

        $this->setData('component_nick', 'amazon');
        $this->setData('component_label', 'Amazon');
    }

    protected function getMenuItems()
    {
        return array(
            array(
                'value' => Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_View::VIEW_MODE_AMAZON,
                'label' => Mage::helper('M2ePro')->__($this->getComponentLabel())
            ),
            array(
                'value' => Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_View::VIEW_MODE_SETTINGS,
                'label' => Mage::helper('M2ePro')->__('Settings')
            ),
            array(
                'value' => Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_View::VIEW_MODE_SELLERCENTRAL,
                'label' => Mage::helper('M2ePro')->__('Seller Сentral')
            ),
            array(
                'value' => Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_View::VIEW_MODE_MAGENTO,
                'label' => Mage::helper('M2ePro')->__('Magento')
            )
        );
    }

    //########################################
}