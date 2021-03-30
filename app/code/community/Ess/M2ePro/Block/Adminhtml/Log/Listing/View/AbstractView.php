<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Log_Listing_View_AbstractView extends
    Ess_M2ePro_Block_Adminhtml_Log_Listing_AbstractView
{
    /** @var Ess_M2ePro_Model_Listing $listing */
    protected $_listing;

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $_listingProduct;

    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->_headerText = '';
        $componentName = Mage::helper('M2ePro/Component_' . $this->getComponentMode())->getTitle();

        if ($this->getListingId()) {
            $listing = $this->getListing();

            $this->_headerText = Mage::helper('M2ePro')->__(
                '%component_name% / M2E Pro Listing "%listing_title%" Logs & Events',
                $componentName, $this->escapeHtml($listing->getTitle())
            );
        } else if ($this->getListingProductId()) {
            $listingProduct = $this->getListingProduct();

            $onlineTitle = $listingProduct->getOnlineTitle();
            if (empty($onlineTitle)) {
                $onlineTitle = $listingProduct->getMagentoProduct()->getName();
            }

            $this->_headerText = Mage::helper('M2ePro')->__(
                '%component_name% / M2E Pro Listing Product "%product_name%" Logs & Events',
                $componentName,
                $this->escapeHtml($onlineTitle)
            );
        }
    }

    //########################################

    protected function getFiltersHtml()
    {
        $params = array(
            'current_view_mode' => $this->getViewMode(),
            'route'             => 'listing'
        );

        if ($this->getListingProductId()) {
            $params['route'] = 'listingProduct';
        }

        $this->_viewModeSwitcherBlock->addData($params);

        $uniqueMessageFilterBlockHtml = '';
        if ($this->getViewMode() == Ess_M2ePro_Block_Adminhtml_Log_Listing_View_ModeSwitcher::VIEW_MODE_SEPARATED) {
            $uniqueMessageFilterBlockHtml = $this->_uniqueMessageFilterBlock->toHtml();
        }

        if ($this->getListingId()) {
            $html = '<div class="static-switcher-block">'
                . $this->getStaticFilterHtml(
                    Mage::helper('M2ePro')->__('Account'),
                    $this->getListing()->getAccount()->getTitle()
                )
                . $this->getStaticFilterHtml(
                    Mage::helper('M2ePro')->__('Marketplace'),
                    $this->getListing()->getMarketplace()->getTitle()
                )
                . '</div>'
                . $uniqueMessageFilterBlockHtml;
        } elseif ($this->getListingProductId()) {
            $html = '<div class="static-switcher-block">'
                . $this->getStaticFilterHtml(
                    Mage::helper('M2ePro')->__('Account'),
                    $this->getListingProduct()->getListing()->getAccount()->getTitle()
                )
                . $this->getStaticFilterHtml(
                    Mage::helper('M2ePro')->__('Marketplace'),
                    $this->getListingProduct()->getListing()->getMarketplace()->getTitle()
                )
                . '</div>';
        } else {
            $html = $this->_accountSwitcherBlock->toHtml()
                . $this->_marketplaceSwitcherBlock->toHtml()
                . $uniqueMessageFilterBlockHtml;
        }

        return $this->_viewModeSwitcherBlock->_toHtml()
            . '<div class="switcher-separator"></div>'
            . $html;
    }

    //########################################

    public function getListingId()
    {
        return $this->getRequest()->getParam(Ess_M2ePro_Block_Adminhtml_Log_AbstractGrid::LISTING_ID_FIELD, false);
    }

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject(
                'Listing', $this->getListingId()
            );
        }

        return $this->_listing;
    }

    //########################################

    public function getListingProductId()
    {
        return $this->getRequest()->getParam(
            Ess_M2ePro_Block_Adminhtml_Log_AbstractGrid::LISTING_PRODUCT_ID_FIELD,
            false
        );
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        if ($this->_listingProduct === null) {
            $this->_listingProduct = Mage::helper('M2ePro/Component')
                ->getUnknownObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    //########################################
}
