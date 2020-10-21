<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Variation_Product_Manage_Tabs
    extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    protected $_listingProductId;

    protected $_errorsCount;

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $_listingProduct;

    //########################################

    /**
     * @param mixed $listingProductId
     */
    public function setListingProductId($listingProductId)
    {
        $this->_listingProductId = $listingProductId;
    }

    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->_listingProductId;
    }

    // ---------------------------------------

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $listingProduct;

    /**
     * @return Ess_M2ePro_Model_Listing_Product|null
     */
    public function getListingProduct()
    {
        if (empty($this->_listingProduct)) {
            $this->_listingProduct = Mage::helper('M2ePro/Component_Walmart')
                ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    // ---------------------------------------

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartVariationProductManageTabs');

        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setDestElementId('variation_product_manage_tabs_container');
    }

    // ---------------------------------------

    protected function _beforeToHtml()
    {
        $this->addTab(
            'variations', array(
            'label'   => Mage::helper('M2ePro')->__('Child Products'),
            'title'   => Mage::helper('M2ePro')->__('Child Products'),
            'content' => $this->getLayout()
                ->createBlock('M2ePro/adminhtml_walmart_listing_variation_product_manage_tabs_variations')
                ->setListingProductId($this->getListingProductId())
                ->setListingProductIdForFilter($this->getRequest()->getParam('listing_product_id_filter'))
                ->toHtml()
            )
        );

        $settingsBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_listing_variation_product_manage_tabs_settings')
            ->setListingProductId($this->getListingProductId());
        $settingsBlock->calculateWarnings();
        $this->_errorsCount = count($settingsBlock->getMessages());

        $settingsBlockLabel = Mage::helper('M2ePro')->__('Settings');
        $settingsBlockTitle = Mage::helper('M2ePro')->__('Settings');

        $iconPath = $this->getSkinUrl('M2ePro/images/'. $settingsBlock->getMessagesType() .'.png');
        $iconTitle = Mage::helper('M2ePro')->__('Action required.');
        $iconStyle = 'vertical-align: middle;';

        if ($this->_errorsCount == 0) {
            $iconStyle .= 'display:none;';
        }

        $problemIcon = <<<HTML
<img style="{$iconStyle}" src="{$iconPath}" title="{$iconTitle}" alt="" width="16" height="15">
HTML;

        $this->addTab(
            'settings', array(
            'label'   => $problemIcon . $settingsBlockLabel,
            'title'   => $settingsBlockTitle,
            'content' => $this->getLayout()
                    ->createBlock('M2ePro/adminhtml_walmart_listing_variation_product_manage_tabs_settings')
                    ->setListingProductId($this->getListingProductId())
                    ->toHtml()
            )
        );

        $this->addTab(
            'vocabulary', array(
            'label'   => Mage::helper('M2ePro')->__('Advanced'),
            'title'   => Mage::helper('M2ePro')->__('Advanced'),
            'content' => $this->getLayout()
                ->createBlock('M2ePro/adminhtml_walmart_listing_variation_product_manage_tabs_vocabulary')
                ->setListingProductId($this->getListingProductId())
                ->toHtml()
            )
        );

        if ($this->_errorsCount > 0) {
            $this->setActiveTab('settings');
        } else {
            $this->setActiveTab('variations');
        }

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        $data = array(
            'style' => 'float: right; margin-top: 7px; ',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'ListingGridObj.variationProductManageHandler.closeManageVariationsPopup()'
        );
        $closeBtn = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);

        $additionalJavascript = <<<HTML
<script type="text/javascript">
    walmartVariationProductManageTabsJsTabs.moveTabContentInDest();

    ListingGridObj.variationProductManageHandler.loadVariationsGrid(true);
</script>
HTML;

        return parent::_toHtml() .
            '<div id="variation_product_manage_tabs_container"></div>' .
            $additionalJavascript .
            $closeBtn->toHtml();
    }

    //########################################
}
