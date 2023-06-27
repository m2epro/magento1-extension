<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Mode as CategoryTemplateBlock;
use Ess_M2ePro_Block_Adminhtml_Ebay_Listing_SourceMode as SourceModeBlock;
use Ess_M2ePro_Helper_Component_Ebay_Category as eBayCategory;
use Ess_M2ePro_Model_Ebay_Template_Category as TemplateCategory;

class Ess_M2ePro_Adminhtml_Ebay_Listing_CategorySettingsController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    protected $_sessionKey = 'ebay_listing_category_settings';

    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    protected function _initAction()
    {
        $this->loadLayout();

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')
            ->addJs('M2ePro/Ebay/Listing/Category.js')
            ->addJs('M2ePro/Ebay/Template/Category/Specifics.js')
            ->addJs('M2ePro/Ebay/Template/Category/Chooser.js')
            ->addJs('M2ePro/Ebay/Template/Category/Chooser/Browse.js');

        $this->_initPopUp();

        $this->_title(Mage::helper('M2ePro')->__('Set eBay Categories'));
        $this->setPageHelpLink(null, null, "set-categories");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/listings'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_listing = $this->getListingFromRequest();

        $addedIds = $this->_listing->getChildObject()->getAddedListingProductsIds();
        if (empty($addedIds)) {
            return $this->_redirect(
                '*/adminhtml_ebay_listing_productAdd',
                array(
                    'listing_id' => $this->getRequest()->getParam('listing_id'),
                    '_current'   => true
                )
            );
        }

        $step = (int)$this->getRequest()->getParam('step');
        if ($this->getSessionValue('mode') === null) {
            $step = 1;
        }

        switch ($step) {
            case 1:
                return $this->stepOne();
            case 2:
                $action = 'stepTwo';
                break;
            case 3:
                $action = 'stepThree';
                break;
            // ....
            default:
                return $this->_redirect('*/*/', array('_current' => true, 'step' => 1));
        }

        $action .= 'Mode' . ucfirst($this->getSessionValue('mode'));

        return $this->$action();
    }

    //########################################

    protected function stepOne()
    {
        $builderData = $this->_listing->getSetting('additional_data', 'mode_same_category_data');
        if ($builderData) {
            $categoryTpl = Mage::getModel('M2ePro/Ebay_Template_Category');
            if (!empty($builderData[eBayCategory::TYPE_EBAY_MAIN])) {
                $categoryTpl->load($builderData[eBayCategory::TYPE_EBAY_MAIN]['template_id']);
            }

            $categorySecondaryTpl = Mage::getModel('M2ePro/Ebay_Template_Category');
            if (!empty($builderData[eBayCategory::TYPE_EBAY_SECONDARY])) {
                $categorySecondaryTpl->load($builderData[eBayCategory::TYPE_EBAY_SECONDARY]['template_id']);
            }

            $storeTpl = Mage::getModel('M2ePro/Ebay_Template_StoreCategory');
            if (!empty($builderData[eBayCategory::TYPE_STORE_MAIN])) {
                $storeTpl->load($builderData[eBayCategory::TYPE_STORE_MAIN]['template_id']);
            }

            $storeSecondaryTpl = Mage::getModel('M2ePro/Ebay_Template_StoreCategory');
            if (!empty($builderData[eBayCategory::TYPE_STORE_SECONDARY])) {
                $storeSecondaryTpl->load($builderData[eBayCategory::TYPE_STORE_SECONDARY]['template_id']);
            }

            if ($categoryTpl->getId()) {
                $this->saveModeSame(
                    $categoryTpl,
                    $categorySecondaryTpl,
                    $storeTpl,
                    $storeSecondaryTpl,
                    false
                );

                return $this->reviewAction();
            }
        }

        $source = $this->_listing->getSetting('additional_data', 'source');

        if ($source == SourceModeBlock::SOURCE_OTHER) {
            return $this->_redirect('*/*/otherCategories', array('_current' => true));
        }

        if ($this->getRequest()->isPost()) {
            $mode = $this->getRequest()->getParam('mode');
            $this->setSessionValue('mode', $mode);

            if ($mode == CategoryTemplateBlock::MODE_SAME) {
                $temp = $this->getSessionValue($this->getSessionDataKey());
                $temp['remember'] = (bool)$this->getRequest()->getParam('mode_same_remember_checkbox', false);
                $this->setSessionValue($this->getSessionDataKey(), $temp);
            }

            if ($source) {
                $this->_listing->setSetting(
                    'additional_data',
                    array('ebay_category_settings_mode', $source),
                    $mode
                )
                    ->save();
            }

            return $this->_redirect(
                '*/*/',
                array(
                    'step'               => 2,
                    '_current'           => true,
                    'skip_get_suggested' => null
                )
            );
        }

        $this->setWizardStep('categoryStepOne');

        $defaultMode = CategoryTemplateBlock::MODE_SAME;
        if ($source == SourceModeBlock::SOURCE_CATEGORIES) {
            $defaultMode = CategoryTemplateBlock::MODE_CATEGORY;
        }

        $mode = null;
        $temp = $this->_listing->getSetting('additional_data', array('ebay_category_settings_mode', $source));
        $temp && $mode = $temp;
        $temp = $this->getSessionValue('mode');
        $temp && $mode = $temp;

        $allowedModes = array(
            CategoryTemplateBlock::MODE_SAME,
            CategoryTemplateBlock::MODE_CATEGORY,
            CategoryTemplateBlock::MODE_PRODUCT,
            CategoryTemplateBlock::MODE_MANUALLY
        );
        if ($mode) {
            !in_array($mode, $allowedModes) && $mode = $defaultMode;
        } else {
            $mode = $defaultMode;
        }

        $this->clearSession();

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_mode');
        $block->setData('mode', $mode);

        $this->_initAction()
            ->_title(Mage::helper('M2ePro')->__('Set Your eBay Categories'))
            ->_addContent($block)
            ->renderLayout();
    }

    //########################################

    protected function stepTwoModeSame()
    {
        if ($this->getRequest()->isPost()) {
            $categoryData = array();
            if ($param = $this->getRequest()->getParam('category_data')) {
                $categoryData = Mage::helper('M2ePro')->jsonDecode($param);
            }

            $sessionData = $this->getSessionValue();
            $sessionData['mode_same']['category'] = $categoryData;
            $this->setSessionValue(null, $sessionData);

            /** @var Ess_M2ePro_Model_Ebay_Template_Category_Chooser_Converter $converter */
            $converter = Mage::getModel('M2ePro/Ebay_Template_Category_Chooser_Converter');
            $converter->setAccountId($this->_listing->getAccountId());
            $converter->setMarketplaceId($this->_listing->getMarketplaceId());
            foreach ($categoryData as $type => $templateData) {
                $converter->setCategoryDataFromChooser($templateData, $type);
            }

            $categoryTpl = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build(
                Mage::getModel('M2ePro/Ebay_Template_Category'),
                $converter->getCategoryDataForTemplate(eBayCategory::TYPE_EBAY_MAIN)
            );
            $categorySecondaryTpl = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build(
                Mage::getModel('M2ePro/Ebay_Template_Category'),
                $converter->getCategoryDataForTemplate(eBayCategory::TYPE_EBAY_SECONDARY)
            );
            $storeTpl = Mage::getModel('M2ePro/Ebay_Template_StoreCategory_Builder')->build(
                Mage::getModel('M2ePro/Ebay_Template_StoreCategory'),
                $converter->getCategoryDataForTemplate(eBayCategory::TYPE_STORE_MAIN)
            );
            $storeSecondaryTpl = Mage::getModel('M2ePro/Ebay_Template_StoreCategory_Builder')->build(
                Mage::getModel('M2ePro/Ebay_Template_StoreCategory'),
                $converter->getCategoryDataForTemplate(eBayCategory::TYPE_STORE_SECONDARY)
            );

            $this->saveModeSame(
                $categoryTpl,
                $categorySecondaryTpl,
                $storeTpl,
                $storeSecondaryTpl,
                !empty($sessionData['mode_same']['remember'])
            );

            return $this->reviewAction();
        }

        $this->setWizardStep('categoryStepTwo');

        $ebayListing = $this->_listing->getChildObject();
        $sessionData = $this->getSessionValue();

        $categoriesData = array();

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_Chooser_Converter $converter */
        $converter = Mage::getModel('M2ePro/Ebay_Template_Category_Chooser_Converter');
        $converter->setAccountId($this->_listing->getAccountId());
        $converter->setMarketplaceId($this->_listing->getMarketplaceId());

        $sameData = $ebayListing->getLastPrimaryCategory(array('ebay_primary_category', 'mode_same'));
        if (!empty($sameData['mode']) && !empty($sameData['value']) && !empty($sameData['path'])) {
            $template = Mage::getModel('M2ePro/Ebay_Template_Category');
            $template->loadByCategoryValue(
                $sameData['value'],
                $sameData['mode'],
                $this->_listing->getMarketplaceId(),
                0
            );

            if ($template->getId()) {
                $converter->setCategoryDataFromTemplate($template->getData(), eBayCategory::TYPE_EBAY_MAIN);
                $categoriesData[eBayCategory::TYPE_EBAY_MAIN] = $converter->getCategoryDataForChooser(
                    eBayCategory::TYPE_EBAY_MAIN
                );
            } else {
                $categoriesData[eBayCategory::TYPE_EBAY_MAIN] = array(
                    'mode'  => $sameData['mode'],
                    'value' => $sameData['value'],
                    'path'  => $sameData['path']
                );
            }
        }

        $sameData = $ebayListing->getLastPrimaryCategory(array('ebay_store_primary_category', 'mode_same'));
        if (!empty($sameData['mode']) && !empty($sameData['value']) && !empty($sameData['path'])) {
            $template = Mage::getModel('M2ePro/Ebay_Template_StoreCategory');
            $template->loadByCategoryValue(
                $sameData['value'],
                $sameData['mode'],
                $this->_listing->getAccountId()
            );

            if ($template->getId()) {
                $converter->setCategoryDataFromTemplate($template->getData(), eBayCategory::TYPE_STORE_MAIN);
                $categoriesData[eBayCategory::TYPE_STORE_MAIN] = $converter->getCategoryDataForChooser(
                    eBayCategory::TYPE_STORE_MAIN
                );
            } else {
                $categoriesData[eBayCategory::TYPE_STORE_MAIN] = array(
                    'mode'  => $sameData['mode'],
                    'value' => $sameData['value'],
                    'path'  => $sameData['path']
                );
            }
        }

        !empty($sessionData['mode_same']['category']) && $categoriesData = $sessionData['mode_same']['category'];

        $this->_initAction();
        $this->setPageHelpLink(null, null, "set-categories");

        $this->_addContent(
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_listing_category_same_chooser',
                '',
                array(
                    'categories_data' => $categoriesData
                )
            )
        )->renderLayout();
    }

    protected function stepTwoModeCategory()
    {
        $categoriesIds = $this->getCategoriesIdsByListingProductsIds(
            $this->_listing->getChildObject()->getAddedListingProductsIds()
        );

        if (empty($categoriesIds) && !$this->getRequest()->isXmlHttpRequest()) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'Magento Category is not provided for the products you are currently adding.
                    Please go back and select a different option to assign Channel category to your products. '
                )
            );
        }

        if (!$this->getRequest()->isAjax()) {
            $this->initSessionDataCategories($categoriesIds);
        }

        $categoriesData = $this->getSessionValue($this->getSessionDataKey());

        $this->setWizardStep('categoryStepTwo');

        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_category_category'
        );
        $block->getChild('grid')->setStoreId($this->_listing->getStoreId());
        $block->getChild('grid')->setCategoriesData($categoriesData);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->loadLayout()->getResponse()->setBody($block->getChild('grid')->toHtml());
        }

        $this->_initAction();
        $this->setPageHelpLink(null, null, "set-categories");

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Ebay/Listing/Category/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Category/Grid.js');

        $this->_addContent($block)
            ->renderLayout();
    }

    protected function stepTwoModeManually()
    {
        $this->stepTwoModeProduct(false);
    }

    protected function stepTwoModeProduct($getSuggested = true)
    {
        $this->setWizardStep('categoryStepTwo');
        $this->_initAction();

        if (!$this->getRequest()->getParam('skip_get_suggested')) {
            Mage::helper('M2ePro/Data_Global')->setValue('get_suggested', $getSuggested);
        }

        if (!$this->getRequest()->isAjax()) {
            $this->initSessionDataProducts($this->_listing->getChildObject()->getAddedListingProductsIds());
        }

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Product/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Product/SuggestedSearch.js')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css');

        if ($getSuggested) {
            $this->setPageHelpLink(null, null, "set-categories");
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_product');
        } else {
            $this->setPageHelpLink(null, null, "set-categories");
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_manually');
        }

        $categoriesData = $this->getSessionValue($this->getSessionDataKey());
        $block->getChild('grid')->setCategoriesData($categoriesData);
        $this->_addContent($block);

        $this->renderLayout();
    }

    // ---------------------------------------

    public function stepTwoModeManuallyGridAction()
    {
        $this->loadLayout();

        $categoriesData = $this->getSessionValue($this->getSessionDataKey());
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_manually_grid');
        $block->setCategoriesData($categoriesData);

        $this->getResponse()->setBody($block->toHtml());
    }

    public function stepTwoModeProductGridAction()
    {
        $this->loadLayout();

        $categoriesData = $this->getSessionValue($this->getSessionDataKey());
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_product_grid');
        $block->setCategoriesData($categoriesData);

        $this->getResponse()->setBody($block->toHtml());
    }

    // ---------------------------------------

    public function stepTwoGetSuggestedCategoryAction()
    {
        $this->loadLayout();
        $listing = $this->getListingFromRequest();

        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Ebay_Listing')->getProductCollection($listing->getId());
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToFilter('listing_product_id', array('in' => $this->getRequestIds()));

        if ($collection->getSize() == 0) {
            $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array()));

            return;
        }

        $sessionData = $this->getSessionValue($this->getSessionDataKey());
        $result = array('failed' => 0, 'succeeded' => 0);

        foreach ($collection->getItems() as $product) {
            $lpId = $product->getData('listing_product_id');
//            if (!empty($sessionData[$lpId][eBayCategory::TYPE_EBAY_MAIN]['value'])) {
//                continue;
//            }

            if (($query = $product->getData('name')) == '') {
                $result['failed']++;
                continue;
            }

            $attributeSetId = $product->getData('attribute_set_id');
            if (!Mage::helper('M2ePro/Magento_AttributeSet')->isDefault($attributeSetId)) {
                $query .= ' ' . Mage::helper('M2ePro/Magento_AttributeSet')->getName($attributeSetId);
            }

            try {
                $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
                $connectorObj = $dispatcherObject->getConnector(
                    'category',
                    'get',
                    'suggested',
                    array('query' => $query),
                    $listing->getMarketplaceId()
                );

                $dispatcherObject->process($connectorObj);
                $suggestions = $connectorObj->getResponseData();
            } catch (Exception $e) {
                Mage::helper('M2ePro/Module_Exception')->process($e);
                $result['failed']++;
                continue;
            }

            if (!empty($suggestions)) {
                foreach ($suggestions as $key => $suggestion) {
                    if (!is_numeric($key)) {
                        unset($suggestions[$key]);
                    }
                }
            }

            if (empty($suggestions)) {
                $result['failed']++;
                continue;
            }

            $suggestedCategory = null;
            foreach ($suggestions as $suggestion) {
                $categoryExists = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->exists(
                    $suggestion['category_id'],
                    $listing->getMarketplaceId()
                );

                if ($categoryExists) {
                    $suggestedCategory = $suggestion;
                    break;
                }
            }

            if ($suggestedCategory === null) {
                $result['failed']++;
                continue;
            }

            /** @var Ess_M2ePro_Model_Ebay_Template_Category $template */
            $template = Mage::getModel('M2ePro/Ebay_Template_Category');
            $template->loadByCategoryValue(
                $suggestedCategory['category_id'],
                TemplateCategory::CATEGORY_MODE_EBAY,
                $listing->getMarketplaceId(),
                0
            );

            $sessionData[$lpId][eBayCategory::TYPE_EBAY_MAIN] = array(
                'mode'               => TemplateCategory::CATEGORY_MODE_EBAY,
                'value'              => $suggestedCategory['category_id'],
                'path'               => implode('>', $suggestedCategory['category_path']),
                'is_custom_template' => $template->getIsCustomTemplate(),
                'template_id'        => $template->getId(),
                'specific'           => null
            );
            $sessionData[$lpId]['listing_products_ids'] = array($lpId);

            $result['succeeded']++;
        }

        $this->setSessionValue($this->getSessionDataKey(), $sessionData);
        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
    }

    public function stepTwoResetAction()
    {
        $sessionData = $this->getSessionValue($this->getSessionDataKey());
        !$sessionData && $sessionData = array();

        foreach ($this->getRequestIds() as $id) {
            $sessionData[$id] = array();
        }

        $this->setSessionValue($this->getSessionDataKey(), $sessionData);
    }

    public function stepTwoSaveToSessionAction()
    {
        $templateData = $this->getRequest()->getParam('template_data');
        $templateData = (array)Mage::helper('M2ePro')->jsonDecode($templateData);

        $sessionData = $this->getSessionValue($this->getSessionDataKey());

        foreach ($this->getRequestIds() as $id) {
            foreach ($templateData as $categoryType => $categoryData) {
                $sessionData[$id][$categoryType] = $categoryData;
                if (empty($sessionData[$id][$categoryType])) {
                    unset($sessionData[$id][$categoryType]);
                }
            }

            if ($this->getSessionValue('mode') == CategoryTemplateBlock::MODE_CATEGORY) {
                $sessionData[$id]['listing_products_ids'] = $this->getSelectedListingProductsIdsByCategoriesIds(
                    array($id)
                );
            } else {
                $sessionData[$id]['listing_products_ids'] = array($id);
            }
        }

        $this->setSessionValue($this->getSessionDataKey(), $sessionData);
    }

    public function stepTwoModeValidateAction()
    {
        $sessionData = $this->getSessionValue($this->getSessionDataKey());
        $sessionData = $this->convertCategoriesIdstoProductIds($sessionData);

        $listing = $this->getListingFromRequest();
        $validateSpecifics = $this->getRequest()->getParam('validate_specifics');
        $validateCategory = $this->getRequest()->getParam('validate_category');

        $failedProductsIds = array();
        $succeedProducersIds = array();
        foreach ($sessionData as $listingProductId => $categoryData) {
            if (!isset($categoryData[eBayCategory::TYPE_EBAY_MAIN]) ||
                $categoryData[eBayCategory::TYPE_EBAY_MAIN]['mode'] === TemplateCategory::CATEGORY_MODE_NONE
            ) {
                $validateCategory ? $failedProductsIds[] = $listingProductId
                    : $succeedProducersIds[] = $listingProductId;
                continue;
            }

            if (!$validateSpecifics) {
                $succeedProducersIds[] = $listingProductId;
                continue;
            }

            if ($categoryData[eBayCategory::TYPE_EBAY_MAIN]['is_custom_template'] !== null) {
                $succeedProducersIds[] = $listingProductId;
                continue;
            }

            $hasRequiredSpecifics = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->hasRequiredSpecifics(
                $categoryData[eBayCategory::TYPE_EBAY_MAIN]['value'],
                $listing->getMarketplaceId()
            );

            if (!$hasRequiredSpecifics) {
                $succeedProducersIds[] = $listingProductId;
                continue;
            }

            $failedProductsIds[] = $listingProductId;
        }

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'validation'      => empty($failedProductsIds),
                    'total_count'     => count($failedProductsIds) + count($succeedProducersIds),
                    'failed_count'    => count($failedProductsIds),
                    'failed_products' => $failedProductsIds
                )
            )
        );
    }

    protected function isEbayPrimaryCategorySelected(
        $categoryData,
        Ess_M2ePro_Model_Listing $listing,
        $validateSpecifics = true
    ) {
        if (!isset($categoryData[eBayCategory::TYPE_EBAY_MAIN]) ||
            $categoryData[eBayCategory::TYPE_EBAY_MAIN]['mode'] === TemplateCategory::CATEGORY_MODE_NONE
        ) {
            return false;
        }

        if (!$validateSpecifics) {
            return true;
        }

        if ($categoryData[eBayCategory::TYPE_EBAY_MAIN]['is_custom_template'] !== null) {
            return true;
        }

        return !Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->hasRequiredSpecifics(
            $categoryData[eBayCategory::TYPE_EBAY_MAIN]['value'],
            $listing->getMarketplaceId()
        );
    }

    public function stepTwoDeleteProductsModeProductAction()
    {
        $ids = $this->getRequestIds();
        $ids = array_map('intval', $ids);

        $sessionData = $this->getSessionValue('mode_product');
        foreach ($ids as $id) {
            unset($sessionData[$id]);
        }

        $this->setSessionValue('mode_product', $sessionData);

        $collection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => $ids));

        foreach ($collection->getItems() as $listingProduct) {
            $listingProduct->deleteInstance();
        }

        $listing = $this->getListingFromRequest();
        $listingProductAddIds = $listing->getChildObject()->getAddedListingProductsIds();
        if (empty($listingProductAddIds)) {
            return;
        }

        $listingProductAddIds = array_map('intval', $listingProductAddIds);
        $listingProductAddIds = array_diff($listingProductAddIds, $ids);

        $listing->setData('product_add_ids', Mage::helper('M2ePro')->jsonEncode($listingProductAddIds));
        $listing->save();
    }

    //########################################

    protected function stepThreeModeSame()
    {
        return $this->_redirect(
            '*/adminhtml_ebay_listing/view',
            array(
                'id' => $this->getRequest()->getParam('listing_id')
            )
        );
    }

    protected function stepThreeModeCategory()
    {
        $this->setWizardStep('categoryStepThree');
        $this->saveAction();
    }

    protected function stepThreeModeProduct()
    {
        $this->setWizardStep('categoryStepThree');
        $this->stepThreeSelectSpecifics();
    }

    protected function stepThreeModeManually()
    {
        $this->setWizardStep('categoryStepThree');
        $this->stepThreeSelectSpecifics();
    }

    protected function stepThreeSelectSpecifics()
    {
        $primaryData = array();
        $defaultHashes = array();

        $sessionData = $this->getSessionValue($this->getSessionDataKey());
        foreach ($sessionData as $id => $categoryData) {
            if (!isset($categoryData[eBayCategory::TYPE_EBAY_MAIN]) ||
                $categoryData[eBayCategory::TYPE_EBAY_MAIN]['mode'] === TemplateCategory::CATEGORY_MODE_NONE
            ) {
                continue;
            }

            $primaryCategory = $categoryData[eBayCategory::TYPE_EBAY_MAIN];

            if ($primaryCategory['is_custom_template'] !== null && $primaryCategory['is_custom_template'] == 0) {
                list($mainHash, $hash) = $this->getCategoryHashes($categoryData[eBayCategory::TYPE_EBAY_MAIN]);

                if (!isset($defaultHashes[$mainHash])) {
                    $defaultHashes[$mainHash] = $hash;
                }

                if (!isset($primaryData[$hash])) {
                    $primaryData[$hash][eBayCategory::TYPE_EBAY_MAIN] = $primaryCategory;
                    $primaryData[$hash]['listing_products_ids'] = array();
                }
            }
        }

        $canBeSkipped = !$this->getRequest()->isAjax();
        $listing = $this->getListingFromRequest();

        $isFromOtherListing = false;
        foreach ($sessionData as $id => &$categoryData) {
            if (!isset($categoryData[eBayCategory::TYPE_EBAY_MAIN]) ||
                $categoryData[eBayCategory::TYPE_EBAY_MAIN]['mode'] === TemplateCategory::CATEGORY_MODE_NONE
            ) {
                continue;
            }

            $primaryCategory = $categoryData[eBayCategory::TYPE_EBAY_MAIN];
            list($mainHash, $hash) = $this->getCategoryHashes($categoryData[eBayCategory::TYPE_EBAY_MAIN]);

            $hasRequiredSpecifics = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->hasRequiredSpecifics(
                $primaryCategory['value'],
                $listing->getMarketplaceId()
            );

            if ($primaryCategory['is_custom_template'] === null) {
                if (isset($defaultHashes[$mainHash])) {
                    /** set default settings for the same category and not selected specifics */
                    $hash = $defaultHashes[$mainHash];
                    if (isset($primaryData[$hash][eBayCategory::TYPE_EBAY_MAIN])) {
                        $categoryData[eBayCategory::TYPE_EBAY_MAIN] = $primaryData[$hash][eBayCategory::TYPE_EBAY_MAIN];
                    }
                } elseif ($hasRequiredSpecifics) {
                    $canBeSkipped = false;
                }
            }

            if (!isset($primaryData[$hash])) {
                $primaryData[$hash][eBayCategory::TYPE_EBAY_MAIN] = $primaryCategory;
                $primaryData[$hash]['listing_products_ids'] = $categoryData['listing_products_ids'];
            } else {
                $primaryData[$hash]['listing_products_ids'] = array_merge(
                    $primaryData[$hash]['listing_products_ids'],
                    $categoryData['listing_products_ids']
                );
            }
        }

        unset($categoryData);
        $this->setSessionValue($this->getSessionDataKey(), $sessionData);

        if ($canBeSkipped) {
            return $this->saveAction();
        }

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific');
        $block->getChild('grid')->setCategoriesData($primaryData);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->loadLayout()->getResponse()->setBody($block->getChild('grid')->toHtml());
        }

        $this->_initAction();

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Ebay/Listing/Category/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Product/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Specific/Grid.js');

        $this->_addContent($block)
            ->renderLayout();
    }

    protected function getCategoryHashes(array $categoryData)
    {
        // @codingStandardsIgnoreStart
        $mainHash = $categoryData['mode'] . '-' . $categoryData['value'];
        $specificsHash = !empty($categoryData['specific'])
            ? sha1(Mage::helper('M2ePro')->jsonEncode($categoryData['specific']))
            : '';

        // @codingStandardsIgnoreEnd

        return array(
            $mainHash,
            $mainHash . '-' . $specificsHash
        );
    }

    //########################################

    public function otherCategoriesAction()
    {
        $this->_listing = $this->getListingFromRequest();

        $this->setWizardStep('categoryStepTwo');
        $this->_initAction();
        $this->clearSession();

        $this->setSessionValue('mode', Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Mode::MODE_PRODUCT);
        $this->initSessionDataProducts($this->_listing->getChildObject()->getAddedListingProductsIds());

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Product/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Product/SuggestedSearch.js')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css');

        $this->setPageHelpLink(null, null, "set-categories");
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_other_product');

        $categoriesData = $this->getSessionValue($this->getSessionDataKey());
        $block->getChild('grid')->setCategoriesData($categoriesData);
        $this->_addContent($block);

        $this->renderLayout();
    }

    public function otherCategoriesGridAction()
    {
        $this->loadLayout();

        $categoriesData = $this->getSessionValue($this->getSessionDataKey());
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_other_product_grid');
        $block->setCategoriesData($categoriesData);

        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    protected function initSessionDataProducts(array $addingListingProductIds)
    {
        $listingProducts = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $listingProducts->addFieldToFilter('id', array('in' => $addingListingProductIds));

        $sessionData = $this->getSessionValue($this->getSessionDataKey());
        !$sessionData && $sessionData = array();

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_Chooser_Converter $converter */
        $converter = Mage::getModel('M2ePro/Ebay_Template_Category_Chooser_Converter');
        $converter->setAccountId($this->_listing->getAccountId());
        $converter->setMarketplaceId($this->_listing->getMarketplaceId());

        foreach ($addingListingProductIds as $id) {
            if (!empty($sessionData[$id])) {
                continue;
            }

            $sessionData[$id] = array();

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = $listingProducts->getItemByColumnValue('id', $id);
            if ($listingProduct === null) {
                continue;
            }

            $onlineDataByType = array(
                'category_main_id'            => eBayCategory::TYPE_EBAY_MAIN,
                'category_secondary_id'       => eBayCategory::TYPE_EBAY_SECONDARY,
                'store_category_main_id'      => eBayCategory::TYPE_STORE_MAIN,
                'store_category_secondary_id' => eBayCategory::TYPE_STORE_SECONDARY,
            );

            $onlineData = $listingProduct->getChildObject()->getOnlineCategoriesData();
            foreach ($onlineDataByType as $onlineKey => $categoryType) {
                if (!empty($onlineData[$onlineKey])) {
                    $categoryPath = Mage::helper('M2ePro/Component_Ebay_Category')->isEbayCategoryType($categoryType)
                        ? Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                            $onlineData[$onlineKey],
                            $listingProduct->getMarketplace()->getId()
                        )
                        : Mage::helper('M2ePro/Component_Ebay_Category_Store')->getPath(
                            $onlineData[$onlineKey],
                            $listingProduct->getAccount()->getId()
                        );

                    $sessionData[$id][$categoryType] = array(
                        'mode'               => TemplateCategory::CATEGORY_MODE_EBAY,
                        'value'              => $onlineData[$onlineKey],
                        'path'               => $categoryPath,
                        'template_id'        => null,
                        'is_custom_template' => null,
                        'specific'           => null
                    );

                    if ($categoryType === eBayCategory::TYPE_EBAY_MAIN) {
                        $template = Mage::getModel('M2ePro/Ebay_Template_Category');
                        $template->loadByCategoryValue(
                            $sessionData[$id][$categoryType]['value'],
                            $sessionData[$id][$categoryType]['mode'],
                            $this->_listing->getMarketplaceId(),
                            0
                        );

                        if ($template->getId()) {
                            $converter->setCategoryDataFromTemplate($template->getData(), eBayCategory::TYPE_EBAY_MAIN);
                            $sessionData[$id][$categoryType] = $converter->getCategoryDataForChooser(
                                eBayCategory::TYPE_EBAY_MAIN
                            );
                        }
                    }
                }
            }

            $sessionData[$id]['listing_products_ids'] = array($id);
        }

        foreach (array_diff(array_keys($sessionData), $addingListingProductIds) as $id) {
            unset($sessionData[$id]);
        }

        $this->setSessionValue($this->getSessionDataKey(), $sessionData);
    }

    protected function initSessionDataCategories(array $categoriesIds)
    {
        $sessionData = $this->getSessionValue($this->getSessionDataKey());
        !$sessionData && $sessionData = array();

        foreach ($categoriesIds as $id) {
            if (!empty($sessionData[$id])) {
                continue;
            }

            $sessionData[$id] = array();
        }

        foreach (array_diff(array_keys($sessionData), $categoriesIds) as $id) {
            unset($sessionData[$id]);
        }

        $ebayListing = $this->getEbayListingFromRequest();
        $previousCategoriesData = array();

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_Chooser_Converter $converter */
        $converter = Mage::getModel('M2ePro/Ebay_Template_Category_Chooser_Converter');
        $converter->setAccountId($this->_listing->getAccountId());
        $converter->setMarketplaceId($this->_listing->getMarketplaceId());

        $tempData = $ebayListing->getLastPrimaryCategory(array('ebay_primary_category', 'mode_category'));
        foreach ($tempData as $categoryId => $data) {
            !isset($previousCategoriesData[$categoryId]) && $previousCategoriesData[$categoryId] = array();
            if (!empty($data['mode']) && !empty($data['value']) && !empty($data['path'])) {
                $template = Mage::getModel('M2ePro/Ebay_Template_Category');
                $template->loadByCategoryValue(
                    $data['value'],
                    $data['mode'],
                    $this->_listing->getMarketplaceId(),
                    0
                );

                if ($template->getId()) {
                    $converter->setCategoryDataFromTemplate($template->getData(), eBayCategory::TYPE_EBAY_MAIN);
                    $previousCategoriesData[$categoryId][eBayCategory::TYPE_EBAY_MAIN] =
                        $converter->getCategoryDataForChooser(eBayCategory::TYPE_EBAY_MAIN);
                } else {
                    $previousCategoriesData[$categoryId][eBayCategory::TYPE_EBAY_MAIN] = array(
                        'mode'  => $data['mode'],
                        'value' => $data['value'],
                        'path'  => $data['path']
                    );
                }
            }
        }

        $tempData = $ebayListing->getLastPrimaryCategory(array('ebay_store_primary_category', 'mode_category'));
        foreach ($tempData as $categoryId => $data) {
            !isset($previousCategoriesData[$categoryId]) && $previousCategoriesData[$categoryId] = array();
            if (!empty($data['mode']) && !empty($data['value']) && !empty($data['path'])) {
                $template = Mage::getModel('M2ePro/Ebay_Template_StoreCategory');
                $template->loadByCategoryValue(
                    $data['value'],
                    $data['mode'],
                    $this->_listing->getAccountId()
                );

                if ($template->getId()) {
                    $converter->setCategoryDataFromTemplate($template->getData(), eBayCategory::TYPE_STORE_MAIN);
                    $previousCategoriesData[$categoryId][eBayCategory::TYPE_STORE_MAIN] =
                        $converter->getCategoryDataForChooser(eBayCategory::TYPE_STORE_MAIN);
                } else {
                    $previousCategoriesData[$categoryId][eBayCategory::TYPE_STORE_MAIN] = array(
                        'mode'  => $data['mode'],
                        'value' => $data['value'],
                        'path'  => $data['path']
                    );
                }
            }
        }

        foreach ($sessionData as $magentoCategoryId => &$data) {
            if (!isset($previousCategoriesData[$magentoCategoryId])) {
                continue;
            }

            $data['listing_products_ids'] = $this->getSelectedListingProductsIdsByCategoriesIds(
                array($magentoCategoryId)
            );

            // @codingStandardsIgnoreLine
            $data = array_replace_recursive($data, $previousCategoriesData[$magentoCategoryId]);
        }

        $this->setSessionValue($this->getSessionDataKey(), $sessionData);
    }

    //########################################

    protected function setSessionValue($key, $value)
    {
        $listing = $this->getListingFromRequest();
        $sessionData = $this->getSessionValue();

        if ($key === null) {
            $sessionData = $value;
        } else {
            $sessionData[$key] = $value;
        }

        Mage::helper('M2ePro/Data_Session')->setValue($this->_sessionKey . $listing->getId(), $sessionData);

        return $this;
    }

    protected function getSessionValue($key = null)
    {
        $listing = $this->getListingFromRequest();
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey . $listing->getId());

        if ($sessionData === null) {
            $sessionData = array();
        }

        if ($key === null) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : null;
    }

    protected function clearSession()
    {
        $listing = $this->getListingFromRequest();
        Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey . $listing->getId(), true);
    }

    protected function getSessionDataKey()
    {
        $key = '';

        switch (strtolower($this->getSessionValue('mode'))) {
            case CategoryTemplateBlock::MODE_SAME:
                $key = 'mode_same';
                break;
            case CategoryTemplateBlock::MODE_CATEGORY:
                $key = 'mode_category';
                break;
            case CategoryTemplateBlock::MODE_PRODUCT:
            case CategoryTemplateBlock::MODE_MANUALLY:
                $key = 'mode_product';
                break;
        }

        return $key;
    }

    //########################################

    public function reviewAction()
    {
        $ids = Mage::helper('M2ePro/Data_Session')->getValue('added_products_ids');

        if (empty($ids) || $this->getRequest()->getParam('disable_list')) {
            return $this->_redirect(
                '*/adminhtml_ebay_listing/view',
                array(
                    'id' => $this->getRequest()->getParam('listing_id')
                )
            );
        }

        $this->_initAction();

        $this->setPageHelpLink(null, null, "add-magento-products-manually");

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_Review $blockReview */
        $blockReview = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_product_review',
            '',
            array(
                'products_count' => count($ids)
            )
        );

        $listing = $this->getListingFromRequest();
        $additionalData = $listing->getSettings('additional_data');

        if (isset($additionalData['source']) && $source = $additionalData['source']) {
            $blockReview->setSource($source);
        }

        unset($additionalData['source']);
        $listing->setSettings('additional_data', $additionalData);
        $listing->setData('product_add_ids', Mage::helper('M2ePro')->jsonEncode(array()));
        $listing->save();

        $this->_title(Mage::helper('M2ePro')->__('Listing Review'))
            ->_addContent($blockReview)
            ->renderLayout();
    }

    /**
     * @return Ess_M2ePro_Adminhtml_Ebay_Listing_CategorySettingsController|Mage_Adminhtml_Controller_Action
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function exitToListingAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        if ($listingId === null) {
            return $this->_redirect('*/adminhtml_ebay_listing/index');
        }

        $this->cancelProductsAdding();

        return $this->_redirect(
            '*/adminhtml_ebay_listing/view',
            array('id' => $listingId)
        );
    }

    //########################################

    protected function setWizardStep($step)
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isActive(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $wizardHelper->setStep(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK, $step);
    }

    protected function endWizard()
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isActive(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $wizardHelper->setStatus(
            Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK,
            Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED
        );

        Mage::helper('M2ePro/Magento')->clearMenuCache();
    }

    //########################################

    protected function endListingCreation()
    {
        $ebayListing = $this->getEbayListingFromRequest();

        Mage::helper('M2ePro/Data_Session')->setValue('added_products_ids', $ebayListing->getAddedListingProductsIds());
        $sessionData = $this->getSessionValue($this->getSessionDataKey());
        $this->updateListingLastPrimaryCategory($ebayListing, $sessionData);

        //-- Remove successfully moved Unmanaged items
        $additionalData = $ebayListing->getSettings('additional_data');
        if (isset($additionalData['source']) && $additionalData['source'] == SourceModeBlock::SOURCE_OTHER) {
            $this->deleteListingOthers();
        }

        $this->clearSession();
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing $ebayListing
     * @param array $sessionData
     * @return void
     */
    protected function updateListingLastPrimaryCategory($ebayListing, $sessionData)
    {
        if ($this->getSessionValue('mode') == CategoryTemplateBlock::MODE_SAME) {
            if (isset($sessionData['category'][eBayCategory::TYPE_EBAY_MAIN])) {
                unset($sessionData['category'][eBayCategory::TYPE_EBAY_MAIN]['specific']);
                $ebayListing->updateLastPrimaryCategory(
                    array('ebay_primary_category', 'mode_same'),
                    $sessionData['category'][eBayCategory::TYPE_EBAY_MAIN]
                );
            }

            if (isset($sessionData['category'][eBayCategory::TYPE_STORE_MAIN])) {
                $ebayListing->updateLastPrimaryCategory(
                    array('ebay_store_primary_category', 'mode_same'),
                    $sessionData['category'][eBayCategory::TYPE_STORE_MAIN]
                );
            }
        } elseif ($this->getSessionValue('mode') == CategoryTemplateBlock::MODE_CATEGORY) {
            foreach ($sessionData as $magentoCategoryId => $data) {
                if (isset($data[eBayCategory::TYPE_EBAY_MAIN])) {
                    unset($data[eBayCategory::TYPE_EBAY_MAIN]['specific']);
                    $ebayListing->updateLastPrimaryCategory(
                        array('ebay_primary_category', 'mode_category', $magentoCategoryId),
                        $data[eBayCategory::TYPE_EBAY_MAIN]
                    );
                }

                if (isset($data[eBayCategory::TYPE_STORE_MAIN])) {
                    $ebayListing->updateLastPrimaryCategory(
                        array('ebay_store_primary_category', 'mode_category', $magentoCategoryId),
                        $data[eBayCategory::TYPE_STORE_MAIN]
                    );
                }
            }
        }
    }

    public function getChooserBlockHtmlAction()
    {
        $listing = $this->getListingFromRequest();

        /** @var $chooserBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Grid_Chooser */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_grid_chooser');
        $chooserBlock->setAccountId($listing->getAccountId());
        $chooserBlock->setMarketplaceId($listing->getMarketplaceId());
        $chooserBlock->setCategoryMode($this->getRequest()->getParam('category_mode'));

        $categoriesData = $this->getCategoriesDataForChooserBlock();
        $chooserBlock->setCategoriesData($categoriesData);

        $this->getResponse()->setBody($chooserBlock->toHtml());
    }

    protected function getCategoriesDataForChooserBlock()
    {
        $sessionData = $this->getSessionValue($this->getSessionDataKey());

        $neededProducts = array();
        foreach ($this->getRequestIds() as $id) {
            $temp = array();
            foreach (Mage::helper('M2ePro/Component_Ebay_Category')->getCategoriesTypes() as $categoryType) {
                isset($sessionData[$id][$categoryType]) && $temp[$categoryType] = $sessionData[$id][$categoryType];
            }

            $neededProducts[$id] = $temp;
        }

        $first = reset($neededProducts);
        $resultData = $first;

        foreach ($neededProducts as $lp => $templatesData) {
            if (empty($resultData)) {
                return array();
            }

            foreach ($templatesData as $categoryType => $categoryData) {
                if (!isset($resultData[$categoryType])) {
                    continue;
                }

                !isset($first[$categoryType]['specific']) && $first[$categoryType]['specific'] = array();
                !isset($categoryData['specific']) && $categoryData['specific'] = array();

                if ($first[$categoryType]['template_id'] !== $categoryData['template_id'] ||
                    $first[$categoryType]['is_custom_template'] !== $categoryData['is_custom_template'] ||
                    $first[$categoryType]['specific'] !== $categoryData['specific']
                ) {
                    $resultData[$categoryType]['template_id'] = null;
                    $resultData[$categoryType]['is_custom_template'] = null;
                    $resultData[$categoryType]['specific'] = array();
                }

                if ($first[$categoryType]['mode'] !== $categoryData['mode'] ||
                    $first[$categoryType]['value'] !== $categoryData['value'] ||
                    $first[$categoryType]['path'] !== $categoryData['path']
                ) {
                    unset($resultData[$categoryType]);
                }
            }
        }

        return !$resultData ? array() : $resultData;
    }

    //########################################

    protected function getSelectedListingProductsIdsByCategoriesIds($categoriesIds)
    {
        $productsIds = Mage::helper('M2ePro/Magento_Category')->getProductsFromCategories($categoriesIds);

        $listingProductIds = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product')
            ->addFieldToFilter('product_id', array('in' => $productsIds))->getAllIds();

        return array_values(
            array_intersect(
                $this->getEbayListingFromRequest()->getAddedListingProductsIds(),
                $listingProductIds
            )
        );
    }

    //########################################

    public function saveAction()
    {
        $this->save($this->getSessionValue($this->getSessionDataKey()));

        return $this->reviewAction();
    }

    // ---------------------------------------

    protected function saveModeSame(
        Ess_M2ePro_Model_Ebay_Template_Category $categoryTpl,
        Ess_M2ePro_Model_Ebay_Template_Category $categorySecondaryTpl,
        Ess_M2ePro_Model_Ebay_Template_StoreCategory $storeTpl,
        Ess_M2ePro_Model_Ebay_Template_StoreCategory $storeSecondaryTpl,
        $remember
    ) {
        Mage::getModel('M2ePro/Ebay_Listing_Product')->assignTemplatesToProducts(
            $this->getEbayListingFromRequest()->getAddedListingProductsIds(),
            $categoryTpl->getId(),
            $categorySecondaryTpl->getId(),
            $storeTpl->getId(),
            $storeSecondaryTpl->getId()
        );

        if ($remember) {
            $sameData = array();

            if ($categoryTpl->getId()) {
                $sameData[EbayCategory::TYPE_EBAY_MAIN]['template_id'] = $categoryTpl->getId();
            }

            if ($categorySecondaryTpl->getId()) {
                $sameData[EbayCategory::TYPE_EBAY_SECONDARY]['template_id'] = $categorySecondaryTpl->getId();
            }

            if ($storeTpl->getId()) {
                $sameData[EbayCategory::TYPE_STORE_MAIN]['template_id'] = $storeTpl->getId();
            }

            if ($storeSecondaryTpl->getId()) {
                $sameData[EbayCategory::TYPE_STORE_SECONDARY]['template_id'] = $storeSecondaryTpl->getId();
            }

            $this->_listing->setSetting('additional_data', 'mode_same_category_data', $sameData);
            $this->_listing->save();
        }

        $this->endWizard();
        $this->endListingCreation();
    }

    protected function save($sessionData)
    {
        $listing = $this->getListingFromRequest();
        $sessionData = $this->convertCategoriesIdstoProductIds($sessionData);
        $sessionData = $this->prepareUniqueTemplatesData($sessionData);

        foreach ($sessionData as $hash => $templatesData) {
            /** @var Ess_M2ePro_Model_Ebay_Template_Category_Chooser_Converter $converter */
            $converter = Mage::getModel('M2ePro/Ebay_Template_Category_Chooser_Converter');
            $converter->setAccountId($listing->getAccountId());
            $converter->setMarketplaceId($listing->getMarketplaceId());

            foreach ($templatesData as $categoryType => $templateData) {
                $listingProductsIds = $templateData['listing_products_ids'];
                $listingProductsIds = array_unique($listingProductsIds);
                unset($templateData['listing_products_ids']);

                if (empty($listingProductsIds)) {
                    continue;
                }

                if (Mage::helper('M2ePro/Component_Ebay_Category')->isEbayCategoryType($categoryType)) {
                    $template = Mage::getModel('M2ePro/Ebay_Template_Category');
                    $builder = Mage::getModel('M2ePro/Ebay_Template_Category_Builder');
                } else {
                    $template = Mage::getModel('M2ePro/Ebay_Template_StoreCategory');
                    $builder = Mage::getModel('M2ePro/Ebay_Template_StoreCategory_Builder');
                }

                $converter->setCategoryDataFromChooser($templateData, $categoryType);
                $categoryTpl = $builder->build($template, $converter->getCategoryDataForTemplate($categoryType));

                Mage::getModel('M2ePro/Ebay_Listing_Product')->assignTemplatesToProducts(
                    $listingProductsIds,
                    $categoryType == eBayCategory::TYPE_EBAY_MAIN ? $categoryTpl->getId() : null,
                    $categoryType == eBayCategory::TYPE_EBAY_SECONDARY ? $categoryTpl->getId() : null,
                    $categoryType == eBayCategory::TYPE_STORE_MAIN ? $categoryTpl->getId() : null,
                    $categoryType == eBayCategory::TYPE_STORE_SECONDARY ? $categoryTpl->getId() : null
                );
            }
        }

        $this->endWizard();
        $this->endListingCreation();
    }

    protected function prepareUniqueTemplatesData($sessionData)
    {
        $unique = array();
        $categoryHelper = Mage::helper('M2ePro/Component_Ebay_Category');
        $listing = $this->getListingFromRequest();

        foreach ($sessionData as $listingProductId => $templatesData) {
            if (!$this->isEbayPrimaryCategorySelected($templatesData, $listing)) {
                $this->deleteListingProducts(array($listingProductId));
                continue;
            }

            foreach ($templatesData as $categoryType => $categoryData) {
                if (!$categoryHelper->isEbayCategoryType($categoryType) &&
                    !$categoryHelper->isStoreCategoryType($categoryType)
                ) {
                    continue;
                }

                list($mainHash, $hash) = $this->getCategoryHashes($categoryData);

                if (!isset($unique[$hash][$categoryType])) {
                    $unique[$hash][$categoryType] = $categoryData;
                    $unique[$hash][$categoryType]['listing_products_ids'] = $templatesData['listing_products_ids'];
                } else {
                    // @codingStandardsIgnoreLine
                    $unique[$hash][$categoryType]['listing_products_ids'] = array_merge(
                        $unique[$hash][$categoryType]['listing_products_ids'],
                        $templatesData['listing_products_ids']
                    );
                }
            }
        }

        return $unique;
    }

    //########################################

    protected function convertCategoriesIdstoProductIds($sessionData)
    {
        if ($this->getSessionValue('mode') !== CategoryTemplateBlock::MODE_CATEGORY) {
            return $sessionData;
        }

        foreach ($sessionData as $categoryId => $data) {
            $listingProductsIds = isset($data['listing_products_ids']) ? $data['listing_products_ids'] : array();
            unset($sessionData[$categoryId]);

            foreach ($listingProductsIds as $listingProductId) {
                $sessionData[$listingProductId] = $data;
            }
        }

        foreach ($this->getEbayListingFromRequest()->getAddedListingProductsIds() as $listingProductId) {
            if (!array_key_exists($listingProductId, $sessionData)) {
                $sessionData[$listingProductId] = array();
            }
        }

        return $sessionData;
    }

    protected function getCategoriesIdsByListingProductsIds($listingProductsIds)
    {
        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => $listingProductsIds));

        $productsIds = array();
        foreach ($listingProductCollection->getData() as $item) {
            $productsIds[] = $item['product_id'];
        }

        $productsIds = array_unique($productsIds);

        return Mage::helper('M2ePro/Magento_Category')->getLimitedCategoriesByProducts(
            $productsIds,
            $this->_listing->getStoreId()
        );
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     * @throws Exception
     */
    protected function getListingFromRequest()
    {
        if (!$listingId = $this->getRequest()->getParam('listing_id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        return Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing
     * @throws Exception
     */
    protected function getEbayListingFromRequest()
    {
        return $this->getListingFromRequest()->getChildObject();
    }

    //########################################

    protected function deleteListingProducts($ids)
    {
        $ids = array_map('intval', $ids);

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => $ids));

        foreach ($collection->getItems() as $listingProduct) {
            /**@var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct->canBeForceDeleted(true);
            $listingProduct->deleteInstance();
        }

        $listing = $this->getListingFromRequest();

        $listingProductAddIds = $listing->getChildObject()->getAddedListingProductsIds();
        if (empty($listingProductAddIds)) {
            return;
        }

        $listingProductAddIds = array_map('intval', $listingProductAddIds);
        $listingProductAddIds = array_diff($listingProductAddIds, $ids);

        $listing->setData('product_add_ids', Mage::helper('M2ePro')->jsonEncode($listingProductAddIds));
        $listing->save();
    }

    protected function deleteListingOthers()
    {
        $listingProductsIds = $this->getEbayListingFromRequest()->getAddedListingProductsIds();
        if (empty($listingProductsIds)) {
            return;
        }

        $otherProductsIds = array();

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $listingProductsIds));
        foreach ($collection->getItems() as $listingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $otherProductsIds[] = (int)$listingProduct->getSetting(
                'additional_data',
                $listingProduct::MOVING_LISTING_OTHER_SOURCE_KEY
            );
        }

        if (empty($otherProductsIds)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Other_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');
        $collection->addFieldToFilter('id', array('in' => $otherProductsIds));
        foreach ($collection->getItems() as $listingOther) {
            /** @var Ess_M2ePro_Model_Listing_Other $listingOther */
            $listingOther->moveToListingSucceed();
        }
    }

    /**
     * @return void
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function cancelProductsAdding()
    {
        $this->endWizard();

        $ebayListing = $this->getEbayListingFromRequest();
        $sessionData = $this->getSessionValue($this->getSessionDataKey());
        if (is_array($sessionData)) {
            $this->updateListingLastPrimaryCategory($ebayListing, $sessionData);
        }

        $listing = $ebayListing->getParentObject();
        $additionalData = $listing->getSettings('additional_data');
        unset($additionalData['source']);
        $listing->setSettings('additional_data', $additionalData)->save();

        $this->deleteListingProducts($ebayListing->getAddedListingProductsIds());

        Mage::helper('M2ePro/Data_Session')->setValue('added_products_ids', array());
        $this->clearSession();
    }
}
