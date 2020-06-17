<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_View_Header extends Ess_M2ePro_Block_Adminhtml_Widget_Info
{
    //########################################

    protected function _prepareLayout()
    {
        $accountTitle = Mage::helper('M2ePro')->escapeHtml($this->getAccountTitle());
        $storeView = Mage::helper('M2ePro')->escapeHtml($this->getStoreViewBreadcrumb());
        $originalStoreView = Mage::helper('M2ePro')->escapeHtml($this->getStoreViewBreadcrumb(false));

        $accountHtml = $accountTitle;
        if (!$this->getRequest()->getParam('wizard')) {
            $accountUrl = $this->getUrl(
                'M2ePro/adminhtml_' . $this->getAccount()->getComponentMode() . '_account/edit/',
                array('id' => $this->getAccount()->getId())
            );

            $accountHtml = <<<HTML
    <a href="{$accountUrl}" target="_blank">{$accountTitle}</a>
HTML;
        }

        $this->setInfo(
            array(
                array(
                    'label' => Mage::helper('M2ePro')->__('Listing'),
                    'value' => Mage::helper('M2ePro')->escapeHtml($this->getListingTitle())
                ),
                array(
                    'label' => Mage::helper('M2ePro')->__('Account'),
                    'value' => $accountHtml
                ),
                array(
                    'label' => Mage::helper('M2ePro')->__('Marketplace'),
                    'value' => Mage::helper('M2ePro')->escapeHtml($this->getMarketplaceTitle())
                ),
                array(
                    'label' => Mage::helper('M2ePro')->__('Magento Store View'),
                    'value' => <<<HTML
    <span title="{$originalStoreView}">{$storeView}</span>
HTML
                )
            )
        );

        return parent::_prepareLayout();
    }

    //########################################

    public function getListingTitle()
    {
        return $this->cutLongLines($this->getListing()->getTitle());
    }

    public function getAccountTitle()
    {
        return $this->cutLongLines($this->getAccount()->getTitle());
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

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getData('listing');
    }

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getListing()->getAccount();
    }

    //########################################
}
