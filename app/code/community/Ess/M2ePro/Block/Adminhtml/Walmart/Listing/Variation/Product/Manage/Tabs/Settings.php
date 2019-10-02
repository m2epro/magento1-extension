<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Variation_Product_Manage_Tabs_Settings
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    const MESSAGE_TYPE_ERROR = 'error';
    const MESSAGE_TYPE_WARNING = 'warning';

    protected $_warningsCalculated = false;

    protected $_childListingProducts;
    protected $_currentProductVariations;

    protected $_messages = array();

    // ---------------------------------------

    protected $_listingProductId;

    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct;

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $_listingProductTypeModel;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/walmart/listing/variation/product/manage/tabs/settings.phtml');
    }

    //########################################

    /**
     * @param mixed $listingProductId
     * @return $this
     */
    public function setListingProductId($listingProductId)
    {
        $this->_listingProductId = $listingProductId;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->_listingProductId;
    }

    // ---------------------------------------

    /**
     * @param array $message
     */
    public function addMessage($message, $type = self::MESSAGE_TYPE_ERROR)
    {
        $this->_messages[] = array(
            'type' => $type,
            'msg' => $message
        );
    }
    /**
     * @param array $messages
     */
    public function setMessages($messages)
    {
        $this->_messages = $messages;
    }
    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    public function getMessagesType()
    {
        $type = self::MESSAGE_TYPE_WARNING;
        foreach ($this->_messages as $message) {
            if ($message['type'] === self::MESSAGE_TYPE_ERROR)     {
                $type = $message['type'];
                break;
            }
        }

        return $type;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product|null
     */
    public function getListingProduct()
    {
        if ($this->_listingProduct === null) {
            $this->_listingProduct = Mage::helper('M2ePro/Component_Walmart')
                                         ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent|null
     */
    public function getListingProductTypeModel()
    {
        if ($this->_listingProductTypeModel === null) {
            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
            $walmartListingProduct = $this->getListingProduct()->getChildObject();
            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent $typeModel */
            $this->_listingProductTypeModel = $walmartListingProduct->getVariationManager()->getTypeModel();
        }

        return $this->_listingProductTypeModel;
    }

    // ---------------------------------------

    /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Matcher_Attribute $matcherAttribute */
    protected $matcherAttributes;

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Matcher_Attribute
     */
    public function getMatcherAttributes()
    {
        if (empty($this->matcherAttributes)) {
            $this->matcherAttributes = Mage::getModel('M2ePro/Walmart_Listing_Product_Variation_Matcher_Attribute');
            $this->matcherAttributes->setMagentoProduct($this->getListingProduct()->getMagentoProduct());
            $this->matcherAttributes->setDestinationAttributes($this->getDestinationAttributes());
        }

        return $this->matcherAttributes;
    }

    // ---------------------------------------

    public function getWarnings()
    {
        $warnings = '';
        foreach ($this->getMessages() as $message) {
            $warnings .= <<<HTML
<li class="{$message['type']}-msg">
    <ul>
        <li>{$message['msg']}</li>
    </ul>
</li>
HTML;
        }

        return $warnings;
    }

    public function calculateWarnings()
    {
        if (!$this->_warningsCalculated) {
            $this->_warningsCalculated = true;

            if (!$this->getListingProductTypeModel()->hasChannelAttributes()) {
                $this->addMessage(
                    Mage::helper('M2ePro')->__(
                        'Walmart Item Variations are not defined. To start configurations, click Set Attributes.'
                    ),
                    self::MESSAGE_TYPE_ERROR
                );
            } else if (!$this->hasMatchedAttributes()) {
                $this->addMessage(
                    Mage::helper('M2ePro')->__(
                        'Item Variations cannot be added/updated on the Channel. The correspondence between Magento
                        Variational Attribute(s) and Walmart Variant Attribute(s) is not set.
                        Please complete the configurations.'
                    ),
                    self::MESSAGE_TYPE_ERROR
                );
            }
        }
    }

    // ---------------------------------------

    protected function _beforeToHtml()
    {
        $this->calculateWarnings();

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        $vocabularyAttributesBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_variation_product_vocabularyAttributesPopup'
        );

        $vocabularyOptionsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_variation_product_vocabularyOptionsPopup'
        );

        return $vocabularyAttributesBlock->toHtml() . $vocabularyOptionsBlock->toHtml() . parent::_toHtml();
    }

    //########################################

    public function isInAction()
    {
        $processingLocks = $this->getListingProduct()->getProcessingLocks();
        return !empty($processingLocks);
    }

    // ---------------------------------------

    public function getProductAttributes()
    {
        return $this->getListingProductTypeModel()->getProductAttributes();
    }

    // ---------------------------------------

    public function getDescriptionTemplateLink()
    {
        $url = $this->getUrl(
            '*/adminhtml_walmart_template_description/edit', array(
            'id' => $this->getListingProduct()->getChildObject()->getTemplateDescriptionId()
            )
        );

        $templateTitle = $this->getListingProduct()->getChildObject()->getDescriptionTemplate()->getTitle();

        return <<<HTML
<a href="{$url}" target="_blank" title="{$templateTitle}" >{$templateTitle}</a>
HTML;
    }

    // ---------------------------------------

    public function getPossibleAttributes()
    {
        $possibleAttributes = Mage::getModel('M2ePro/Walmart_Marketplace_Details')
            ->setMarketplaceId($this->getListingProduct()->getMarketplace()->getId())
            ->getVariationAttributes(
                $this->getListingProduct()->getChildObject()->getCategoryTemplate()->getProductDataNick()
            );

        return $possibleAttributes;
    }

    // ---------------------------------------

    public function getSwatchImagesAttribute()
    {
        return $this->getListingProduct()
                    ->getSetting('additional_data', 'variation_swatch_images_attribute');
    }

    // ---------------------------------------

    public function hasMatchedAttributes()
    {
        return $this->getListingProductTypeModel()->hasMatchedAttributes();
    }

    public function getMatchedAttributes()
    {
        if ($this->hasMatchedAttributes()) {
            return $this->getListingProductTypeModel()->getMatchedAttributes();
        }

        return $this->getMatcherAttributes()->getMatchedAttributes();
    }

    public function getDestinationAttributes()
    {
        return $this->getListingProductTypeModel()->getChannelAttributes();
    }

    // ---------------------------------------

    public function getVirtualAttributes()
    {
        $typeModel = $this->getListingProductTypeModel();

        if ($virtualProductAttributes = $typeModel->getVirtualProductAttributes()) {
            return $virtualProductAttributes;
        }

        if ($virtualChannelAttributes = $typeModel->getVirtualChannelAttributes()) {
            return $virtualChannelAttributes;
        }

        return array();
    }

    public function getVirtualProductAttributes()
    {
        $typeModel = $this->getListingProductTypeModel();

        if ($virtualProductAttributes = $typeModel->getVirtualProductAttributes()) {
            return $virtualProductAttributes;
        }

        return array();
    }

    public function getVirtualChannelAttributes()
    {
        $typeModel = $this->getListingProductTypeModel();

        if ($virtualChannelAttributes = $typeModel->getVirtualChannelAttributes()) {
            return $virtualChannelAttributes;
        }

        return array();
    }

    // ---------------------------------------

    public function isChangeMatchedAttributesAllowed()
    {
        if ($this->isInAction() ) {
            return false;
        }

        if ($this->hasMatchedAttributes()) {
            $typeModel = $this->getListingProductTypeModel();

            $realMatchedAttributes = $typeModel->getRealMatchedAttributes();

            if (count($realMatchedAttributes) === 1) {
                return false;
            }
        }

        return true;
    }

    //########################################

    public function getChildListingProducts()
    {
        if ($this->_childListingProducts !== null) {
            return $this->_childListingProducts;
        }

        return $this->_childListingProducts = $this->getListingProductTypeModel()->getChildListingsProducts();
    }

    public function getCurrentProductVariations()
    {
        if ($this->_currentProductVariations !== null) {
            return $this->_currentProductVariations;
        }

        $magentoProductVariations = $this->getListingProduct()
            ->getMagentoProduct()
            ->getVariationInstance()
            ->getVariationsTypeStandard();

        $productVariations = array();

        foreach ($magentoProductVariations['variations'] as $option) {
            $productOption = array();

            foreach ($option as $attribute) {
                $productOption[$attribute['attribute']] = $attribute['option'];
            }

            $productVariations[] = $productOption;
        }

        return $this->_currentProductVariations = $productVariations;
    }

    public function getCurrentChannelVariations()
    {
        return $this->getListingProductTypeModel()->getChannelVariations();
    }

    // ---------------------------------------

    public function getWalmartVariationsSet()
    {
        $variations = $this->getCurrentChannelVariations();

        if (empty($variations)) {
            return false;
        }

        $attributesOptions = array();

        foreach ($variations as $variation) {
            foreach ($variation as $attr => $option) {
                if (!isset($attributesOptions[$attr])) {
                    $attributesOptions[$attr] = array();
                }

                if (!in_array($option, $attributesOptions[$attr])) {
                    $attributesOptions[$attr][] = $option;
                }
            }
        }

        ksort($attributesOptions);

        return $attributesOptions;
    }

    // ---------------------------------------

    public function getUsedChannelVariations()
    {
        return $this->getListingProductTypeModel()->getUsedChannelOptions();
    }

    public function getUsedProductVariations()
    {
        return $this->getListingProductTypeModel()->getUsedProductOptions();
    }

    // ---------------------------------------

    public function getUnusedProductVariations()
    {
        return $this->getListingProductTypeModel()->getUnusedProductOptions();
    }

    // ---------------------------------------

    public function hasUnusedProductVariation()
    {
        return (bool)$this->getUnusedProductVariations();
    }

    // ---------------------------------------

    public function hasChildWithEmptyProductOptions()
    {
        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationProductMatched()) {
                return true;
            }
        }

        return false;
    }

    //########################################
}
