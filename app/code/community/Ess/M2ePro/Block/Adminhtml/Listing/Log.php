<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Listing_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /** @var Ess_M2ePro_Model_Listing $listing */
    protected $_listing = null;

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $_listingProduct = null;
    
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId($this->getComponentMode() . 'ListingLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_' . $this->getComponentMode() . '_listing_log_view_' . $this->getViewMode();

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

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->setTemplate('M2ePro/listing/log.phtml');
    }

    //########################################

    public function getListingId()
    {
        return $this->getRequest()->getParam('id', false);
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
        return $this->getRequest()->getParam('listing_product_id', false);
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

    // ---------------------------------------

    public function getViewMode()
    {
        if ($viewMode = $this->getRequest()->getParam('view_mode', false)) {
            Mage::helper('M2ePro/Module_Log')->setViewMode(
                $this->getComponentMode() . '_log_listing_view_mode',
                $viewMode
            );

            return $viewMode;
        }

        return Mage::helper('M2ePro/Module_Log')->getViewMode(
            $this->getComponentMode() . '_log_listing_view_mode'
        );
    }

    //########################################

    public function getFilterBlock()
    {
        $params = array(
            'current_view_mode' => $this->getViewMode(),
            'route'             => 'listing'
        );

        if ($this->getListingProductId()) {
            $params['route'] = 'listingProduct';
        }

        $viewModeSwitcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_log_listing_view_modeSwitcher');
        $viewModeSwitcherBlock->addData($params);

        $html = $viewModeSwitcherBlock->_toHtml();

        $html .= '<div class="switcher-separator"></div>';

        if ($this->getListingId()) {
            $html .= '<div class="static-switcher-block">'
                . $this->getStaticFilterHtml(
                    Mage::helper('M2ePro')->__('Account'),
                    $this->getListing()->getAccount()->getTitle()
                )
                . $this->getStaticFilterHtml(
                    Mage::helper('M2ePro')->__('Marketplace'),
                    $this->getListing()->getMarketplace()->getTitle()
                )
                . '</div>';
        } elseif ($this->getListingProductId()) {
            $html .= '<div class="static-switcher-block">'
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
            $accountFilterBlock = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_account_switcher', '', array(
                    'component_mode'  => $this->getComponentMode(),
                    'controller_name' => 'adminhtml_' . $this->getComponentMode() . '_log'
                )
            );
            $accountFilterBlock->setUseConfirm(false);

            $marketplaceFilterBlock = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_marketplace_switcher', '', array(
                    'component_mode'  => $this->getComponentMode(),
                    'controller_name' => 'adminhtml_' . $this->getComponentMode() . '_log'
                )
            );
            $marketplaceFilterBlock->setUseConfirm(false);

            $html .= $accountFilterBlock->_toHtml()
                . $marketplaceFilterBlock->_toHtml();
        }

        return $html;
    }

    //########################################

    protected function getStaticFilterHtml($label, $value)
    {
        return <<<HTML
<p class="static-switcher">
    <span>{$label}:</span>
    <span>{$value}</span>
</p>
HTML;
    }

    //########################################

    abstract public function getComponentMode();

    //########################################
}
