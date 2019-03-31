<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_View_ModeSwitcher
    extends Ess_M2ePro_Block_Adminhtml_Listing_View_ModeSwitcher_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListingViewModeSwitcher');
        // ---------------------------------------

        $this->setData('component_nick', 'walmart');
        $this->setData('component_label', 'Walmart');
    }

    protected function getMenuItems()
    {
        return array(
            array(
                'value' => Ess_M2ePro_Block_Adminhtml_Walmart_Listing_View::VIEW_MODE_WALMART,
                'label' => Mage::helper('M2ePro')->__($this->getComponentLabel())
            ),
            array(
                'value' => Ess_M2ePro_Block_Adminhtml_Walmart_Listing_View::VIEW_MODE_SETTINGS,
                'label' => Mage::helper('M2ePro')->__('Settings')
            ),
            array(
                'value' => Ess_M2ePro_Block_Adminhtml_Walmart_Listing_View::VIEW_MODE_MAGENTO,
                'label' => Mage::helper('M2ePro')->__('Magento')
            )
        );
    }

    //########################################
}