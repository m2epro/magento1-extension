<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_ModeSwitcher
    extends Ess_M2ePro_Block_Adminhtml_Listing_View_ModeSwitcher_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingViewModeSwitcher');
        // ---------------------------------------

        $this->setData('component_nick', 'ebay');
        $this->setData('component_label', 'eBay');
    }

    protected function getMenuItems()
    {
        $data = array(
            array(
                'value' => $this->getComponentNick(),
                'label' => Mage::helper('M2ePro')->__($this->getComponentLabel())
            ),
            array(
                'value' => 'settings',
                'label' => Mage::helper('M2ePro')->__('Settings')
            ),
            array(
                'value' => 'magento',
                'label' => Mage::helper('M2ePro')->__('Magento')
            )
        );

        return $data;
    }

    //########################################
}
