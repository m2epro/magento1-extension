<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Variation_Product_Manage_Tabs_Settings
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    const MESSAGE_TYPE_ERROR = 'error';
    const MESSAGE_TYPE_WARNING = 'warning';

    protected $_messages           = array();
    protected $_warningsCalculated = false;

    protected $_channelThemes;
    protected $_childListingProducts;
    protected $_currentProductVariations;

    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute $matcherAttribute */
    protected $_matcherAttributes;

    protected $_listingProductId;
    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct;
    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $_listingProductTypeModel;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/amazon/listing/variation/product/manage/tabs/settings.phtml');
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
            if ($message['type'] === self::MESSAGE_TYPE_ERROR) {
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
            $this->_listingProduct = Mage::helper('M2ePro/Component_Amazon')
                                         ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent|null
     */
    public function getListingProductTypeModel()
    {
        if ($this->_listingProductTypeModel === null) {
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $this->getListingProduct()->getChildObject();
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $typeModel */
            $this->_listingProductTypeModel = $amazonListingProduct->getVariationManager()->getTypeModel();
        }

        return $this->_listingProductTypeModel;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute
     */
    public function getMatcherAttributes()
    {
        if (empty($this->_matcherAttributes)) {
            $this->_matcherAttributes = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Attribute');
            $this->_matcherAttributes->setMagentoProduct($this->getListingProduct()->getMagentoProduct());
            $this->_matcherAttributes->setDestinationAttributes($this->getDestinationAttributes());
        }

        return $this->_matcherAttributes;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isNotExistsMissedProductTypeTemplate()
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->getListingProduct()->getChildObject();
        $isExistProductTypeTemplate = $amazonListingProduct->isExistProductTypeTemplate();

        return $this->isGeneralIdOwner() && !$isExistProductTypeTemplate;
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

            if ($this->isNotExistsMissedProductTypeTemplate()) {
                $url = 'https://help.m2epro.com/support/solutions/articles/9000226190';

                $this->addMessage(
                    Mage::helper('M2ePro')
                        ->__(
                            'Variation Theme is not set. Please assign a Product Type to the Item first and then select 
                            the Variation Theme. Follow the steps in
                            <a href="%url%" target="_blank" class="external-link">this article</a>.',
                            array('url' => $url)
                        ),
                    self::MESSAGE_TYPE_ERROR
                );

                return;
            }

            if (!$this->hasGeneralId() && $this->isGeneralIdOwner()) {
                if (!$this->hasChannelTheme() || !$this->hasMatchedAttributes()) {
                    $this->addMessage(
                        Mage::helper('M2ePro')
                            ->__(
                                'Creation of New Parent-Child Product is impossible because Variation Theme
                                  or correspondence between Magento Product Attributes and Amazon Product Attributes
                                  was not set. Please, specify a Variation Theme or correspondence between
                                  Variation Attributes.'
                            ),
                        self::MESSAGE_TYPE_ERROR
                    );
                }
            } elseif ($this->hasGeneralId()) {
                if (!$this->hasMatchedAttributes()) {
                    $this->addMessage(
                        Mage::helper('M2ePro')->__(
                            'Item Variations cannot be added/updated on Amazon. The correspondence between Magento
                            Variational Attribute(s) and Amazon Variational Attribute(s) is not set.
                            Please complete the configurations.'
                        ),
                        self::MESSAGE_TYPE_ERROR
                    );
                }

                if ($this->isGeneralIdOwner() && !$this->hasChannelTheme()) {
                    $this->addMessage(
                        Mage::helper('M2ePro')->__(
                            'Creation of New Amazon Child Products feature is temporary unavailable because
                             Variation Theme was not set. Please, specify Variation Theme.'
                        ),
                        self::MESSAGE_TYPE_WARNING
                    );
                }
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
            'M2ePro/adminhtml_amazon_listing_variation_product_vocabularyAttributesPopup'
        );

        $vocabularyOptionsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_listing_variation_product_vocabularyOptionsPopup'
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

    public function showGeneralIdActions()
    {
        return !$this->getListingProduct()->getMagentoProduct()->isBundleType() &&
               !$this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions() &&
               !$this->getListingProduct()->getMagentoProduct()->isDownloadableTypeWithSeparatedLinks();
    }

    // ---------------------------------------

    public function hasGeneralId()
    {
        return $this->getListingProduct()->getChildObject()->getGeneralId() !== null;
    }

    public function getGeneralId()
    {
        return $this->getListingProduct()->getChildObject()->getGeneralId();
    }

    public function getGeneralIdLink()
    {
        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
            $this->getGeneralId(),
            $this->getListingProduct()->getListing()->getMarketplaceId()
        );

        return <<<HTML
<a href="{$url}" target="_blank" title="{$this->getGeneralId()}" >{$this->getGeneralId()}</a>
HTML;
    }

    public function isGeneralIdOwner()
    {
        return $this->getListingProduct()->getChildObject()->isGeneralIdOwner();
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getProductTypeLink()
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->getListingProduct()->getChildObject();
        if (!$amazonListingProduct->isExistProductTypeTemplate()) {
            throw new \LogicException('Product Type is not set.');
        }

        $url = $this->getUrl(
            '*/adminhtml_amazon_productType/edit', array(
                'id' => $amazonListingProduct->getTemplateProductTypeId()
            )
        );

        $productTypeTitle = $amazonListingProduct->getProductTypeTemplate()
                                                 ->getTitle();
        return <<<HTML
<a href="{$url}" target="_blank" title="{$productTypeTitle}" >{$productTypeTitle}</a>
HTML;
    }

    // ---------------------------------------

    public function hasChannelTheme()
    {
        return $this->getListingProductTypeModel()->hasChannelTheme();
    }

    public function getChannelTheme()
    {
        return $this->getListingProductTypeModel()->getChannelTheme();
    }

    public function getChannelThemes()
    {
        if ($this->_channelThemes !== null) {
            return $this->_channelThemes;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->getListingProduct()->getChildObject();
        $productTypeTemplate = $amazonListingProduct->getProductTypeTemplate();

        if (!$productTypeTemplate) {
            return array();
        }

        $marketPlaceId = $this->getListingProduct()->getListing()->getMarketplaceId();

        $productTypeDictionary = $productTypeTemplate->getDictionary();

        $channelThemes = $productTypeDictionary->getVariationThemes();

        $variationHelper = Mage::helper('M2ePro/Component_Amazon_Variation');
        $themesUsageData = $variationHelper->getThemesUsageData();
        $usedThemes = array();

        if (!empty($themesUsageData[$marketPlaceId])) {
            foreach ($themesUsageData[$marketPlaceId] as $theme => $count) {
                if (!empty($channelThemes[$theme])) {
                    $usedThemes[$theme] = $channelThemes[$theme];
                }
            }
        }

        return $this->_channelThemes = array_merge($usedThemes, $channelThemes);
    }

    public function getChannelThemeAttr()
    {
        $theme = $this->getChannelTheme();
        $themes = $this->getChannelThemes();

        if (!empty($themes[$theme])) {
            return $themes[$theme]['attributes'];
        }

        return null;
    }

    public function getChannelThemeNote()
    {
        $theme = $this->getChannelTheme();
        $themes = $this->getChannelThemes();

        if (!isset($themes[$theme]) || !isset($themes[$theme]['note'])) {
            return null;
        }

        if (!empty($themes[$theme])) {
            return $themes[$theme]['note'];
        }

        return null;
    }

    public function getChannelThemeAttrString()
    {
        $themesAttributes = $this->getChannelThemeAttr();

        if (!empty($themesAttributes)) {
            return implode(', ', $themesAttributes);
        }

        return Mage::helper('M2ePro')->__('Variation Theme not found.');
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
        if (!$this->hasGeneralId() && $this->isGeneralIdOwner() && $this->hasChannelTheme()) {
            return $this->getChannelThemeAttr();
        }

        return array_keys($this->getListingProductTypeModel()->getChannelAttributesSets());
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

    public function getAmazonVariationsSet()
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

    public function getUnusedChannelVariations()
    {
        return $this->getListingProductTypeModel()->getUnusedChannelOptions();
    }

    // ---------------------------------------

    public function hasUnusedProductVariation()
    {
        return (bool)$this->getUnusedProductVariations();
    }

    public function hasUnusedChannelVariations()
    {
        return (bool)$this->getUnusedChannelVariations();
    }

    // ---------------------------------------

    public function hasChildWithEmptyProductOptions()
    {
        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationProductMatched()) {
                return true;
            }
        }

        return false;
    }

    public function hasChildWithEmptyChannelOptions()
    {
        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationChannelMatched()) {
                return true;
            }
        }

        return false;
    }

    //########################################
}
