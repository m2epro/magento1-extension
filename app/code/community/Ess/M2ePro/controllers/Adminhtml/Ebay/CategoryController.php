<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_CategoryController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Configuration'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Ebay/Configuration/CategoryHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/ChooserHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/SpecificHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Chooser/BrowseHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(NULL, 'pages/viewpage.action?pageId=17367053');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/configuration');
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock(
                    'M2ePro/adminhtml_ebay_configuration', '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_CATEGORY)
                )
            )->renderLayout();
    }

    public function gridAction()
    {
        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_configuration_category_grid')
            ->toHtml();

        $this->getResponse()->setBody($response);
    }

    //########################################

    public function editAction()
    {
        $categoryValue = $this->getRequest()->getParam('value');
        $categoryMode = $this->getRequest()->getParam('mode');
        $categoryType = $this->getRequest()->getParam('type');
        $marketplaceId = $this->getRequest()->getParam('marketplace');
        $accountId = $this->getRequest()->getParam('account');

        $categoryPath = '';
        if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $categoryPath = $this->getSavedCategoryPath($categoryType, $categoryValue);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('chooser_data', array(
            'value' => $categoryValue,
            'mode' => $categoryMode,
            'type' => $categoryType,
            'path' => $categoryPath,
            'marketplace' => $marketplaceId,
            'account' => $accountId,
        ));

        $this->_initAction();

        $blockType = 'other';
        if ($categoryType == Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN) {
            $blockType = 'primary';
        }

        $this->_addContent(
            $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_configuration_category_edit_'.$blockType)
        );
        $this->renderLayout();
    }

    public function saveAction()
    {
        $post = $this->getRequest()->getPost();

        $categoryModelName = 'Category';
        if ($post['category_type'] != Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN) {
            $categoryModelName = 'OtherCategory';
        }

        $categoryTypePrefixes = array(
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN => 'category_main_',
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_SECONDARY => 'category_secondary_',
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN => 'store_category_main_',
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_SECONDARY => 'store_category_secondary_',
        );
        $typePrefix = $categoryTypePrefixes[(int)$post['category_type']];

        $templateIds = $this->getTemplateCategoryIds($post, $typePrefix, $categoryModelName);
        $oldSnapshots = array();
        foreach ($templateIds as $templateId) {
            $oldSnapshots[$templateId] = Mage::getModel('M2ePro/Ebay_Template_' . $categoryModelName)
                ->loadInstance((int)$templateId)
                ->getDataSnapshot();
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connWrite->update(
            Mage::getModel('M2ePro/Ebay_Template_' . $categoryModelName)->getResource()->getMainTable(),
            $this->prepareCategoryUpdateBind($post, $typePrefix),
            array('id IN (?)' => $templateIds)
        );

        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_category');
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_othercategory');

        if (empty($post['specifics_data'])) {
            $this->setSynchStatusNeed($oldSnapshots, $categoryModelName);
            return;
        }

        $specificsData = json_decode($post['specifics_data'], true);
        if (empty($specificsData)) {
            $this->setSynchStatusNeed($oldSnapshots, $categoryModelName);
            return;
        }

        foreach ($specificsData as $templateId => $specifics) {
            if (!is_array($specifics)) {
                continue;
            }

            $templateId = (int)$templateId;

            $connWrite->delete(
                Mage::getModel('M2ePro/Ebay_Template_Category_Specific')->getResource()->getMainTable(),
                array('template_category_id = ?' => $templateId)
            );

            foreach ($specifics as $specific) {
                $specificData = array(
                    'mode' => (int)$specific['mode'],
                    'attribute_title' => $specific['attribute_title'],
                    'value_mode' => (int)$specific['value_mode'],
                    'value_ebay_recommended' => $specific['value_ebay_recommended'],
                    'value_custom_value' => $specific['value_custom_value'],
                    'value_custom_attribute' => $specific['value_custom_attribute']
                );

                $specificData['template_category_id'] = $templateId;

                $specific = Mage::getModel('M2ePro/Ebay_Template_Category_Specific');
                $specific->setData($specificData)->save();
            }
        }

        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_category');
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_othercategory');

        $this->setSynchStatusNeed($oldSnapshots, $categoryModelName);
    }

    //########################################

    public function getChooserHtmlAction()
    {
        // ---------------------------------------
        $selectedCategoriesJson = $this->getRequest()->getParam('selected_categories');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $accountId = $this->getRequest()->getParam('account_id');
        $divId = $this->getRequest()->getParam('div_id');
        $interfaceMode = $this->getRequest()->getParam('interface_mode');
        $isShowEditLinks = $this->getRequest()->getParam('is_show_edit_links');
        $isSingleCategoryMode = $this->getRequest()->getParam('is_single_category_mode');
        $singleCategoryType = $this->getRequest()->getParam('single_category_type');
        $selectCallback = $this->getRequest()->getParam('select_callback');
        $unSelectCallback = $this->getRequest()->getParam('unselect_callback');

        $selectedCategories = array();
        if (!is_null($selectedCategoriesJson)) {
            $selectedCategories = json_decode($selectedCategoriesJson, true);
        }
        // ---------------------------------------

        $ebayCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes();
        $storeCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getStoreCategoryTypes();

        foreach ($selectedCategories as $type => &$selectedCategory) {
            if (!empty($selectedCategory['path'])) {
                continue;
            }

            switch ($selectedCategory['mode']) {
                case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY:
                    if (in_array($type, $ebayCategoryTypes)) {
                        $selectedCategory['path'] = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
                            ->getPath(
                                $selectedCategory['value'],
                                $marketplaceId
                            );

                        $selectedCategory['path'] .= ' (' . $selectedCategory['value'] . ')';

                        Mage::helper('M2ePro/Component_Ebay_Category')
                            ->addRecent(
                                $selectedCategory['value'],
                                $marketplaceId,
                                $type
                            );
                    } elseif (in_array($type, $storeCategoryTypes)) {
                        $selectedCategory['path'] = Mage::helper('M2ePro/Component_Ebay_Category_Store')
                            ->getPath(
                                $selectedCategory['value'],
                                $accountId
                            );

                        $selectedCategory['path'] .= ' (' . $selectedCategory['value'] . ')';

                        Mage::helper('M2ePro/Component_Ebay_Category')
                            ->addRecent(
                                $selectedCategory['value'],
                                $accountId,
                                $type
                            );
                    }

                    break;

                case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE:
                    $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')
                        ->getAttributeLabel($selectedCategory['value']);

                    $selectedCategory['path'] = Mage::helper('M2ePro')->__('Magento Attribute');
                    $selectedCategory['path'] .= ' > ' . $attributeLabel;
                    break;
            }
        }

        // ---------------------------------------
        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser $chooserBlock */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser');
        $chooserBlock->setMarketplaceId($marketplaceId);
        $chooserBlock->setDivId($divId);
        if (!empty($accountId)) {
            $chooserBlock->setAccountId($accountId);
        }
        if (!empty($selectedCategories)) {
            $chooserBlock->setConvertedInternalData($selectedCategories);
        }
        if (!empty($interfaceMode)) {
            $chooserBlock->setInterfaceMode($interfaceMode);
        }
        if (!empty($isShowEditLinks)) {
            $chooserBlock->setShowEditLinks($isShowEditLinks);
        }
        if ($isSingleCategoryMode === 'true') {
            $chooserBlock->setSingleCategoryMode();
            $chooserBlock->setSingleCategoryType($singleCategoryType);
        }
        if (!empty($selectCallback)) {
            $chooserBlock->setSelectCallback($selectCallback);
        }
        if (!empty($unselectCallback)) {
            $chooserBlock->setUnselectCallback($unSelectCallback);
        }
        // ---------------------------------------

        $this->getResponse()->setBody($chooserBlock->toHtml());
    }

    public function getChooserEditHtmlAction()
    {
        // ---------------------------------------
        $categoryType = $this->getRequest()->getParam('category_type');
        $selectedMode = $this->getRequest()->getParam(
            'selected_mode', Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE
        );
        $selectedValue = $this->getRequest()->getParam('selected_value');
        $selectedPath = $this->getRequest()->getParam('selected_path');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $accountId = $this->getRequest()->getParam('account_id');
        // ---------------------------------------

        Mage::helper('M2ePro/Data_Global')->setValue('chooser_category_type', $categoryType);

        // ---------------------------------------
        $editBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser_edit');
        $editBlock->setCategoryType($categoryType);
        // ---------------------------------------

        $ebayCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes();

        if (in_array($categoryType, $ebayCategoryTypes)) {
            $recentCategories = Mage::helper('M2ePro/Component_Ebay_Category')->getRecent(
                $marketplaceId, $categoryType, $selectedValue
            );
        } else {
            $recentCategories = Mage::helper('M2ePro/Component_Ebay_Category')->getRecent(
                $accountId, $categoryType, $selectedValue
            );
        }

        if (empty($recentCategories)) {
            Mage::helper('M2ePro/Data_Global')->setValue('category_chooser_hide_recent', true);
        }

        if ($selectedMode != Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
            if (empty($selectedPath)) {
                switch ($selectedMode) {
                    case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY:
                        if (in_array($categoryType, $ebayCategoryTypes)) {
                            $selectedPath = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                                $selectedValue, $marketplaceId
                            );

                            $selectedPath .= ' (' . $selectedValue . ')';
                        } else {
                            $selectedPath = Mage::helper('M2ePro/Component_Ebay_Category_Store')->getPath(
                                $selectedValue, $accountId
                            );
                        }

                        break;
                    case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE:
                        $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($selectedValue);
                        $selectedPath = Mage::helper('M2ePro')->__('Magento Attribute') . ' > ' . $attributeLabel;

                        break;
                }
            }

            $editBlock->setSelectedCategory(array(
                'mode' => $selectedMode,
                'value' => $selectedValue,
                'path' => $selectedPath
            ));
        }

        $this->getResponse()->setBody($editBlock->toHtml());
    }

    public function getChildCategoriesAction()
    {
        $marketplaceId  = $this->getRequest()->getParam('marketplace_id');
        $accountId = $this->getRequest()->getParam('account_id');
        $parentCategoryId  = $this->getRequest()->getParam('parent_category_id');
        $categoryType = $this->getRequest()->getParam('category_type');

        $ebayCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes();
        $storeCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getStoreCategoryTypes();

        $data = array();

        if ((in_array($categoryType, $ebayCategoryTypes) && is_null($marketplaceId)) ||
            (in_array($categoryType, $storeCategoryTypes) && is_null($accountId))
        ) {
            $this->getResponse()->setBody(json_encode($data));
            return;
        }

        if (in_array($categoryType, $ebayCategoryTypes)) {
            $data = Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Marketplace',$marketplaceId)
                ->getChildObject()
                ->getChildCategories($parentCategoryId);
        } elseif (in_array($categoryType, $storeCategoryTypes)) {
            $tableAccountStoreCategories = Mage::getSingleton('core/resource')
                ->getTableName('m2epro_ebay_account_store_category');

            /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
            $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

            $dbSelect = $connRead->select()
                ->from($tableAccountStoreCategories,'*')
                ->where('`account_id` = ?',(int)$accountId)
                ->where('`parent_id` = ?', $parentCategoryId)
                ->order(array('sorder ASC'));

            $data = $connRead->fetchAll($dbSelect);
        }

        $this->getResponse()->setBody(json_encode($data));
    }

    public function getPathAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $accountId = $this->getRequest()->getParam('account_id');
        $value = $this->getRequest()->getParam('value');
        $mode = $this->getRequest()->getParam('mode');
        $categoryType = $this->getRequest()->getParam('category_type');

        $ebayCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes();
        $storeCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getStoreCategoryTypes();

        if (is_null($value) || is_null($mode)
            || (in_array($categoryType, $ebayCategoryTypes) && is_null($marketplaceId))
            || (in_array($categoryType, $storeCategoryTypes) && is_null($accountId))
        ) {
            $this->getResponse()->setBody('');
            return;
        }

        $path = '';

        switch ($mode) {
            case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY:
                if (in_array($categoryType, $ebayCategoryTypes)) {
                    $path = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath($value, $marketplaceId);
                } else {
                    $path = Mage::helper('M2ePro/Component_Ebay_Category_Store')->getPath($value, $accountId);
                }

                $path .= ' (' . $value . ')';

                break;
            case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE:
                $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($value);
                $path = Mage::helper('M2ePro')->__('Magento Attribute') . ' > ' . $attributeLabel;

                break;
        }

        $this->getResponse()->setBody($path);
    }

    public function searchAction()
    {
        $query = $this->getRequest()->getParam('query');
        $categoryType = $this->getRequest()->getParam('category_type');
        $marketplaceId  = $this->getRequest()->getParam('marketplace_id');
        $accountId  = $this->getRequest()->getParam('account_id');
        $result = array();

        $ebayCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes();
        $storeCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getStoreCategoryTypes();

        if (is_null($query)
            || (in_array($categoryType, $ebayCategoryTypes) && is_null($marketplaceId))
            || (in_array($categoryType, $storeCategoryTypes) && is_null($accountId))
        ) {
            $this->getResponse()->setBody(json_encode($result));
            return;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        if (in_array($categoryType, $ebayCategoryTypes)) {
            $tableName = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');
        } else {
            $tableName = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_account_store_category');
        }

        $dbSelect = $connRead->select();
        $dbSelect->from($tableName, 'category_id')
                 ->where('is_leaf = ?', 1);
        if (in_array($categoryType, $ebayCategoryTypes)) {
            $dbSelect->where('marketplace_id = ?', (int)$marketplaceId);
        } else {
            $dbSelect->where('account_id = ?', (int)$accountId);
        }

        $tempDbSelect = clone $dbSelect;
        $isSearchById = false;

        if (is_numeric($query)) {
            $dbSelect->where('category_id = ?', $query);
            $isSearchById = true;
        } else {
            $dbSelect->where('title like ?', '%' . $query . '%');
        }

        $ids = $connRead->fetchAll($dbSelect);
        if (empty($ids) && $isSearchById) {
            $tempDbSelect->where('title like ?', '%' . $query . '%');
            $ids = $connRead->fetchAll($tempDbSelect);
        }

        foreach ($ids as $categoryId) {
            if (in_array($categoryType, $ebayCategoryTypes)) {
                $treePath = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                    $categoryId['category_id'], $marketplaceId
                );
            } else {
                $treePath = Mage::helper('M2ePro/Component_Ebay_Category_Store')->getPath(
                    $categoryId['category_id'], $accountId
                );
            }

            $result[] = array(
                'titles' => $treePath,
                'id' => $categoryId['category_id']
            );
        }

        $this->getResponse()->setBody(json_encode($result));
    }

    public function getAttributeLabelsAction()
    {
        $attributesParam = $this->getRequest()->getParam('attributes');
        if (is_null($attributesParam)) {
            $this->getResponse()->setBody('');
            return;
        }

        $attributes = explode(',', $attributesParam);
        $labels = Mage::helper('M2ePro/Magento_Attribute')->getAttributesLabels($attributes);

        $this->getResponse()->setBody(json_encode($labels));
    }

    public function getRecentAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace');
        $accountId = $this->getRequest()->getParam('account');
        $categoryType = $this->getRequest()->getParam('category_type');
        $selectedCategory = $this->getRequest()->getParam('selected_category');

        if (in_array($categoryType, Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes())) {
            $categories = Mage::helper('M2ePro/Component_Ebay_Category')->getRecent(
                $marketplaceId, $categoryType, $selectedCategory
            );
        } else {
            $categories = Mage::helper('M2ePro/Component_Ebay_Category')->getRecent(
                $accountId, $categoryType, $selectedCategory
            );
        }

        $this->getResponse()->setBody(json_encode($categories));
    }

    //########################################

    public function refreshStoreCategoriesAction()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');

        Mage::getModel('M2ePro/Ebay_Account')->loadInstance($accountId)->updateEbayStoreInfo();
    }

    public function getAttributeTypeAction()
    {
        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $attribute = Mage::getResourceModel('catalog/product')->getAttribute($attributeCode);

        if ($attribute === false) {
            $this->getResponse()->setBody(json_encode(array('type' => null)));
            return;
        }

        $this->getResponse()->setBody(json_encode(array('type' => $attribute->getBackendType())));
    }

    public function getJsonSpecificsFromPostAction()
    {
        $itemSpecifics = $this->_getSpecificsFromPost($this->getRequest()->getPost());
        $this->getResponse()->setBody(json_encode($itemSpecifics));
    }

    public function getSpecificHtmlAction()
    {
        $post = $this->getRequest()->getPost();
        $specifics = $this->_getSpecificsFromPost($post);

        $categoryMode = $this->getRequest()->getParam('category_mode');
        $categoryValue = $this->getRequest()->getParam('category_value');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $uniqueId = $this->getRequest()->getParam('unique_id');

        $categoryBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific');

        $categoryBlock->setMarketplaceId($marketplaceId);
        $categoryBlock->setCategoryMode($categoryMode);
        $categoryBlock->setCategoryValue($categoryValue);
        $categoryBlock->setUniqueId($uniqueId);
        $categoryBlock->setSelectedSpecifics($specifics);

        $this->getResponse()->setBody($categoryBlock->toHtml());
    }

    public function getConfigurationCategorySpecificHtmlAction()
    {
        $categoryValue = $this->getRequest()->getParam('category_value');
        $categoryMode = $this->getRequest()->getParam('category_mode');
        $marketplaceId = $this->getRequest()->getParam('marketplace');

        $templates = $this->getRequest()->getParam('templates');
        if (is_string($templates) && !empty($templates)) {
            $templates = json_decode($templates, true);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('chooser_data', array(
            'value' => $categoryValue,
            'mode' => $categoryMode,
            'marketplace' => $marketplaceId,
            'templates' => $templates,
        ));

        $specificBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_configuration_category_edit_primary_tabs_specific'
        );

        $this->getResponse()->setBody($specificBlock->toHtml());
    }

    //########################################

    protected function _getSpecificsFromPost($post)
    {
        $itemSpecifics = array();
        for ($i=0; true; $i++) {
            if (!isset($post['item_specifics_mode_'.$i])) {
                break;
            }
            if (!isset($post['custom_item_specifics_value_mode_'.$i])) {
                continue;
            }
            $ebayRecommendedTemp = array();
            if (isset($post['item_specifics_value_ebay_recommended_'.$i])) {
                $ebayRecommendedTemp = (array)$post['item_specifics_value_ebay_recommended_'.$i];
            }
            foreach ($ebayRecommendedTemp as $key=>$temp) {
                $ebayRecommendedTemp[$key] = base64_decode($temp);
            }

            $attributeValue = '';
            $customAttribute = '';

            if ($post['item_specifics_mode_'.$i] ==
                Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ITEM_SPECIFICS) {

                $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE;
                if ((int)$post['item_specifics_value_mode_' . $i] == $temp) {
                    $attributeValue = (array)$post['item_specifics_value_custom_value_'.$i];
                    $customAttribute = '';
                    $ebayRecommendedTemp = '';
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE;
                if ((int)$post['item_specifics_value_mode_'.$i] == $temp) {
                    $customAttribute = $post['item_specifics_value_custom_attribute_'.$i];
                    $attributeValue = '';
                    $ebayRecommendedTemp = '';
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED;
                if ((int)$post['item_specifics_value_mode_'.$i] == $temp) {
                    $customAttribute = '';
                    $attributeValue = '';
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE;
                if ((int)$post['item_specifics_value_mode_'.$i] == $temp) {
                    $customAttribute = '';
                    $attributeValue = '';
                    $ebayRecommendedTemp = '';
                }

                $itemSpecifics[] = array(
                    'mode'                   => (int)$post['item_specifics_mode_'.$i],
                    'attribute_title'        => $post['item_specifics_attribute_title_'.$i],
                    'value_mode'             => (int)$post['item_specifics_value_mode_'.$i],
                    'value_ebay_recommended' => !empty($ebayRecommendedTemp) ? json_encode($ebayRecommendedTemp) : '',
                    'value_custom_value'     => !empty($attributeValue)      ? json_encode($attributeValue)      : '',
                    'value_custom_attribute' => $customAttribute
                );
            }

            if ($post['item_specifics_mode_'.$i] ==
                Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS) {

                $attributeTitle = '';
                $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE;
                if ((int)$post['custom_item_specifics_value_mode_' . $i] == $temp) {
                    $attributeTitle = $post['custom_item_specifics_label_custom_value_'.$i];
                    $attributeValue = (array)$post['item_specifics_value_custom_value_'.$i];
                    $customAttribute = '';
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE;
                if ((int)$post['custom_item_specifics_value_mode_'.$i] == $temp) {
                    $attributeTitle = '';
                    $attributeValue = '';
                    $customAttribute = $post['item_specifics_value_custom_attribute_'.$i];
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE;
                if ((int)$post['custom_item_specifics_value_mode_'.$i] == $temp) {
                    $attributeTitle = $post['custom_item_specifics_label_custom_label_attribute_'.$i];
                    $attributeValue = '';
                    $customAttribute = $post['item_specifics_value_custom_attribute_'.$i];
                }

                $itemSpecifics[] = array(
                    'mode'                      => (int)$post['item_specifics_mode_' . $i],
                    'attribute_title'           => $attributeTitle,
                    'value_mode'                => (int)$post['custom_item_specifics_value_mode_' . $i],
                    'value_ebay_recommended'    => '',
                    'value_custom_value'        => !empty($attributeValue) ? json_encode($attributeValue) : '',
                    'value_custom_attribute'    => $customAttribute
                );
            }
        }

        return $itemSpecifics;
    }

    //########################################

    private function prepareCategoryUpdateBind($post, $typePrefix)
    {
        $updateBind = array(
            $typePrefix.'mode' => $post['category_mode'],
        );
        if ($post['category_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $updateBind[$typePrefix.'id'] = $post['category_value'];
            $updateBind[$typePrefix.'attribute'] = '';

            if (
                in_array($post['category_type'], Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes())
            ) {
                $updateBind[$typePrefix.'path'] = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                    $post['category_value'], $post['marketplace']
                );
            } else {
                $updateBind[$typePrefix.'path'] = Mage::helper('M2ePro/Component_Ebay_Category_Store')->getPath(
                    $post['category_value'], $post['account']
                );
            }
        } elseif ($post['category_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            $updateBind[$typePrefix.'id'] = '';
            $updateBind[$typePrefix.'attribute'] = $post['category_value'];

            $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($post['category_value']);
            $updateBind[$typePrefix.'path'] = Mage::helper('M2ePro')->__('Magento Attribute').' > '.$attributeLabel;
        } else {
            $updateBind[$typePrefix.'id'] = '';
            $updateBind[$typePrefix.'attribute'] = '';
            $updateBind[$typePrefix.'path'] = '';
        }

        return $updateBind;
    }

    private function getTemplateCategoryIds($post, $typePrefix, $categoryModelName)
    {
        $categoryTemplateTable = Mage::getModel('M2ePro/Ebay_Template_' . $categoryModelName)
            ->getResource()
            ->getMainTable();

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $dbSelect = $connRead->select();

        $dbSelect->from($categoryTemplateTable, 'id')->where($typePrefix.'mode = ?', $post['old_category_mode']);
        if (
            in_array($post['category_type'], Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes())
        ) {
            $dbSelect->where('marketplace_id = ?', (int)$post['marketplace']);
        } else {
            $dbSelect->where('account_id = ?', (int)$post['account']);
        }

        if ($post['old_category_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $dbSelect->where($typePrefix.'id = ?', $post['old_category_value']);
        } else {
            $dbSelect->where($typePrefix.'attribute = ?', $post['old_category_value']);
        }

        $templateIds = array();
        foreach ($connRead->query($dbSelect)->fetchAll() as $row) {
            $templateIds[] = $row['id'];
        }

        return $templateIds;
    }

    private function setSynchStatusNeed($oldSnapshots, $categoryModelName)
    {
        if (empty($oldSnapshots)) {
            return;
        }

        foreach ($oldSnapshots as $templateId => $oldSnapshot) {
            $model = Mage::getModel('M2ePro/Ebay_Template_' . $categoryModelName)->loadInstance((int)$templateId);
            $model->setSynchStatusNeed($model->getDataSnapshot(), $oldSnapshot);
        }
    }

    private function getSavedCategoryPath($type, $id)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $dbSelect = $connRead->select();

        $tableName = Mage::getModel('M2ePro/Ebay_Template_OtherCategory')->getResource()->getMainTable();
        if ($type == Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN) {
            $tableName = Mage::getModel('M2ePro/Ebay_Template_Category')->getResource()->getMainTable();
        }

        $categoryTypePrefixes = array(
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN => 'category_main_',
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_SECONDARY => 'category_secondary_',
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN => 'store_category_main_',
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_SECONDARY => 'store_category_secondary_',
        );
        $typePrefix = $categoryTypePrefixes[$type];

        $dbSelect->from($tableName, $typePrefix . 'path')->where($typePrefix.'id = ?', $id);

        return $connRead->fetchOne($dbSelect);
    }

    //########################################
}