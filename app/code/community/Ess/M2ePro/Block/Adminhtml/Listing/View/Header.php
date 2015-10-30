<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_View_Header extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_template = 'M2ePro/listing/view/header.phtml';

    //########################################

    public function getComponent()
    {
        if ($this->getListing()->isComponentModeEbay()) {
            return Mage::helper('M2ePro')->__('eBay');
        }

        if ($this->getListing()->isComponentModeAmazon()) {
            return Mage::helper('M2ePro')->__('Amazon');
        }

        if ($this->getListing()->isComponentModeBuy()) {
            return Mage::helper('M2ePro')->__('Rakuten');
        }

        return '';
    }

    public function getProfileTitle()
    {
        return $this->cutLongLines($this->getListing()->getTitle());
    }

    public function getAccountTitle()
    {
        return $this->cutLongLines($this->getListing()->getAccount()->getTitle());
    }

    public function getMarketplaceTitle()
    {
        return $this->cutLongLines($this->getListing()->getMarketplace()->getTitle());
    }

    public function getStoreViewBreadcrumb($cutLongValues = true)
    {
        $breadcrumb = Mage::helper('M2ePro/Magento_Store')->getStorePath($this->getListing()->getStoreId());

        return $cutLongValues ? $this->cutLongLines($breadcrumb) : $breadcrumb;
    }

    //########################################

    private function cutLongLines($line)
    {
        if (strlen($line) < 50) {
            return $line;
        }

        return substr($line, 0, 50) . '...';
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    private function getListing()
    {
        return $this->getData('listing');
    }

    //########################################
}