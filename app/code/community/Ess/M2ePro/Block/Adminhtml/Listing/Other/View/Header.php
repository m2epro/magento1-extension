<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Other_View_Header extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_template = 'M2ePro/listing/other/view/header.phtml';

    //########################################

    public function getAccountTitle()
    {
        return $this->cutLongLines($this->getAccount()->getTitle());
    }

    public function getMarketplaceTitle()
    {
        return $this->cutLongLines($this->getMarketplace()->getTitle());
    }

    public function getStoreViewBreadcrumb()
    {
        if ($this->getAccount()->isComponentModeEbay()) {
            $relatedStoreId = $this->getAccount()->getChildObject()->getRelatedStoreId(
                $this->getMarketplace()->getId()
            );
        } else {
            $relatedStoreId = $this->getAccount()->getRelatedStoreId();
        }

        $breadcrumb = Mage::helper('M2ePro/Magento_Store')->getStorePath($relatedStoreId);

        return $this->cutLongLines($breadcrumb);
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
     * @return Ess_M2ePro_Model_Account
     */
    private function getAccount()
    {
        return $this->getData('account');
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    private function getMarketplace()
    {
        return $this->getData('marketplace');
    }

    //########################################
}