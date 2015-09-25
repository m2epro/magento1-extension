<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_CategorySettingsController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    protected $sessionKey = 'ebay_listing_category_settings';

    //#############################################

    protected function _initAction()
    {
        $this->loadLayout();

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')
            ->addJs('M2ePro/Ebay/Listing/Category/ChooserHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/SpecificHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Chooser/BrowseHandler.js');

        $this->_initPopUp();

        $this->setComponentPageHelpLink('Set+eBay+Categories');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/listings');
    }

    //#############################################

    public function indexAction()
    {
        if (!$listingId = $this->getRequest()->getParam('listing_id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if (!$this->checkProductAddIds()) {
            return $this->_redirect('*/adminhtml_ebay_listing_productAdd',array('listing_id' => $listingId,
                                                                                '_current' => true));
        }

        Mage::helper('M2ePro/Data_Global')->setValue(
            'temp_data',
            Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId)
        );

        $step = (int)$this->getRequest()->getParam('step');

        if (is_null($this->getSessionValue('mode'))) {
            $step = 1;
        }

        switch ($step) {
            case 1:
                return $this->stepOne();
                break;
            case 2:
                $action = 'stepTwo';
                break;
            case 3:
                $action = 'stepThree';
                break;
            // ....
            default:
                return $this->_redirect('*/*/', array('_current' => true,'step' => 1));
        }

        $action .= 'Mode'. ucfirst($this->getSessionValue('mode'));

        return $this->$action();
    }

    //#############################################

    private function stepOne()
    {
        if ($builderData = $this->getListingFromRequest()->getSetting('additional_data','mode_same_category_data')) {

            $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($builderData);
            $otherCategoryTemplate = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Builder')->build($builderData);

            $this->saveModeSame($categoryTemplate, $otherCategoryTemplate, false);
            return $this->_redirect(
                '*/adminhtml_ebay_listing/review', array('listing_id' => $this->getRequest()->getParam('listing_id'))
            );
        }

        if ($this->getRequest()->isPost()) {
            $mode = $this->getRequest()->getParam('mode');

            $this->setSessionValue('mode', $mode);

            if ($mode == 'same') {
                $temp = $this->getSessionValue($this->getSessionDataKey());
                $temp['remember'] = (bool)$this->getRequest()->getParam('mode_same_remember_checkbox', false);
                $this->setSessionValue($this->getSessionDataKey(),$temp);
            }

            if ($source = $this->getRequest()->getParam('source')) {
                $this->getListingFromRequest()
                    ->getParentObject()
                    ->setSetting('additional_data',
                                 array('ebay_category_settings_mode',$source),
                                 $mode)
                    ->save();
            }

            return $this->_redirect('*/*/', array(
                'step' => 2,
                '_current' => true,
                'skip_get_suggested' => NULL
            ));
        }

        $this->setWizardStep('categoryStepOne');

        $defaultMode = 'same';
        if ($this->getRequest()->getParam('source') == 'categories' &&
            Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $defaultMode = 'category';
        }

        $mode = NULL;

        $temp = $this->getListingFromRequest()->getSetting(
            'additional_data', array('ebay_category_settings_mode',$this->getRequest()->getParam('source'))
        );
        $temp && $mode = $temp;

        $temp = $this->getSessionValue('mode');
        $temp && $mode = $temp;

        if ($mode) {
            if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
                !in_array($mode, array('same','product')) && $mode = $defaultMode;
            } else {
                !in_array($mode, array('same','category','product','manually')) && $mode = $defaultMode;
            }
        } else {
            $mode = $defaultMode;
        }

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_mode');
        $block->setData('mode', $mode);

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('Set Your eBay Categories'))
             ->_addContent($block)
             ->renderLayout();
    }

    //#############################################

    private function stepTwoModeSame()
    {
        if ($this->getRequest()->isPost()) {
            $categoryParam = $this->getRequest()->getParam('category_data');
            $categoryData = array();
            if (!empty($categoryParam)) {
                $categoryData = json_decode($categoryParam, true);
            }

            $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);

            $data = array();
            $keys = array(
                'category_main_mode',
                'category_main_id',
                'category_main_attribute',

                'category_secondary_mode',
                'category_secondary_id',
                'category_secondary_attribute',

                'store_category_main_mode',
                'store_category_main_id',
                'store_category_main_attribute',

                'store_category_secondary_mode',
                'store_category_secondary_id',
                'store_category_secondary_attribute',
            );
            foreach ($categoryData as $key => $value) {
                if (!in_array($key, $keys)) {
                    continue;
                }

                $data[$key] = $value;
            }

            $listingId = $this->getRequest()->getParam('listing_id');
            $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

            $this->addCategoriesPath($data,$listing);
            $data['marketplace_id'] = $listing->getMarketplaceId();

            $templates = Mage::getModel('M2ePro/Ebay_Template_Category')->getCollection()->getItemsByPrimaryCategories(
                array($data)
            );

            $templateExists = (bool)$templates;

            $specifics = array();
            /* @var $categoryTemplate Ess_M2ePro_Model_Ebay_Template_Category */
            if ($categoryTemplate = reset($templates)) {
                $specifics = $categoryTemplate->getSpecifics();
            }

            $useLastSpecifics = $this->useLastSpecifics();

            $sessionData['mode_same']['category'] = $data;
            $sessionData['mode_same']['specific'] = $specifics;

            Mage::helper('M2ePro/Data_Session')->setValue($this->sessionKey, $sessionData);

            if (!$useLastSpecifics || !$templateExists) {
                return $this->_redirect(
                    '*/*', array('_current' => true, 'step' => 3)
                );
            }

            $builderData = $data;
            $builderData['account_id'] = $this->getListingFromRequest()->getParentObject()->getAccountId();
            $builderData['marketplace_id'] = $this->getListingFromRequest()->getParentObject()->getMarketplaceId();

            $otherCategoryTemplate = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Builder')->build($builderData);

            $this->saveModeSame($categoryTemplate,$otherCategoryTemplate,!empty($sessionData['mode_same']['remember']));

            return $this->_redirect(
                '*/adminhtml_ebay_listing/review', array('listing_id' => $this->getRequest()->getParam('listing_id'))
            );
        }

        $this->setWizardStep('categoryStepTwo');

        $listing = $this->getListingFromRequest();
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);

        $internalData = array();

        $internalData = array_merge(
            $internalData, $listing->getLastPrimaryCategory(array('ebay_primary_category','mode_same'))
        );
        $internalData = array_merge(
            $internalData, $listing->getLastPrimaryCategory(array('ebay_store_primary_category','mode_same'))
        );

        !empty($sessionData['mode_same']['category']) && $internalData = $sessionData['mode_same']['category'];

        $this->_initAction();

        $this->setComponentPageHelpLink('All+Products+Same+Category');

        $this->_title(Mage::helper('M2ePro')->__('Set Your eBay Categories'))
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_same_chooser', '',
                array(
                    'internal_data' => $internalData
                )
            ))->renderLayout();
    }

    private function stepTwoModeCategory()
    {
        $categoriesIds = $this->getCategoriesIdsByListingProductsIds(
            $this->getListingFromRequest()->getAddedListingProductsIds()
        );

        if (empty($categoriesIds) && !$this->getRequest()->isXmlHttpRequest()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__(
                'Magento Categories are not specified on Products you are adding.')
            );
        }

        $this->initSessionData($categoriesIds);

        $listing = $this->getListingFromRequest();

        $previousCategoriesData = array();

        $tempData = $listing->getLastPrimaryCategory(array('ebay_primary_category','mode_category'));
        foreach ($tempData as $categoryId => $data) {
            !isset($previousCategoriesData[$categoryId]) && $previousCategoriesData[$categoryId] = array();
            $previousCategoriesData[$categoryId] += $data;
        }

        $tempData = $listing->getLastPrimaryCategory(array('ebay_store_primary_category','mode_category'));
        foreach ($tempData as $categoryId => $data) {
            !isset($previousCategoriesData[$categoryId]) && $previousCategoriesData[$categoryId] = array();
            $previousCategoriesData[$categoryId] += $data;
        }

        $categoriesData = $this->getSessionValue($this->getSessionDataKey());

        foreach ($categoriesData as $magentoCategoryId => &$data) {

            if (!isset($previousCategoriesData[$magentoCategoryId])) {
                continue;
            }

            $listingProductsIds = $this->getSelectedListingProductsIdsByCategoriesIds(array($magentoCategoryId));
            $data['listing_products_ids'] = $listingProductsIds;

            if ($data['category_main_mode'] != Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
                continue;
            }

            $this->addCategoriesPath($previousCategoriesData[$magentoCategoryId], $listing->getParentObject());

            $data = array_merge($data,$previousCategoriesData[$magentoCategoryId]);
        }

        $this->setSessionValue($this->getSessionDataKey(),$categoriesData);

        $this->setWizardStep('categoryStepTwo');

        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_category_category'
        );
        $block->getChild('grid')->setStoreId($listing->getParentObject()->getStoreId());
        $block->getChild('grid')->setCategoriesData($categoriesData);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->loadLayout()->getResponse()->setBody($block->getChild('grid')->toHtml());
        }

        $this->_initAction();

        $this->setPageHelpLink(NULL, 'pages/viewpage.action?pageId=17367120');

        $this->_title(Mage::helper('M2ePro')->__('Select Products (eBay Categories)'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Ebay/Listing/Category/GridHandler.js')
             ->addJs('M2ePro/Ebay/Listing/Category/Category/GridHandler.js');

         $this->_addContent($block)
              ->renderLayout();
    }

    private function stepTwoModeManually()
    {
        $this->stepTwoModeProduct(false);
    }

    private function stepTwoModeProduct($getSuggested = true)
    {
        $this->setWizardStep('categoryStepTwo');

        $this->_initAction();

        //------------------------------
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $listingProductAddIds = (array)json_decode($listing->getData('product_add_ids'), true);
        //------------------------------

        //------------------------------
        if (!$this->getRequest()->getParam('skip_get_suggested')) {
            Mage::helper('M2ePro/Data_Global')->setValue('get_suggested', $getSuggested);
        }
        $this->initSessionData($listingProductAddIds);
        //------------------------------

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Ebay/Listing/Category/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Product/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Product/SuggestedSearchHandler.js')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addCss('M2ePro/css/Plugin/AutoComplete.css');

        if ($getSuggested) {
            $this->setComponentPageHelpLink('Get+Suggested+Categories');
        } else {
            $this->setPageHelpLink(NULL, 'pages/viewpage.action?pageId=17367077');
        }

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_product'));
        $this->renderLayout();
    }

    //---------------------------------------------

    public function stepTwoModeProductGridAction()
    {
        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        //------------------------------

        //------------------------------
        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $listing);
        //------------------------------

        $this->loadLayout();

        $body = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_product_grid')->toHtml();
        $this->getResponse()->setBody($body);
    }

    //---------------------------------------------

    public function stepTwoGetSuggestedCategoryAction()
    {
        $this->loadLayout();

        //------------------------------
        $listingProductIds = $this->getRequestIds();
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        $marketplaceId = (int)$listing->getData('marketplace_id');
        //------------------------------

        //------------------------------
        $collection = Mage::getResourceModel('M2ePro/Ebay_Listing')->getProductCollection($listingId);
        $collection->addAttributeToSelect('name');
        $collection->getSelect()->where('lp.id IN (?)', $listingProductIds);
        $collection->load();
        //------------------------------

        if ($collection->count() == 0) {
            $this->getResponse()->setBody(json_encode(array()));
            return;
        }

        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);

        $result = array('failed' => 0, 'succeeded' => 0);

        //------------------------------
        foreach ($collection as $product) {
            if (($query = $product->getData('name')) == '') {
                $result['failed']++;
                continue;
            }

            $attributeSetId = $product->getData('attribute_set_id');
            if (!Mage::helper('M2ePro/Magento_AttributeSet')->isDefault($attributeSetId)) {
                $query .= ' ' . Mage::helper('M2ePro/Magento_AttributeSet')->getName($attributeSetId);
            }

            try {

                $dispatcherObject = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
                $connectorObj = $dispatcherObject->getConnector('category','get','suggested',
                                                                array('query' => $query), $marketplaceId);

                $suggestions = $dispatcherObject->process($connectorObj);

            } catch (Exception $e) {
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

            $suggestedCategory = reset($suggestions);

            $categoryExists = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
                ->exists(
                    $suggestedCategory['category_id'],
                    $marketplaceId
                );

            if (!$categoryExists) {
                $result['failed']++;
                continue;
            }

            $listingProductId = $product->getData('listing_product_id');
            $listingProductData = $sessionData['mode_product'][$listingProductId];
            $listingProductData['category_main_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY;
            $listingProductData['category_main_id'] = $suggestedCategory['category_id'];
            $listingProductData['category_main_path'] = implode(' > ', $suggestedCategory['category_path']);

            $sessionData['mode_product'][$listingProductId] = $listingProductData;

            $result['succeeded']++;
        }
        //------------------------------

        Mage::helper('M2ePro/Data_Session')->setValue($this->sessionKey, $sessionData);

        $this->getResponse()->setBody(json_encode($result));
    }

    //---------------------------------------------

    public function stepTwoSuggestedResetAction()
    {
        //------------------------------
        $listingProductIds = $this->getRequestIds();
        //------------------------------

        $this->initSessionData($listingProductIds, true);
    }

    //---------------------------------------------

    public function stepTwoSaveToSessionAction()
    {
        $ids = $this->getRequestIds();
        $templateData = $this->getRequest()->getParam('template_data');
        $templateData = (array)json_decode($templateData, true);

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $this->addCategoriesPath($templateData,$listing);

        $key = $this->getSessionDataKey();
        $sessionData = $this->getSessionValue($key);

        if ($this->getSessionValue('mode') == 'category') {

            foreach ($ids as $categoryId) {
                $sessionData[$categoryId]['listing_products_ids'] = $this->getSelectedListingProductsIdsByCategoriesIds(
                    array($categoryId)
                );
            }

        }

        foreach ($ids as $id) {
            $sessionData[$id] = array_merge($sessionData[$id], $templateData);
        }

        $this->setSessionValue($key, $sessionData);
    }

    //---------------------------------------------

    public function stepTwoModeProductValidateAction()
    {
        $key = $this->getSessionDataKey();
        $sessionData = $this->getSessionValue($key);

        $this->clearSpecificsSession();

        if (empty($sessionData)) {
            return $this->getResponse()->setBody(json_encode(array(
                'validation' => false,
                'message' => Mage::helper('M2ePro')->__(
                    'There are no Items to continue. Please, go back and select the Item(s).'
                )
            )));
        }

        $failedCount = 0;
        foreach ($sessionData as $categoryData) {

            if ($categoryData['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $key = 'category_main_id';
            } else {
                $key = 'category_main_attribute';
            }

            if (!$categoryData[$key]) {
                $failedCount++;
            }
        }

        $this->getResponse()->setBody(json_encode(array(
            'validation' => $failedCount == 0,
            'total_count' => count($sessionData),
            'failed_count' => $failedCount
        )));
    }

    //---------------------------------------------

    public function stepTwoModeCategoryValidateAction()
    {
        $key = $this->getSessionDataKey();
        $sessionData = $this->getSessionValue($key);

        $this->clearSpecificsSession();

        if (empty($sessionData)) {
            return $this->getResponse()->setBody(json_encode(array(
                'validation' => false,
                'message' => Mage::helper('M2ePro')->__(
                    'Magento Categories are not specified on Products you are adding.'
                )
            )));
        }

        $isValid = true;
        foreach ($sessionData as $categoryData) {

            if ($categoryData['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $key = 'category_main_id';
            } else {
                $key = 'category_main_attribute';
            }

            if (!$categoryData[$key]) {
                $isValid = false;
            }
        }

        $this->getResponse()->setBody(json_encode(array(
            'validation' => $isValid,
            'message' => Mage::helper('M2ePro')->__(
                'You have not selected the Primary eBay Category for some Magento Categories.'
            )
        )));
    }

    //---------------------------------------------

    public function stepTwoDeleteProductsModeProductAction()
    {
        $ids = $this->getRequestIds();
        $ids = array_map('intval',$ids);

        $sessionData = $this->getSessionValue('mode_product');
        foreach ($ids as $id) {
            unset($sessionData[$id]);
        }
        $this->setSessionValue('mode_product', $sessionData);

        $collection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id',array('in' => $ids));

        foreach ($collection->getItems() as $listingProduct) {
            $listingProduct->deleteInstance();
        }

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $listingProductAddIds = $this->getListingFromRequest()->getAddedListingProductsIds();
        if (empty($listingProductAddIds)) {
            return;
        }
        $listingProductAddIds = array_map('intval', $listingProductAddIds);
        $listingProductAddIds = array_diff($listingProductAddIds,$ids);

        $listing->setData('product_add_ids',json_encode($listingProductAddIds))->save();
    }

    //#############################################

    private function stepThreeModeSame()
    {
        if ($this->getRequest()->isPost()) {
            $specifics = $this->getRequest()->getParam('specific_data');

            if ($specifics) {
                $specifics = json_decode($specifics, true);
                $specifics = $specifics['specifics'];
            } else {
                $specifics = array();
            }

            $sessionData = $this->getSessionValue($this->getSessionDataKey());

            // save category template & specifics
            //------------------------------
            $builderData = $sessionData['category'];
            $builderData['specifics'] = $specifics;
            $builderData['account_id'] = $this->getListingFromRequest()->getParentObject()->getAccountId();
            $builderData['marketplace_id'] = $this->getListingFromRequest()->getParentObject()->getMarketplaceId();

            $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($builderData);
            $otherCategoryTemplate = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Builder')->build($builderData);

            $this->saveModeSame($categoryTemplate, $otherCategoryTemplate, !empty($sessionData['remember']));

            return $this->_redirect(
                '*/adminhtml_ebay_listing/review', array('listing_id' => $this->getRequest()->getParam('listing_id'))
            );
        }

        $this->setWizardStep('categoryStepThree');

        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);
        $selectedCategoryMode = $sessionData['mode_same']['category']['category_main_mode'];
        if ($selectedCategoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $selectedCategoryValue = $sessionData['mode_same']['category']['category_main_id'];
        } else {
            $selectedCategoryValue = $sessionData['mode_same']['category']['category_main_attribute'];
        }

        $specificBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_category_same_specific', '',
            array(
                'category_mode' => $selectedCategoryMode,
                'category_value' => $selectedCategoryValue,
                'specifics' => $sessionData['mode_same']['specific']
            )
        );

        $this->_initAction();

        $this->setComponentPageHelpLink('Set+Item+Specifics');

        $this->_title(Mage::helper('M2ePro')->__('Set Your eBay Categories'))
            ->_addContent($specificBlock)
            ->renderLayout();
    }

    private function stepThreeModeCategory()
    {
        $this->stepThree();
    }

    private function stepThreeModeProduct()
    {
        $this->stepThree();
    }

    private function stepThreeModeManually()
    {
        $this->stepThree();
    }

    private function stepThree()
    {
        $this->setWizardStep('categoryStepThree');

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $templatesData = $this->getTemplatesData();

        if (count($templatesData) <= 0) {

            $this->save($this->getSessionValue($this->getSessionDataKey()));

            return $this->_redirect('*/adminhtml_ebay_listing/review', array(
                'disable_list' => true,
                '_current' => true
            ));
        }

        $this->initSpecificsSessionData($templatesData);

        $useLastSpecifics = $this->useLastSpecifics();

        $templatesExistForAll = true;
        foreach ($this->getSessionValue('specifics') as $categoryId => $specificsData) {
            if ($specificsData['template_exists'] && $useLastSpecifics) {
                unset($templatesData[$categoryId]);
            } else {
                $templatesExistForAll = false;
            }
        }

        if ($templatesExistForAll && $useLastSpecifics) {
            $this->save($this->getSessionValue($this->getSessionDataKey()));
            return $this->_redirect('*/adminhtml_ebay_listing/review', array('_current' => true));
        }

        $currentPrimaryCategory = $this->getCurrentPrimaryCategory();

        $this->setSessionValue('current_primary_category', $currentPrimaryCategory);

        $wrapper = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific_wrapper');
        $wrapper->setData('store_id', $listing->getStoreId());
        $wrapper->setData('categories', $templatesData);
        $wrapper->setData('current_category', $currentPrimaryCategory);

        $wrapper->setChild('specific', $this->getSpecificBlock());

        $this->_initAction();

        $this->getLayout()->getBlock('head')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Specific/WrapperHandler.js');

        $this->setComponentPageHelpLink('Set+Item+Specifics');

        $this->_title(Mage::helper('M2ePro')->__('Specifics'));

        $this->_addContent($wrapper)
              ->renderLayout();
    }

    //---------------------------------------------

    public function stepThreeGetCategorySpecificsAction()
    {
        $category = $this->getRequest()->getParam('category');
        $templateData = $this->getTemplatesData();
        $templateData = $templateData[$category];

        if ($templateData['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {

            $listingId = $this->getRequest()->getParam('listing_id');
            $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

            $hasRequiredSpecifics = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->hasRequiredSpecifics(
                $templateData['category_main_id'],
                $listing->getMarketplaceId()
            );

        } else {
            $hasRequiredSpecifics = true;
        }

        $this->setSessionValue('current_primary_category', $category);

        $this->getResponse()->setBody(json_encode(array(
            'text' => $this->getSpecificBlock()->toHtml(),
            'hasRequiredSpecifics' => $hasRequiredSpecifics
        )));
    }

    //---------------------------------------------

    public function stepThreeSaveCategorySpecificsToSessionAction()
    {
        $category = $this->getRequest()->getParam('category');
        $categorySpecificsData = json_decode($this->getRequest()->getParam('data'), true);

        $sessionSpecificsData = $this->getSessionValue('specifics');

        $sessionSpecificsData[$category] = array_merge(
            $sessionSpecificsData[$category],
            array('specifics' => $categorySpecificsData['specifics'])
        );

        $this->setSessionValue('specifics', $sessionSpecificsData);
    }

    //#############################################

    private function checkProductAddIds()
    {
        return count($this->getListingFromRequest()->getAddedListingProductsIds()) > 0;
    }

    //#############################################

    private function initSessionData($ids, $override = false)
    {
        $key = $this->getSessionDataKey();

        $sessionData = $this->getSessionValue($key);
        !$sessionData && $sessionData = array();

        foreach ($ids as $id) {

            if (!empty($sessionData[$id]) && !$override) {
                continue;
            }

            $sessionData[$id] = array(
                'category_main_id' => NULL,
                'category_main_path' => NULL,
                'category_main_mode' => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
                'category_main_attribute' => NULL,

                'category_secondary_id' => NULL,
                'category_secondary_path' => NULL,
                'category_secondary_mode' => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
                'category_secondary_attribute' => NULL,

                'store_category_main_id' => NULL,
                'store_category_main_path' => NULL,
                'store_category_main_mode' => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
                'store_category_main_attribute' => NULL,

                'store_category_secondary_id' => NULL,
                'store_category_secondary_path' => NULL,
                'store_category_secondary_mode' => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
                'store_category_secondary_attribute' => NULL,
            );

        }

        if (!$override) {
            foreach (array_diff(array_keys($sessionData),$ids) as $id) {
                unset($sessionData[$id]);
            }
        }

        $this->setSessionValue($key, $sessionData);
    }

    //#############################################

    private function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();
        $sessionData[$key] = $value;

        Mage::helper('M2ePro/Data_Session')->setValue($this->sessionKey, $sessionData);

        return $this;
    }

    private function getSessionValue($key = NULL)
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);

        if (is_null($sessionData)) {
            $sessionData = array();
        }

        if (is_null($key)) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : NULL;
    }

    private function getSessionDataKey()
    {
        $key = '';

        switch (strtolower($this->getSessionValue('mode'))) {
            case 'same':
                $key = 'mode_same';
                break;
            case 'category':
                $key = 'mode_category';
                break;
            case 'product':
            case 'manually':
                $key = 'mode_product';
                break;
        }

        return $key;
    }

    private function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey, true);
    }

    //#############################################

    private function setWizardStep($step)
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isActive(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $wizardHelper->setStep(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK,$step);
    }

    private function endWizard()
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

    //#############################################

    private function endListingCreation()
    {
        $listing = $this->getListingFromRequest();

        Mage::helper('M2ePro/Data_Session')->setValue('added_products_ids',
            $this->getListingFromRequest()->getAddedListingProductsIds());

        $sessionData = $this->getSessionValue($this->getSessionDataKey());

        if ($this->getSessionValue('mode') == 'same') {

            $listing->updateLastPrimaryCategory(
                array('ebay_primary_category', 'mode_same'),
                array('category_main_id' => $sessionData['category']['category_main_id'],
                      'category_main_mode' => $sessionData['category']['category_main_mode'],
                      'category_main_attribute' => $sessionData['category']['category_main_attribute'])
            );

            $listing->updateLastPrimaryCategory(
                array('ebay_store_primary_category', 'mode_same'),
                array('store_category_main_id' => $sessionData['category']['store_category_main_id'],
                      'store_category_main_mode' => $sessionData['category']['store_category_main_mode'],
                      'store_category_main_attribute' => $sessionData['category']['store_category_main_attribute'])
            );

        } elseif ($this->getSessionValue('mode') == 'category') {

            foreach ($sessionData as $magentoCategoryId => $data) {

                $listing->updateLastPrimaryCategory(
                    array('ebay_primary_category', 'mode_category', $magentoCategoryId),
                    array(
                        'category_main_id' => $data['category_main_id'],
                        'category_main_mode' => $data['category_main_mode'],
                        'category_main_attribute' => $data['category_main_attribute']
                    )
                );

                $listing->updateLastPrimaryCategory(
                    array('ebay_store_primary_category', 'mode_category', $magentoCategoryId),
                    array(
                        'store_category_main_id' => $data['store_category_main_id'],
                        'store_category_main_mode' => $data['store_category_main_mode'],
                        'store_category_main_attribute' => $data['store_category_main_attribute']
                    )
                );
            }
        }

        $listing->setData('product_add_ids',json_encode(array()))->save();

        $this->clearSession();
    }

    //#############################################

    private function getSpecificBlock()
    {
        $templatesData = $this->getTemplatesData();
        $currentPrimaryCategory = $this->getCurrentPrimaryCategory();

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        /* @var $specific Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Specific */
        $specific = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific');
        $specific->setMarketplaceId($listing->getMarketplaceId());

        $currentTemplateData = $templatesData[$currentPrimaryCategory];

        $categoryMode = $currentTemplateData['category_main_mode'];
        $specific->setCategoryMode($categoryMode);

        if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $specific->setCategoryValue($currentTemplateData['category_main_id']);
        } elseif($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            $specific->setCategoryValue($currentTemplateData['category_main_attribute']);
        }

        $specificsData = $this->getSessionValue('specifics');

        $specific->setInternalData($specificsData[$currentPrimaryCategory]);
        $specific->setSelectedSpecifics($specificsData[$currentPrimaryCategory]['specifics']);

        return $specific;
    }

    //#############################################

    public function getChooserBlockHtmlAction()
    {
        $ids = $this->getRequestIds();

        $key = $this->getSessionDataKey();
        $sessionData = $this->getSessionValue($key);

        $neededData = array();

        foreach ($ids as $id) {
            $neededData[$id] = $sessionData[$id];
        }

        // ----------------------------------------------

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $accountId = $listing->getAccountId();
        $marketplaceId = $listing->getMarketplaceId();
        $internalData  = $this->getInternalDataForChooserBlock($neededData);

        /* @var $chooserBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser');
        $chooserBlock->setDivId('chooser_main_container');
        $chooserBlock->setAccountId($accountId);
        $chooserBlock->setMarketplaceId($marketplaceId);
        $chooserBlock->setInternalData($internalData);

        // ---------------------------------------------
        $wrapper = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser_wrapper');
        $wrapper->setChild('chooser', $chooserBlock);
        // ---------------------------------------------

        $this->getResponse()->setBody($wrapper->toHtml());
    }

    //#############################################

    private function getInternalDataForChooserBlock($data)
    {
        $resultData = array();

        $firstData = reset($data);

        $tempKeys = array('category_main_id',
                          'category_main_path',
                          'category_main_mode',
                          'category_main_attribute');

        foreach ($tempKeys as $key) {
            $resultData[$key] = $firstData[$key];
        }

        if (!Mage::helper('M2ePro')->theSameItemsInData($data,$tempKeys)) {
            $resultData['category_main_id'] = 0;
            $resultData['category_main_path'] = NULL;
            $resultData['category_main_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;
            $resultData['category_main_attribute'] = NULL;
            $resultData['category_main_message'] = Mage::helper('M2ePro')->__(
                'Please, specify a value suitable for all chosen Products.'
            );
        }

        // ---------------------------------------------

        $tempKeys = array('category_secondary_id',
                          'category_secondary_path',
                          'category_secondary_mode',
                          'category_secondary_attribute');

        foreach ($tempKeys as $key) {
            $resultData[$key] = $firstData[$key];
        }

        if (!Mage::helper('M2ePro')->theSameItemsInData($data,$tempKeys)) {
            $resultData['category_secondary_id'] = 0;
            $resultData['category_secondary_path'] = NULL;
            $resultData['category_secondary_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;
            $resultData['category_secondary_attribute'] = NULL;
            $resultData['category_secondary_message'] = Mage::helper('M2ePro')->__(
                'Please, specify a value suitable for all chosen Products.'
            );
        }

        // ---------------------------------------------

        $tempKeys = array('store_category_main_id',
                          'store_category_main_path',
                          'store_category_main_mode',
                          'store_category_main_attribute');

        foreach ($tempKeys as $key) {
            $resultData[$key] = $firstData[$key];
        }

        if (!Mage::helper('M2ePro')->theSameItemsInData($data,$tempKeys)) {
            $resultData['store_category_main_id'] = 0;
            $resultData['store_category_main_path'] = NULL;
            $resultData['store_category_main_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;
            $resultData['store_category_main_attribute'] = NULL;
            $resultData['store_category_main_message'] = Mage::helper('M2ePro')->__(
                'Please, specify a value suitable for all chosen Products.'
            );
        }

        // ---------------------------------------------

        $tempKeys = array('store_category_secondary_id',
                          'store_category_secondary_path',
                          'store_category_secondary_mode',
                          'store_category_secondary_attribute');

        foreach ($tempKeys as $key) {
            $resultData[$key] = $firstData[$key];
        }

        if (!Mage::helper('M2ePro')->theSameItemsInData($data,$tempKeys)) {
            $resultData['store_category_secondary_id'] = 0;
            $resultData['store_category_secondary_path'] = NULL;
            $resultData['store_category_secondary_mode'] =
                Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;
            $resultData['store_category_secondary_attribute'] = NULL;
            $resultData['store_category_secondary_message'] = Mage::helper('M2ePro')->__(
                'Please, specify a value suitable for all chosen Products.'
            );
        }

        // ---------------------------------------------

        return $resultData;
    }

    //#############################################

    private function clearSpecificsSession()
    {
        $this->setSessionValue('specifics', null);
        $this->setSessionValue('current_primary_category', null);
    }

    //#############################################

    private function getCurrentPrimaryCategory()
    {
        $currentPrimaryCategory = $this->getSessionValue('current_primary_category');

        if (!is_null($currentPrimaryCategory)) {
            return $currentPrimaryCategory;
        }

        $useLastSpecifics = $this->useLastSpecifics();

        $specifics = $this->getSessionValue('specifics');

        if (!$useLastSpecifics) {
            return key($specifics);
        }

        foreach ($specifics as $id => $specificsData) {
            if (!$specificsData['template_exists']) {
                $currentPrimaryCategory = $id;
                break;
            }
        }

        return $currentPrimaryCategory;
    }

    private function getTemplatesData()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

        $templatesData = array();
        foreach ($this->getSessionValue($this->getSessionDataKey()) as $templateData) {

            if ($templateData['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $id = $templateData['category_main_id'];
            } else {
                $id = $templateData['category_main_attribute'];
            }

            if (empty($id)) {
                continue;
            }

            $templateData['marketplace_id'] = $listing->getMarketplaceId();
            $templatesData[$id] = $templateData;
        }

        ksort($templatesData);
        $templatesData = array_reverse($templatesData, true);

        return $templatesData;
    }

    //#############################################

    private function initSpecificsSessionData($templatesData)
    {
        $specificsData = $this->getSessionValue('specifics');
        is_null($specificsData) && $specificsData = array();

        $existingTemplates = Mage::getModel('M2ePro/Ebay_Template_Category')->getCollection()
            ->getItemsByPrimaryCategories($templatesData);

        foreach ($templatesData as $id => $templateData) {

            if (!empty($specificsData[$id])) {
                continue;
            }

            $specifics = array();
            $templateExists = false;

            if (isset($existingTemplates[$id])) {
                $specifics = $existingTemplates[$id]->getSpecifics();
                $templateExists = true;
            }

            $specificsData[$id] = array(
                'specifics' => $specifics,
                'template_exists' => $templateExists
            );
        }

        $this->setSessionValue('specifics', $specificsData);
    }

    //#############################################

    private function getSelectedListingProductsIdsByCategoriesIds($categoriesIds)
    {
        $productsIds = Mage::helper('M2ePro/Magento_Category')->getProductsFromCategories($categoriesIds);

        $listingProductIds = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product')
                ->addFieldToFilter('product_id', array('in' => $productsIds))->getAllIds();

        return array_values(array_intersect(
            $this->getListingFromRequest()->getAddedListingProductsIds(),
            $listingProductIds
        ));
    }

    //#############################################

    public function saveAction()
    {
        $this->save($this->getSessionValue($this->getSessionDataKey()));
    }

    //-----------------------------------------------

    private function saveModeSame($categoryTemplate, $otherCategoryTemplate, $remember)
    {
        $this->assignTemplatesToProducts(
            $categoryTemplate->getId(),
            $otherCategoryTemplate->getId(),
            $this->getListingFromRequest()->getAddedListingProductsIds()
        );

        if ($remember) {
            $this->getListingFromRequest()->getParentObject()
                ->setSetting(
                    'additional_data', 'mode_same_category_data',
                    array_merge(
                        $categoryTemplate->getData(),
                        $otherCategoryTemplate->getData(),
                        array('specifics' => $categoryTemplate->getSpecifics())
                    )
                )
                ->save();
        }

        $this->endWizard();
        $this->endListingCreation();
    }

    private function save($sessionData)
    {
        if ($this->getSessionValue('mode') == 'category') {
            foreach ($sessionData as $categoryId => $data) {

                $listingProductsIds = $data['listing_products_ids'];
                unset($data['listing_products_ids']);

                foreach ($listingProductsIds as $listingProductId) {
                    $sessionData[$listingProductId] = $data;
                }

                unset($sessionData[$categoryId]);
            }
        }

        $specificsData = $this->getSessionValue('specifics');

        foreach ($this->getUniqueTemplatesData($sessionData) as $templateData) {

            $listingProductsIds = $templateData['listing_products_ids'];
            $listingProductsIds = array_unique($listingProductsIds);

            if (empty($listingProductsIds)) {
                continue;
            }

            // save category template & specifics
            //------------------------------
            $builderData = $templateData;
            $builderData['account_id'] = $this->getListingFromRequest()->getParentObject()->getAccountId();
            $builderData['marketplace_id'] = $this->getListingFromRequest()->getParentObject()->getMarketplaceId();

            $categoryTemplateId = NULL;

            if (!is_null($builderData['identifier'])) {

                $builderData['specifics'] = $specificsData[$templateData['identifier']]['specifics'];

                $categoryTemplateId = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($builderData)
                                                                                             ->getId();
            }

            $otherCategoryTemplate = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Builder')->build($builderData);
            //------------------------------

            $this->assignTemplatesToProducts(
                $categoryTemplateId,
                $otherCategoryTemplate->getId(),
                $listingProductsIds
            );
        }

        $this->endWizard();
        $this->endListingCreation();
    }

    private function getUniqueTemplatesData($templatesData)
    {
        $unique = array();

        foreach ($templatesData as $listingProductId => $data) {

            $hash = md5(json_encode($data));

            $data['identifier'] = NULL;

            if ($data['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $data['identifier'] = $data['category_main_id'];
            }
            if ($data['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
                $data['identifier'] = $data['category_main_attribute'];
            }

            !isset($unique[$hash]) && $unique[$hash] = array();

            $unique[$hash] = array_merge($unique[$hash], $data);
            $unique[$hash]['listing_products_ids'][] = $listingProductId;
        }

        return array_values($unique);
    }

    //#############################################

    private function getCategoriesIdsByListingProductsIds($listingProductsIds)
    {
        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id',array('in' => $listingProductsIds));

        $productsIds = array();
        foreach ($listingProductCollection->getData() as $item) {
            $productsIds[] = $item['product_id'];
        }
        $productsIds = array_unique($productsIds);

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',
            $this->getRequest()->getParam('listing_id')
        );

        return Mage::helper('M2ePro/Magento_Category')->getLimitedCategoriesByProducts(
            $productsIds,
            $listing->getStoreId()
        );
    }

    //#############################################

    private function addCategoriesPath(&$data,Ess_M2ePro_Model_Listing $listing)
    {
        $marketplaceId = $listing->getData('marketplace_id');
        $accountId = $listing->getAccountId();

        if (isset($data['category_main_mode'])) {
            if ($data['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $data['category_main_path'] = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                    $data['category_main_id'],
                    $marketplaceId
                );
            } else {
                $data['category_main_path'] = null;
            }
        }

        if (isset($data['category_secondary_mode'])) {
            if ($data['category_secondary_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $data['category_secondary_path'] = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                    $data['category_secondary_id'],
                    $marketplaceId
                );
            } else {
                $data['category_secondary_path'] = null;
            }
        }

        if (isset($data['store_category_main_mode'])) {
            if ($data['store_category_main_mode'] ==
                    Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $data['store_category_main_path'] = Mage::helper('M2ePro/Component_Ebay_Category_Store')
                    ->getPath(
                        $data['store_category_main_id'],
                        $accountId
                    );
            } else {
                $data['store_category_main_path'] = null;
            }
        }

        if (isset($data['store_category_secondary_mode'])) {
            if ($data['store_category_secondary_mode'] ==
                    Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $data['store_category_secondary_path'] = Mage::helper('M2ePro/Component_Ebay_Category_Store')
                    ->getPath(
                        $data['store_category_secondary_id'],
                        $accountId
                    );
            } else {
                $data['store_category_secondary_path'] = null;
            }
        }
    }

    //#############################################

    /** @return Ess_M2ePro_Model_Ebay_Listing
     * @throws Exception
     */
    private function getListingFromRequest()
    {
        if (!$listingId = $this->getRequest()->getParam('listing_id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        return Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId)->getChildObject();
    }

    //#############################################

    private function assignTemplatesToProducts($categoryTemplateId, $otherCategoryTemplateId, $productsIds)
    {
        if (empty($productsIds)) {
            return;
        }

        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $connWrite->update(
            Mage::getSingleton('core/resource')->getTableName('M2ePro/Ebay_Listing_Product'),
            array(
                'template_category_id'       => $categoryTemplateId,
                'template_other_category_id' => $otherCategoryTemplateId
            ),
            'listing_product_id IN ('.implode(',',$productsIds).')'
        );
    }

    //#############################################

    private function useLastSpecifics()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return false;
        }

        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/ebay/template/category/', 'use_last_specifics'
        );
    }

    //#############################################
}