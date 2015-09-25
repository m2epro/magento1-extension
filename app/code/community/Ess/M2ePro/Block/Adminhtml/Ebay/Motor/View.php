<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View extends Mage_Adminhtml_Block_Widget
{
    private $listingProductId = null;

    private $compatibilityType = null;

    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayMotorView');
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = '';
        //------------------------------
    }

    // ####################################

    public function setListingProductId($listingProductId)
    {
        $this->listingProductId = $listingProductId;
        return $this;
    }

    public function getListingProductId()
    {
        return $this->listingProductId;
    }

    public function setCompatibilityType($compatibilityType)
    {
        $this->compatibilityType = $compatibilityType;
        return $this;
    }

    public function getCompatibilityType()
    {
        return $this->compatibilityType;
    }

    // ####################################

    public function _toHtml()
    {
        $helper = Mage::helper('M2ePro');

        $attribute = $this->getCompatibilityHelper()->getAttribute($this->getCompatibilityType());
        $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($attribute);

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject(
            'Listing_Product', (int)$this->getListingProductId()
        );

        /** @var Ess_M2ePro_Model_Magento_Product $magentoProduct */
        $magentoProduct = $listingProduct->getMagentoProduct();

        $productTitle = $magentoProduct->getName();

        $gridUrl = $this->getUrl(
            '*/adminhtml_ebay_listing/motorViewGrid',
            array('listing_product_id' => $this->getListingProductId(),
                  'compatibility_type' => $this->getCompatibilityType())
        );

        return <<<HTML

<style type="text/css">

    #compatibility_frame_container {
        max-height: 480px;
        overflow-y: scroll;
    }
    #compatibility_frame_container table td, #compatibility_frame_container table th {
        padding: 5px;
    }

</style>

<div id="compatibility_view_container" style="padding-top: 10px;">

    <div style="margin: 2px 0;">
        <span style="font-weight: bold;">{$helper->__('Product')}:</span>&nbsp;
        <span style="color: grey;">{$productTitle}</span>
    </div>

    <div style="margin: 2px 0 6px 0;">
        <span style="font-weight: bold;">{$helper->__('Attribute')}:</span>&nbsp;
        <span style="color: grey;">{$attributeLabel}</span><br/>
    </div>

    <div id="compatibility_frame_container" style="overflow-y: hidden; overflow-x: hidden;">
        <iframe id="compatibility_view_grid_container" src="{$gridUrl}"
                width="100%" height="340px" style="border: none;"></iframe>
    </div>
</div>

<div style="float: right; margin-top: 10px;">
    <a href="javascript:void(0);" onclick="Windows.getFocusedWindow().close()">{$helper->__('Close')}<a/>
</div>

HTML;
    }

    // ####################################

    /**
     * @return Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility
     */
    private function getCompatibilityHelper()
    {
        return Mage::helper('M2ePro/Component_Ebay_Motor_Compatibility');
    }

    // ####################################
}