<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Amazon_Listing_Variation_Product_ManageController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Listing/ProductGrid.js')

            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/Listing/Grid.js')
            ->addJs('M2ePro/Amazon/Listing/Grid.js')

            ->addJs('M2ePro/Action.js')
            ->addJs('M2ePro/Listing/Action.js')
            ->addJs('M2ePro/Listing/Moving.js')
            ->addJs('M2ePro/Amazon/Listing/Action.js')
            ->addJs('M2ePro/Amazon/Listing/ProductSearch.js')
            ->addJs('M2ePro/Amazon/Listing/Template/Description.js')
            ->addJs('M2ePro/Amazon/Listing/VariationProductManage.js')
            ->addJs('M2ePro/Amazon/Listing/VariationProductManageVariationsGrid.js')
            ->addJs('M2ePro/Amazon/Listing/Fulfillment.js')
            ->addJs('M2ePro/Amazon/Listing/RepricingPrice.js')

            ->addJs('M2ePro/TemplateManager.js')
            ->addJs('M2ePro/Amazon/Listing/Category/Tree.js')
            ->addJs('M2ePro/Amazon/Listing/Product/Add.js')
            ->addJs('M2ePro/Amazon/Listing/ProductsFilter.js')

            ->addJs('M2ePro/Amazon/Listing/Product/Variation.js');

        return $this;
    }

    protected function _setActiveMenu($menuPath)
    {
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Amazon::MENU_ROOT_NODE_NICK . '/listings'
        );
    }

    // ---------------------------------------

    protected function addNotificationMessages()
    {
        return null;
    }

    protected function beforeAddContentEvent()
    {
        return null;
    }

    // ---------------------------------------

    public function indexAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);
        $listingProduct->getChildObject()->getVariationManager()->getTypeModel()->getProcessor()->process();

        $tabs = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_variation_product_manage_tabs');
        $tabs->setListingProductId($productId);

        return $this->getResponse()->setBody($tabs->toHtml());
    }

    //########################################

    public function viewVariationsGridAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if ($this->hasChildWithWarning($productId)) {
            $message = Mage::helper('M2ePro')->__(
                'For one of the Child Amazon Products the accordance of Magento
            Product Variation is not set. Please, specify a Variation for further work with this Child Product.'
            );
            $this->_getSession()->addWarning($message);
        }

        $grid = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_variation_product_manage_tabs_variations_grid');
        $grid->setListingProductId($productId);

        $help = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_variation_product_manage_tabs_variations_help');

        if ($this->getRequest()->getParam('listing_product_id_filter')) {
            $this->_getSession()->addNotice(Mage::helper('M2ePro')->__(
                'This list includes a Product you are searching for.'
            ));
        }

        $this->_initAction();

        $this->_addContent($help);
        $this->_addContent($grid)->renderLayout();
    }

    public function viewVariationsGridAjaxAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_variation_product_manage_tabs_variations_grid');
        $grid->setListingProductId($productId);

        return $this->getResponse()->setBody($grid->toHtml());
    }

    public function setChildListingProductOptionsAction()
    {
        $listingProductId = $this->getRequest()->getParam('product_id');
        $productOptions   = $this->getRequest()->getParam('product_options');

        if (empty($listingProductId) || empty($productOptions['values']) || empty($productOptions['attr'])) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */
        $childListingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $listingProductId);

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonChildListingProduct */
        $amazonChildListingProduct = $childListingProduct->getChildObject();

        $childTypeModel = $amazonChildListingProduct->getVariationManager()->getTypeModel();

        $parentListingProduct = $childTypeModel->getParentListingProduct();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonParentListingProduct */
        $amazonParentListingProduct = $parentListingProduct->getChildObject();

        $magentoProduct = $parentListingProduct->getMagentoProduct();

        $magentoOptions = array_combine(
            $productOptions['attr'],
            $productOptions['values']
        );

        $magentoVariation = $magentoProduct->getVariationInstance()->getVariationTypeStandard($magentoOptions);

        $childTypeModel->setProductVariation($magentoVariation);

        $parentTypeModel = $amazonParentListingProduct->getVariationManager()->getTypeModel();
        $parentTypeModel->getProcessor()->process();

        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

        $result = array('success' => true);

        if ($vocabularyHelper->isOptionAutoActionDisabled()) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
        }

        $matchedAttributes = $parentTypeModel->getMatchedAttributes();
        $channelOptions = $childTypeModel->getChannelOptions();

        $optionsForAddingToVocabulary = array();

        foreach ($matchedAttributes as $productAttribute => $channelAttribute) {
            $productOption = $magentoOptions[$productAttribute];
            $channelOption = $channelOptions[$channelAttribute];

            if ($productOption == $channelOption) {
                continue;
            }

            if ($vocabularyHelper->isOptionExistsInLocalStorage($productOption, $channelOption, $channelAttribute)) {
                continue;
            }

            if ($vocabularyHelper->isOptionExistsInServerStorage($productOption, $channelOption, $channelAttribute)) {
                continue;
            }

            $optionsForAddingToVocabulary[$channelAttribute] = array($productOption => $channelOption);
        }

        if ($vocabularyHelper->isOptionAutoActionNotSet()) {
            if (!empty($optionsForAddingToVocabulary)) {
                $result['vocabulary_attribute_options'] = $optionsForAddingToVocabulary;
            }

            return $this->getResponse()->setBody(json_encode($result, JSON_FORCE_OBJECT));
        }

        foreach ($optionsForAddingToVocabulary as $channelAttribute => $options) {
            foreach ($options as $productOption => $channelOption) {
                $vocabularyHelper->addOption($productOption, $channelOption, $channelAttribute);
            }
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('success' => true)));
    }

    //########################################

    public function viewVariationsSettingsAjaxAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $settings = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_variation_product_manage_tabs_settings')
            ->setListingProductId($productId);

        $html = $settings->toHtml();
        $messages = $settings->getMessages();

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'error_icon' => !empty($messages) ? $settings->getMessagesType() : '',
                    'html' => $html
                )
            )
        );
    }

    public function setGeneralIdOwnerAction()
    {
        $listingProductId = $this->getRequest()->getParam('product_id');
        $generalIdOwner = $this->getRequest()->getParam('general_id_owner', null);

        if (empty($listingProductId) || $generalIdOwner === null) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if ($generalIdOwner != Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    $this->setGeneralIdOwner($listingProductId, $generalIdOwner)
                )
            );
        }

        $sku = Mage::helper('M2ePro/Data_Session')->getValue('listing_product_setting_owner_sku_' . $listingProductId);

        if (!$this->hasListingProductSku($listingProductId) && empty($sku)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array('success' => false, 'empty_sku' => true)
                )
            );
        }

        $data = $this->setGeneralIdOwner($listingProductId, $generalIdOwner);

        if (!$data['success']) {
            $mainBlock = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_amazon_listing_template_description_main');
            $mainBlock->setMessages(
                array(
                array(
                'type' => 'warning',
                'text' => $data['msg']
                ))
            );
            $data['html'] = $mainBlock->toHtml();
        } else {
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $listingProductId);
            $listingProduct->setData('sku', $sku);
            $listingProduct->save();

            Mage::helper('M2ePro/Data_Session')->removeValue('listing_product_setting_owner_sku_' . $listingProductId);
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($data));
    }

    public function setListingProductSkuAction()
    {
        $listingProductId = $this->getRequest()->getParam('product_id');
        $sku = $this->getRequest()->getParam('sku');
        $msg = '';

        if (empty($listingProductId) || $sku === null) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $listingProductId);

        if ($this->isExistInM2eProListings($listingProduct, $sku)) {
            $msg = Mage::helper('M2ePro')->__('This SKU is already being used in M2E Pro Listing.');
        } else if ($this->isExistInOtherListings($listingProduct, $sku)) {
            $msg = Mage::helper('M2ePro')->__('This SKU is already being used in M2E Pro Unmanaged Listing.');
        } else {
            $skuInfo = $this->getSkuInfo($listingProduct, $sku);

            if (!$skuInfo) {
                $msg = Mage::helper('M2ePro')->__('This SKU is not found in your Amazon Inventory.');
            } else if ($skuInfo['info']['type'] != 'parent') {
                $msg = Mage::helper('M2ePro')->__('This SKU is used not for Parent Product in your Amazon Inventory.');
            } else if (!empty($skuInfo['info']['bad_parent'])) {
                $msg = Mage::helper('M2ePro')->__(
                    'Working with found Amazon Product is impossible because of the
                    limited access due to Amazon API restriction'
                );
            } else if ($skuInfo['asin'] != $listingProduct->getGeneralId()) {
                $msg = Mage::helper('M2ePro')->__(
                    'The ASIN/ISBN of the Product with this SKU in your Amazon Inventory is different
                     from the ASIN/ISBN for which you want to set you are creator.'
                );
            }
        }

        if (!empty($msg)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'success' => false,
                    'msg' => $msg
                    )
                )
            );
        }

        Mage::helper('M2ePro/Data_Session')->setValue('listing_product_setting_owner_sku_' . $listingProductId, $sku);

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('success' => true)));
    }

    public function viewTemplateDescriptionsGridAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_template_description_grid');
        $grid->setCheckNewAsinAccepted(true);
        $grid->setProductsIds(array($productId));
        $grid->setMapToTemplateJsFn('ListingGridObj.variationProductManageHandler.mapToTemplateDescription');

        return $this->getResponse()->setBody($grid->toHtml());
    }

    public function mapToTemplateDescriptionAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $templateId = $this->getRequest()->getParam('template_id');

        if (empty($productId) || empty($templateId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

        $listingProduct->setData('template_description_id', $templateId)->save();
        $listingProduct->getChildObject()->getVariationManager()->getTypeModel()->getProcessor()->process();

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('success' => true)));
    }

    public function setVariationThemeAction()
    {
        $listingProductId = $this->getRequest()->getParam('product_id');
        $variationTheme   = $this->getRequest()->getParam('variation_theme', null);

        if (empty($listingProductId) || $variationTheme === null) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $listingProductId);

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        $parentTypeModel = $amazonListingProduct->getVariationManager()->getTypeModel();

        $result = array('success' => true);

        if ($parentTypeModel->getChannelTheme() == $variationTheme) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
        }

        $parentTypeModel->setChannelTheme($variationTheme, true, false);

        $variationHelper = Mage::helper('M2ePro/Component_Amazon_Variation');
        $variationHelper->increaseThemeUsageCount($variationTheme, $listingProduct->getMarketplace()->getId());

        $productDataNick = $amazonListingProduct->getAmazonDescriptionTemplate()->getProductDataNick();

        $marketplaceDetails = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
        $marketplaceDetails->setMarketplaceId($amazonListingProduct->getMarketplace()->getId());

        $themeAttributes   = $marketplaceDetails->getVariationThemeAttributes($productDataNick, $variationTheme);
        $productAttributes = $parentTypeModel->getProductAttributes();

        if (count($themeAttributes) != 1 || count($productAttributes) != 1) {
            $parentTypeModel->getProcessor()->process();
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
        }

        $productAttribute = reset($productAttributes);
        $themeAttribute   = reset($themeAttributes);

        $parentTypeModel->setMatchedAttributes(array($productAttribute => $themeAttribute), true);
        $parentTypeModel->getProcessor()->process();

        if ($productAttribute == $themeAttribute || $listingProduct->getMagentoProduct()->isGroupedType()) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
        }

        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

        if ($vocabularyHelper->isAttributeAutoActionDisabled()) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
        }

        if ($vocabularyHelper->isAttributeExistsInLocalStorage($productAttribute, $themeAttribute)) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
        }

        if ($vocabularyHelper->isAttributeExistsInServerStorage($productAttribute, $themeAttribute)) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
        }

        if ($vocabularyHelper->isAttributeAutoActionNotSet()) {
            $result['vocabulary_attributes'] = array($productAttribute => $themeAttribute);
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
        }

        $vocabularyHelper->addAttribute($productAttribute, $themeAttribute);

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
    }

    public function setMatchedAttributesAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $variationAttributes = $this->getRequest()->getParam('variation_attributes');

        if (empty($productId) || empty($variationAttributes)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $matchedAttributes = array_combine(
            $variationAttributes['magento_attributes'],
            $variationAttributes['amazon_attributes']
        );

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        $typeModel = $amazonListingProduct->getVariationManager()->getTypeModel();

        if (!empty($variationAttributes['virtual_magento_attributes'])) {
            $typeModel->setVirtualProductAttributes(
                array_combine(
                    $variationAttributes['virtual_magento_attributes'],
                    $variationAttributes['virtual_magento_option']
                )
            );
        } else if (!empty($variationAttributes['virtual_amazon_attributes'])) {
            $typeModel->setVirtualChannelAttributes(
                array_combine(
                    $variationAttributes['virtual_amazon_attributes'],
                    $variationAttributes['virtual_amazon_option']
                )
            );
        }

        $typeModel->setMatchedAttributes($matchedAttributes);
        $typeModel->getProcessor()->process();

        $result = array(
            'success' => true,
        );

        if ($listingProduct->getMagentoProduct()->isGroupedType()) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
        }

        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

        if ($vocabularyHelper->isAttributeAutoActionDisabled()) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
        }

        $attributesForAddingToVocabulary = array();

        foreach ($matchedAttributes as $productAttribute => $channelAttribute) {
            if ($productAttribute == $channelAttribute) {
                continue;
            }

            if ($vocabularyHelper->isAttributeExistsInLocalStorage($productAttribute, $channelAttribute)) {
                continue;
            }

            if ($vocabularyHelper->isAttributeExistsInServerStorage($productAttribute, $channelAttribute)) {
                continue;
            }

            $attributesForAddingToVocabulary[$productAttribute] = $channelAttribute;
        }

        if ($vocabularyHelper->isAttributeAutoActionNotSet()) {
            if (!empty($attributesForAddingToVocabulary)) {
                $result['vocabulary_attributes'] = $attributesForAddingToVocabulary;
            }

            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
        }

        foreach ($attributesForAddingToVocabulary as $productAttribute => $channelAttribute) {
            $vocabularyHelper->addAttribute($productAttribute, $channelAttribute);
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
    }

    public function createNewChildAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $newChildProductData = $this->getRequest()->getParam('new_child_product');
        $createNewAsin = (int)$this->getRequest()->getParam('create_new_asin', 0);

        if (empty($productId) || empty($newChildProductData)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        /** @var Ess_M2ePro_Model_Listing_Product $parentListingProduct */
        $parentListingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $parentAmazonListingProduct */
        $parentAmazonListingProduct = $parentListingProduct->getChildObject();

        $parentTypeModel = $parentAmazonListingProduct->getVariationManager()->getTypeModel();

        $productOptions = array_combine(
            $newChildProductData['product']['attributes'],
            $newChildProductData['product']['options']
        );

        if ($parentTypeModel->isProductsOptionsRemoved($productOptions)) {
            $parentTypeModel->restoreRemovedProductOptions($productOptions);
        }

        $channelOptions = array();
        $generalId = null;

        if (!$createNewAsin) {
            $channelOptions = array_combine(
                $newChildProductData['channel']['attributes'],
                $newChildProductData['channel']['options']
            );

            $generalId = $parentTypeModel->getChannelVariationGeneralId($channelOptions);
        }

        /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */
        $childListingProduct = $parentTypeModel->createChildListingProduct(
            $productOptions, $channelOptions, $generalId
        );

        $addedProductOptions = $childListingProduct->getChildObject()->getVariationManager()
            ->getTypeModel()->getProductOptions();

        // Don't use $childListingProduct anymore, because it might be removed after calling the following method
        $parentTypeModel->getProcessor()->process();

        $isProductOptionWasAdded = false;
        foreach ($addedProductOptions as $addedProductOption) {
            if ($productOptions == $addedProductOption) {
                $isProductOptionWasAdded = true;
            }
        }

        if (!$isProductOptionWasAdded) {
            $parentListingProduct->logProductMessage(
                'New Child Product cannot be created. There is no correspondence between the Magento Attribute
                 value of a new Child Product and available Magento Attribute values of the Parent Product.',
                Ess_M2ePro_Helper_Data::INITIATOR_USER,
                Ess_M2ePro_Model_Listing_Log::ACTION_ADD_NEW_CHILD_LISTING_PRODUCT,
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
            );

            $message = Mage::helper('M2ePro')->__(
                'New Child Product was not created.
 Please view <a target="_blank" href="%url%">Listing Logs</a> for details.',
                $this->getUrl(
                    '*/adminhtml_amazon_log/listingProduct',
                    array('listing_product_id' => $parentListingProduct->getId())
                )
            );
            $result = array(
                'type' => 'error',
                'msg'  => $message
            );
        } else {
            $result = array(
                'type' => 'success',
                'msg'  => Mage::helper('M2ePro')->__('New Amazon Child Product was created.')
            );
        }

        if ($createNewAsin) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
        }

        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

        if ($vocabularyHelper->isOptionAutoActionDisabled()) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
        }

        $matchedAttributes = $parentTypeModel->getMatchedAttributes();

        $optionsForAddingToVocabulary = array();

        foreach ($matchedAttributes as $productAttribute => $channelAttribute) {
            $productOption = $productOptions[$productAttribute];
            $channelOption = $channelOptions[$channelAttribute];

            if ($productOption == $channelOption) {
                continue;
            }

            if ($vocabularyHelper->isOptionExistsInLocalStorage($productOption, $channelOption, $channelAttribute)) {
                continue;
            }

            if ($vocabularyHelper->isOptionExistsInServerStorage($productOption, $channelOption, $channelAttribute)) {
                continue;
            }

            $optionsForAddingToVocabulary[$channelAttribute] = array($productOption => $channelOption);
        }

        if ($vocabularyHelper->isOptionAutoActionNotSet()) {
            if (!empty($optionsForAddingToVocabulary)) {
                $result['vocabulary_attribute_options'] = $optionsForAddingToVocabulary;
            }

            return $this->getResponse()->setBody(json_encode($result, JSON_FORCE_OBJECT));
        }

        foreach ($optionsForAddingToVocabulary as $channelAttribute => $options) {
            foreach ($options as $productOption => $channelOption) {
                $vocabularyHelper->addOption($productOption, $channelOption, $channelAttribute);
            }
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
    }

    public function addAttributesToVocabularyAction()
    {
        $attributes           = $this->getRequest()->getParam('attributes');
        $isRememberAutoAction = (bool)$this->getRequest()->getParam('is_remember', false);
        $needAddToVocabulary  = (bool)$this->getRequest()->getParam('need_add', false);

        if (!empty($attributes)) {
            $attributes = Mage::helper('M2ePro')->jsonDecode($attributes);
        }

        if (!$isRememberAutoAction && !$needAddToVocabulary) {
            return;
        }

        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

        if ($isRememberAutoAction && !$needAddToVocabulary) {
            $vocabularyHelper->disableAttributeAutoAction();
            return;
        }

        if (!$needAddToVocabulary) {
            return;
        }

        if ($isRememberAutoAction) {
            $vocabularyHelper->enableAttributeAutoAction();
        }

        if (empty($attributes)) {
            return;
        }

        foreach ($attributes as $productAttribute => $channelAttribute) {
            $vocabularyHelper->addAttribute($productAttribute, $channelAttribute);
        }
    }

    public function addOptionsToVocabularyAction()
    {
        $optionsData          = $this->getRequest()->getParam('options_data');
        $isRememberAutoAction = (bool)$this->getRequest()->getParam('is_remember', false);
        $needAddToVocabulary  = (bool)$this->getRequest()->getParam('need_add', false);

        if (!empty($optionsData)) {
            $optionsData = Mage::helper('M2ePro')->jsonDecode($optionsData);
        }

        if (!$isRememberAutoAction && !$needAddToVocabulary) {
            return;
        }

        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

        if ($isRememberAutoAction && !$needAddToVocabulary) {
            $vocabularyHelper->disableOptionAutoAction();
            return;
        }

        if (!$needAddToVocabulary) {
            return;
        }

        if ($isRememberAutoAction) {
            $vocabularyHelper->enableOptionAutoAction();
        }

        if (empty($optionsData)) {
            return;
        }

        foreach ($optionsData as $channelAttribute => $options) {
            foreach ($options as $productOption => $channelOption) {
                $vocabularyHelper->addOption($productOption, $channelOption, $channelAttribute);
            }
        }
    }

    //########################################

    public function viewVocabularyAjaxAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $vocabulary = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_variation_product_manage_tabs_vocabulary')
            ->setListingProductId($productId);

        return $this->getResponse()->setBody($vocabulary->toHtml());
    }

    public function saveAutoActionSettingsAction()
    {
        $attributeAutoAction = $this->getRequest()->getParam('attribute_auto_action');
        $optionAutoAction = $this->getRequest()->getParam('option_auto_action');

        if ($attributeAutoAction === null || $optionAutoAction === null) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

        switch($attributeAutoAction) {
            case Ess_M2ePro_Helper_Component_Amazon_Vocabulary::VOCABULARY_AUTO_ACTION_NOT_SET:
                $vocabularyHelper->unsetAttributeAutoAction();
                break;
            case Ess_M2ePro_Helper_Component_Amazon_Vocabulary::VOCABULARY_AUTO_ACTION_NO:
                $vocabularyHelper->disableAttributeAutoAction();
                break;
            case Ess_M2ePro_Helper_Component_Amazon_Vocabulary::VOCABULARY_AUTO_ACTION_YES:
                $vocabularyHelper->enableAttributeAutoAction();
                break;
        }

        switch($optionAutoAction) {
            case Ess_M2ePro_Helper_Component_Amazon_Vocabulary::VOCABULARY_AUTO_ACTION_NOT_SET:
                $vocabularyHelper->unsetOptionAutoAction();
                break;
            case Ess_M2ePro_Helper_Component_Amazon_Vocabulary::VOCABULARY_AUTO_ACTION_NO:
                $vocabularyHelper->disableOptionAutoAction();
                break;
            case Ess_M2ePro_Helper_Component_Amazon_Vocabulary::VOCABULARY_AUTO_ACTION_YES:
                $vocabularyHelper->enableOptionAutoAction();
                break;
        }

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('success' => true)));
    }

    public function removeAttributeFromVocabularyAction()
    {
        $magentoAttr = $this->getRequest()->getParam('magento_attr');
        $channelAttr = $this->getRequest()->getParam('channel_attr');

        if (empty($magentoAttr) || empty($channelAttr)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');
        $vocabularyHelper->removeAttributeFromLocalStorage($magentoAttr, $channelAttr);

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('success' => true)));
    }

    public function removeOptionFromVocabularyAction()
    {
        $productOption = $this->getRequest()->getParam('product_option');
        $productOptionsGroup = $this->getRequest()->getParam('product_options_group');
        $channelAttr = $this->getRequest()->getParam('channel_attr');

        if (empty($productOption) || empty($productOptionsGroup) || empty($channelAttr)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productOptionsGroup)) {
            $productOptionsGroup = htmlspecialchars_decode($productOptionsGroup);
            $productOptionsGroup = Mage::helper('M2ePro')->jsonDecode($productOptionsGroup);
        }

        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');
        $vocabularyHelper->removeOptionFromLocalStorage($productOption, $productOptionsGroup, $channelAttr);

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('success' => true)));
    }

    //########################################

    protected function isExistInM2eProListings($listingProduct, $sku)
    {
        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->join(
            array('l'=>$listingTable),
            '`main_table`.`listing_id` = `l`.`id`',
            array()
        );

        $collection->addFieldToFilter('sku', $sku);
        $collection->addFieldToFilter('account_id', $listingProduct->getAccount()->getId());

        return $collection->getSize() > 0;
    }

    protected function isExistInOtherListings($listingProduct, $sku)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Other_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');

        $collection->addFieldToFilter('sku', $sku);
        $collection->addFieldToFilter('account_id', $listingProduct->getAccount()->getId());

        return $collection->getSize() > 0;
    }

    protected function getSkuInfo($listingProduct, $sku)
    {
        try {

            /** @var $dispatcherObject Ess_M2ePro_Model_Amazon_Connector_Dispatcher */
            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'product', 'search', 'asinBySkus',
                array('include_info'  => true,
                                                                         'only_realtime' => true,
                                                                         'items'         => array($sku)),
                'items', $listingProduct->getAccount()->getId()
            );

            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            return false;
        }

        return $response[$sku];
    }

    protected function hasListingProductSku($productId)
    {
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

        $sku = $listingProduct->getSku();
        return !empty($sku);
    }

    protected function setGeneralIdOwner($productId, $generalIdOwner)
    {
        $data = array('success' => true);

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        if ($generalIdOwner == Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES) {
            if (!$amazonListingProduct->isExistDescriptionTemplate()) {
                $data['success'] = false;
                $data['msg'] = Mage::helper('M2ePro')->__(
                    'Description Policy with enabled ability to create new ASIN(s)/ISBN(s)
                     should be added in order for operation to be finished.'
                );

                return $data;
            }

            if (!$amazonListingProduct->getAmazonDescriptionTemplate()->isNewAsinAccepted()) {
                $data['success'] = false;
                $data['msg'] = Mage::helper('M2ePro')->__(
                    'Description Policy with enabled ability to create new ASIN(s)/ISBN(s)
                     should be added in order for operation to be finished.'
                );

                return $data;
            }

            $detailsModel = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
            $detailsModel->setMarketplaceId($listingProduct->getListing()->getMarketplaceId());
            $themes = $detailsModel->getVariationThemes(
                $amazonListingProduct->getAmazonDescriptionTemplate()->getProductDataNick()
            );

            if (empty($themes)) {
                $data['success'] = false;
                $data['msg'] = Mage::helper('M2ePro')->__(
                    'The Category chosen in the Description Policy does not support variations.'
                );

                return $data;
            }

            $productAttributes = $amazonListingProduct->getVariationManager()
                ->getTypeModel()
                ->getProductAttributes();

            $isCountEqual = false;
            foreach ($themes as $theme) {
                if (count($theme['attributes']) == count($productAttributes)) {
                    $isCountEqual = true;
                    break;
                }
            }

            if (!$isCountEqual) {
                $data['success'] = false;
                $data['msg'] = Mage::helper('M2ePro')->__('Number of attributes doesn’t match');

                return $data;
            }
        }

        $listingProduct->setData('is_general_id_owner', $generalIdOwner)->save();
        $amazonListingProduct->getVariationManager()->getTypeModel()->getProcessor()->process();

        return $data;
    }

    //########################################

    protected function hasChildWithWarning($productId)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_listing_product');

        $select = $connRead->select();
        $select->distinct(true);
        $select->from(array('alp' => $tableAmazonListingProduct), array('variation_parent_id'))
            ->where('variation_parent_id = ?', $productId)
            ->where(
                'is_variation_product_matched = 0 OR
                (general_id IS NOT NULL AND is_variation_channel_matched = 0)'
            );

        return (bool)Mage::getResourceModel('core/config')->getReadConnection()->fetchCol($select);
    }

    //########################################
}
