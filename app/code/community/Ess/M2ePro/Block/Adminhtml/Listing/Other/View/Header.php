<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Other_View_Header extends Ess_M2ePro_Block_Adminhtml_Widget_Info
{
    //########################################

    protected function _prepareLayout()
    {
        $accountUrl = $this->getUrl(
            'M2ePro/adminhtml_' . $this->getAccount()->getComponentMode() . '_account/edit/',
            array('id' => $this->getAccount()->getId())
        );
        $accountTitle = Mage::helper('M2ePro')->escapeHtml($this->getAccountTitle());
        $storeView = Mage::helper('M2ePro')->escapeHtml($this->getStoreViewBreadcrumb());
        $originalStoreView = Mage::helper('M2ePro')->escapeHtml($this->getStoreViewBreadcrumb(false));

        $this->setInfo(
            array(
                array(
                    'label' => Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->__('Account')),
                    'value' => <<<HTML
    <a href="{$accountUrl}" target="_blank">{$accountTitle}</a>
HTML
                ),
                array(
                    'label' => Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->__('Marketplace')),
                    'value' => Mage::helper('M2ePro')->escapeHtml($this->getMarketplaceTitle())
                ),
                array(
                    'label' => Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->__('Magento Store View')),
                    'value' => <<<HTML
    <span title="{$originalStoreView}">{$storeView}</span>
HTML
                )
            )
        );

        return parent::_prepareLayout();
    }

    //########################################

    public function getAccountTitle()
    {
        return $this->cutLongLines($this->getAccount()->getTitle());
    }

    public function getMarketplaceTitle()
    {
        return $this->cutLongLines($this->getMarketplace()->getTitle());
    }

    public function getStoreViewBreadcrumb($cutLongValues = true)
    {
        if ($this->getAccount()->isComponentModeEbay()) {
            $relatedStoreId = $this->getAccount()->getChildObject()->getRelatedStoreId(
                $this->getMarketplace()->getId()
            );
        } else {
            $relatedStoreId = $this->getAccount()->getChildObject()->getRelatedStoreId();
        }

        $breadcrumb = Mage::helper('M2ePro/Magento_Store')->getStorePath($relatedStoreId);

        return $cutLongValues ? $this->cutLongLines($breadcrumb) : $breadcrumb;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getData('account');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getData('marketplace');
    }

    //########################################
}
