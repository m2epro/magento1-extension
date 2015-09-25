<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Variation_Product_Manage_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    protected $listingProductId;

    // ####################################

    /**
     * @param mixed $listingProductId
     */
    public function setListingProductId($listingProductId)
    {
        $this->listingProductId = $listingProductId;
    }

    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->listingProductId;
    }

    //------------------------------

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $listingProduct;

    /**
     * @return Ess_M2ePro_Model_Listing_Product|null
     */
    public function getListingProduct()
    {
        if(empty($this->listingProduct)) {
            $this->listingProduct = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->listingProduct;
    }

    //------------------------------

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonVariationProductManageTabs');
        //------------------------------

        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setDestElementId('variation_product_manage_tabs_container');
    }

    //------------------------------

    protected function _beforeToHtml()
    {
        $this->addTab('variations', array(
            'label'   => Mage::helper('M2ePro')->__('Child Products'),
            'title'   => Mage::helper('M2ePro')->__('Child Products'),
            'content' => $this->getLayout()
                ->createBlock('M2ePro/adminhtml_common_amazon_listing_variation_product_manage_tabs_variations')
                ->setListingProductId($this->getListingProductId())
                ->toHtml()
        ));

        $settingsBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_variation_product_manage_tabs_settings')
            ->setListingProductId($this->getListingProductId());
        $settingsBlock->calculateWarnings();

        $settingsBlockLabel = Mage::helper('M2ePro')->__('Settings');
        $settingsBlockTitle = Mage::helper('M2ePro')->__('Settings');

        if(count($settingsBlock->getMessages()) > 0) {
            $iconPath = $this->getSkinUrl('M2ePro/images/'. $settingsBlock->getMessagesType() .'.png');
            $problemIcon = '<img style="vertical-align: middle;" src="'
                . $iconPath . '" alt="" width="16" height="15"> ';
            $settingsBlockLabel = $problemIcon.$settingsBlockLabel;
            $settingsBlockTitle = Mage::helper('M2ePro')->__(
                'Action required.'
            );
        }

        $this->addTab('settings', array(
            'label'   => $settingsBlockLabel,
            'title'   => $settingsBlockTitle,
            'content' => $this->getLayout()
                    ->createBlock('M2ePro/adminhtml_common_amazon_listing_variation_product_manage_tabs_settings')
                    ->setListingProductId($this->getListingProductId())
                    ->toHtml()
        ));

        $this->addTab('vocabulary', array(
            'label'   => Mage::helper('M2ePro')->__('Advanced'),
            'title'   => Mage::helper('M2ePro')->__('Advanced'),
            'content' => $this->getLayout()
                ->createBlock('M2ePro/adminhtml_common_amazon_listing_variation_product_manage_tabs_vocabulary')
                ->setListingProductId($this->getListingProductId())
                ->toHtml()
        ));

        $generalId = $this->getListingProduct()->getChildObject()->getGeneralId();
        if(empty($generalId) && $this->getListingProduct()->getChildObject()->isGeneralIdOwner()) {
            $this->setActiveTab('settings');
        } else {
            $this->setActiveTab('variations');
        }

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        $generalId = $this->getListingProduct()->getChildObject()->getGeneralId();

        $showMask = 0;
        if(!(empty($generalId) && $this->getListingProduct()->getChildObject()->isGeneralIdOwner())) {
            $showMask = 1;
        }

        $data = array(
            'style' => 'float: right; margin-top: 7px; ',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'ListingGridHandlerObj.variationProductManageHandler.closeManageVariationsPopup()'
        );
        $closeBtn = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);

        $additionalJavascript = <<<JAVASCRIPT
<script type="text/javascript">
    amazonVariationProductManageTabsJsTabs.moveTabContentInDest();

    if (!{$showMask}) {
        amazonVariationProductManageTabsJsTabs.tabs[0].hide();
    }

    ListingGridHandlerObj.variationProductManageHandler.loadVariationsGrid({$showMask});
</script>
JAVASCRIPT;

        return parent::_toHtml() .
            '<div id="variation_product_manage_tabs_container"></div>' .
            $additionalJavascript .
            $closeBtn->toHtml();
    }

    // ####################################
}