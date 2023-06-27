<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Template_Description_Specific as Description_Specific;

class Ess_M2ePro_Adminhtml_Amazon_Template_DescriptionController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Policies'))
             ->_title(Mage::helper('M2ePro')->__('Description Policies'));

        $this->getLayout()->getBlock('head')
                ->addJs('M2ePro/Template/Edit.js')
                ->addJs('M2ePro/Amazon/Template/Edit.js')
                ->addJs('M2ePro/Amazon/Template/Description.js')
                ->addJs('M2ePro/Amazon/Template/Description/Definition.js')
                ->addJs('M2ePro/Amazon/Template/Description/Category/Chooser.js')
                ->addJs('M2ePro/Amazon/Template/Description/Category/Specific.js')
                ->addJs('M2ePro/Amazon/Template/Description/Category/Specific/Renderer.js')
                ->addJs('M2ePro/Amazon/Template/Description/Category/Specific/Dictionary.js')
                ->addJs('M2ePro/Amazon/Template/Description/Category/Specific/BlockRenderer.js')
                ->addJs('M2ePro/Amazon/Template/Description/Category/Specific/Block/GridRenderer.js')
                ->addJs('M2ePro/Amazon/Template/Description/Category/Specific/Block/AddSpecificRenderer.js')
                ->addJs('M2ePro/Amazon/Template/Description/Category/Specific/Grid/RowRenderer.js')
                ->addJs('M2ePro/Amazon/Template/Description/Category/Specific/Grid/RowAttributeRenderer.js')

                ->addJs('M2ePro/Attribute.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "description-policies");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Amazon::MENU_ROOT_NODE_NICK . '/configuration'
        );
    }

    //########################################

    public function indexAction()
    {
        return $this->_redirect('*/adminhtml_amazon_template/index');
    }

    public function gridAction()
    {
        $block = $this->loadLayout()->getLayout()
                                    ->createBlock('M2ePro/adminhtml_amazon_template_description_grid');

        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        /** @var Ess_M2ePro_Model_Amazon_Template_Description $templateModel */
        $id = $this->getRequest()->getParam('id');
        $templateModel = Mage::helper('M2ePro/Component_Amazon')->getModel('Template_Description')->load($id);

        if (!$templateModel->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy does not exist'));
            return $this->_redirect('*/*/index');
        }

        $marketplaces = Mage::helper('M2ePro/Component_Amazon')->getMarketplacesAvailableForAsinCreation();
        if ($marketplaces->getSize() <= 0) {
            $message = 'You should select and update at least one Amazon Marketplace.';
            $this->_getSession()->addError(Mage::helper('M2ePro')->__($message));
            return $this->_redirect('*/*/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $templateModel);

        $this->_initAction()
            ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_template_description_edit_tabs'))
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_template_description_edit'))
            ->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/*/index');
        }

        // Saving general data
        // ---------------------------------------
        /** @var Ess_M2ePro_Model_Template_Description $descriptionTemplate */
        $descriptionTemplate = Mage::helper('M2ePro/Component_Amazon')->getModel(
            'Template_Description'
        )->load($this->getRequest()->getParam('id'));

        $oldData = array();
        if ($descriptionTemplate->getId()) {
            $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_Description_SnapshotBuilder');
            $snapshotBuilder->setModel($descriptionTemplate);

            $oldData = $snapshotBuilder->getSnapshot();
        }

        Mage::getModel('M2ePro/Amazon_Template_Description_Builder')->build($descriptionTemplate, $post['general']);

        $id = $descriptionTemplate->getId();

        // Saving definition info
        // ---------------------------------------
        /** @var $descriptionDefinition Ess_M2ePro_Model_Amazon_Template_Description_Definition */
        $descriptionDefinition = Mage::getModel('M2ePro/Amazon_Template_Description_Definition');
        $descriptionDefinition->load($id);

        /** @var Ess_M2ePro_Model_Amazon_Template_Description_Definition_Builder $descriptionDefinitionBuilder */
        $descriptionDefinitionBuilder = Mage::getModel('M2ePro/Amazon_Template_Description_Definition_Builder');
        $descriptionDefinitionBuilder->setTemplateDescriptionId($id);
        $descriptionDefinitionBuilder->build($descriptionDefinition, $post['definition']);

        /** @var Ess_M2ePro_Model_Amazon_Template_Description $amazonDescriptionTemplate */
        $amazonDescriptionTemplate = $descriptionTemplate->getChildObject();
        $amazonDescriptionTemplate->setDefinitionTemplate($descriptionDefinition);

        // Saving specifics info
        // ---------------------------------------
        foreach ($amazonDescriptionTemplate->getSpecifics(true) as $specific) {
            $specific->deleteInstance();
        }

        $specifics = !empty($post['specifics']['encoded_data']) ? $post['specifics']['encoded_data'] : '';
        $specifics = (array)Mage::helper('M2ePro')->jsonDecode($specifics);

        $this->sortSpecifics($specifics, $post['general']['product_data_nick'], $post['general']['marketplace_id']);

        /** @var Ess_M2ePro_Model_Amazon_Template_Description_Specific_Builder $descriptionSpecificBuilder */
        $descriptionSpecificBuilder = Mage::getModel('M2ePro/Amazon_Template_Description_Specific_Builder');

        foreach ($specifics as $xpath => $specificData) {
            if (!$this->validateSpecificData($specificData)) {
                continue;
            }

            $specificData['xpath'] = $xpath;

            /** @var Ess_M2ePro_Model_Amazon_Template_Description_Specific $specificInstance */
            $specificInstance = Mage::getModel('M2ePro/Amazon_Template_Description_Specific');
            $descriptionSpecificBuilder->setTemplateDescriptionId($id);
            $descriptionSpecificBuilder->build($specificInstance, $specificData);
        }

        // Is Need Synchronize
        // ---------------------------------------
        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_Description_SnapshotBuilder');
        $snapshotBuilder->setModel($amazonDescriptionTemplate->getParentObject());
        $newData = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Amazon_Template_Description_Diff');
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = Mage::getModel('M2ePro/Amazon_Template_Description_AffectedListingsProducts');
        $affectedListingsProducts->setModel($amazonDescriptionTemplate);

        $changeProcessor = Mage::getModel('M2ePro/Amazon_Template_Description_ChangeProcessor');
        $changeProcessor->process(
            $diff, $affectedListingsProducts->getData(array('id', 'status'))
        );

        // Run Processor for Variation Relation Parents
        // ---------------------------------------
        if ($diff->isDetailsDifferent() || $diff->isImagesDifferent()) {

            /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
            $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product')
                 ->addFieldToFilter('template_description_id', $id)
                ->addFieldToFilter(
                    'is_general_id_owner', Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES
                )
                 ->addFieldToFilter('general_id', array('null' => true))
                 ->addFieldToFilter('is_variation_product', 1)
                 ->addFieldToFilter('is_variation_parent', 1);

            $massProcessor = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
            );
            $massProcessor->setListingsProducts($listingProductCollection->getItems());
            $massProcessor->setForceExecuting(false);

            $massProcessor->execute();
        }

        // ---------------------------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was saved'));
        return $this->_redirectUrl(
            Mage::helper('M2ePro')->getBackUrl('list', array(), array('edit' => array('id' => $id)))
        );
    }

    protected function validateSpecificData($specificData)
    {
        if (empty($specificData['mode'])) {
            return false;
        }

        if ($specificData['mode'] == Description_Specific::DICTIONARY_MODE_RECOMMENDED_VALUE &&
            (!isset($specificData['recommended_value']) || $specificData['recommended_value'] == '')
        ) {
            return false;
        }

        if ($specificData['mode'] == Description_Specific::DICTIONARY_MODE_CUSTOM_ATTRIBUTE &&
            (!isset($specificData['custom_attribute']) || $specificData['custom_attribute'] == '')
        ) {
            return false;
        }

        if ($specificData['mode'] == Description_Specific::DICTIONARY_MODE_CUSTOM_VALUE &&
            (!isset($specificData['custom_value']) || $specificData['custom_value'] == '')
        ) {
            return false;
        }

        return true;
    }

    protected function sortSpecifics(&$specifics, $productData, $marketplaceId)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_dictionary_specific');

        $dictionarySpecifics = $connRead->select()
            ->from($table, array('id', 'xpath'))
            ->where('product_data_nick = ?', $productData)
            ->where('marketplace_id = ?', $marketplaceId)
            ->query()->fetchAll();

        foreach ($dictionarySpecifics as $key => $specific) {
            $xpath = $specific['xpath'];
            unset($dictionarySpecifics[$key]);
            $dictionarySpecifics[$xpath] = $specific['id'];
        }

        Mage::helper('M2ePro/Data_Global')->setValue('dictionary_specifics', $dictionarySpecifics);

        function callback($aXpath, $bXpath)
        {
            $dictionarySpecifics = Mage::helper('M2ePro/Data_Global')->getValue('dictionary_specifics');

            $aXpathParts = explode('/', $aXpath);
            foreach ($aXpathParts as &$part) {
                $part = preg_replace('/\-\d+$/', '', $part);
            }

            unset($part);
            $aXpath = implode('/', $aXpathParts);

            $bXpathParts = explode('/', $bXpath);
            foreach ($bXpathParts as &$part) {
                $part = preg_replace('/\-\d+$/', '', $part);
            }

            unset($part);
            $bXpath = implode('/', $bXpathParts);

            $aIndex = $dictionarySpecifics[$aXpath];
            $bIndex = $dictionarySpecifics[$bXpath];

            return $aIndex > $bIndex ? 1 : -1;
        }

        uksort($specifics, 'callback');
    }

    // ---------------------------------------

    public function deleteAction()
    {
        $ids = $this->getRequestIds();

        if (empty($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Item(s) to remove.'));
            $this->_redirect('*/*/index');
            return;
        }

        $deleted = $locked = 0;
        foreach ($ids as $id) {
            $template = Mage::helper('M2ePro/Component')->getUnknownObject('Template_Description', $id);
            if ($template->isLocked()) {
                $locked++;
            } else {
                $template->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%s record(s) were deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__('%s record(s) are used in Listing(s).', $locked) . ' ';
        $tempString .= Mage::helper('M2ePro')->__('Policy must not be in use to be deleted.');
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/*/index');
    }

    //########################################

    public function getCategoryChooserHtmlAction()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Amazon_Template_Description_Category_Chooser_Edit $editBlock */
        $blockName = 'M2ePro/adminhtml_amazon_template_description_category_chooser_edit';
        $editBlock = $this->getLayout()->createBlock($blockName);
        $editBlock->setMarketplaceId($this->getRequest()->getPost('marketplace_id'));

        $browseNodeId = $this->getRequest()->getPost('browsenode_id');
        $categoryPath = $this->getRequest()->getPost('category_path');

        $recentlySelectedCategories = Mage::helper('M2ePro/Component_Amazon_Category')->getRecent(
            $this->getRequest()->getPost('marketplace_id'),
            array('browsenode_id' => $browseNodeId, 'path' => $categoryPath)
        );

        if (empty($recentlySelectedCategories)) {
            Mage::helper('M2ePro/Data_Global')->setValue('category_chooser_hide_recent', true);
        }

        if ($browseNodeId && $categoryPath) {
            $editBlock->setSelectedCategory(
                array(
                                                'browseNodeId' => $browseNodeId,
                                                'categoryPath' => $categoryPath
                )
            );
        }

        $this->getResponse()->setBody($editBlock->toHtml());
    }

    //########################################

    public function getCategoryInfoByBrowseNodeIdAction()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $queryStmt = $connRead->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_amazon_dictionary_category')
            )
            ->where('marketplace_id = ?', $this->getRequest()->getPost('marketplace_id'))
            ->where('browsenode_id = ?', $this->getRequest()->getPost('browsenode_id'))
            ->query();

        $tempCategories = array();

        while ($row = $queryStmt->fetch()) {
            $this->formatCategoryRow($row);
            $tempCategories[] = $row;
        }

        if (empty($tempCategories)) {
            return $this->getResponse()->setBody(null);
        }

        $dbCategoryPath = str_replace(' > ', '>', $this->getRequest()->getPost('category_path'));

        foreach ($tempCategories as $category) {
            $tempCategoryPath = $category['path'] !== null ? $category['path'] . '>' . $category['title']
                                                            : $category['title'];
            if ($tempCategoryPath == $dbCategoryPath) {
                return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($category));
            }
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($tempCategories[0]));
    }

    public function getCategoryInfoByCategoryIdAction()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $category = $connRead->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_amazon_dictionary_category')
            )
            ->where('marketplace_id = ?', $this->getRequest()->getPost('marketplace_id'))
            ->where('category_id = ?', $this->getRequest()->getPost('category_id'))
            ->query()
            ->fetch();

        if (!$category) {
            return $this->getResponse()->setBody(null);
        }

        $this->formatCategoryRow($category);
        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($category));
    }

    public function getChildCategoriesAction()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $connRead->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_amazon_dictionary_category')
            )
            ->where('marketplace_id = ?', $this->getRequest()->getPost('marketplace_id'))
            ->order('title ASC');

        $parentCategoryId = $this->getRequest()->getPost('parent_category_id');
        empty($parentCategoryId) ? $select->where('parent_category_id IS NULL')
                                 : $select->where('parent_category_id = ?', $parentCategoryId);

        $queryStmt = $select->query();
        $tempCategories = array();

        $sortIndex = 0;
        while ($row = $queryStmt->fetch()) {
            $this->formatCategoryRow($row);
            $this->isItOtherCategory($row) ? $tempCategories[10000] = $row
                                           : $tempCategories[$sortIndex++] = $row;
        }

        ksort($tempCategories);
        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array_values($tempCategories)));
    }

    public function searchCategoryAction()
    {
        if (!$keywords = $this->getRequest()->getParam('query', '')) {
            $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array()));
            return;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $connRead->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_amazon_dictionary_category')
            )
            ->where('is_leaf = 1')
            ->where('marketplace_id = ?', $this->getRequest()->getParam('marketplace_id'));

        $where = array();
        $where[] = "browsenode_id = {$connRead->quote($keywords)}";

        foreach (explode(' ', $keywords) as $part) {
            $part = trim($part);
            if ($part == '') {
                continue;
            }

            $part = $connRead->quote('%'.$part.'%');
            $where[] = "keywords LIKE {$part} OR title LIKE {$part}";
        }

        $select->where(implode(' OR ', $where))
               ->limit(200)
               ->order('id ASC');

        $categories = array();
        $queryStmt = $select->query();

        while ($row = $queryStmt->fetch()) {
            $this->formatCategoryRow($row);
            $categories[] = $row;
        }

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($categories));
    }

    public function saveRecentCategoryAction()
    {
        $marketplaceId = $this->getRequest()->getPost('marketplace_id');
        $browseNodeId  = $this->getRequest()->getPost('browsenode_id');
        $categoryPath  = $this->getRequest()->getPost('category_path');

        if (!$marketplaceId || !$browseNodeId || !$categoryPath) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => false)));
        }

        Mage::helper('M2ePro/Component_Amazon_Category')->addRecent(
            $marketplaceId, $browseNodeId, $categoryPath
        );
        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => true)));
    }

    public function getAvailableProductTypesAction()
    {
        $marketplaceId = (int)$this->getRequest()->getPost('marketplace_id');
        $browsenodeId  = $this->getRequest()->getPost('browsenode_id');

        $resource = Mage::getSingleton('core/resource');
        $tableName = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_dictionary_category_product_data');

        $queryStmt = $resource->getConnection('core_read')
               ->select()
               ->from($tableName)
               ->where('marketplace_id = ?', $marketplaceId)
               ->where('browsenode_id = ?', $browsenodeId)
               ->query();

        $cachedProductTypes = array();

        while ($row = $queryStmt->fetch()) {
            $cachedProductTypes[$row['product_data_nick']] = array(
                'product_data_nick'   => $row['product_data_nick'],
                'is_applicable'       => $row['is_applicable'],
                'required_attributes' => $row['required_attributes']
            );
        }

        $model = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
        $model->setMarketplaceId($marketplaceId);

        $allAvailableProductTypes = $model->getProductData();
        $shouldBeUpdatedProductTypes = array_diff(
            array_keys($allAvailableProductTypes),
            array_keys($cachedProductTypes)
        );

        if (!empty($shouldBeUpdatedProductTypes)) {
            $result = $this->updateProductDataNicksInfo($marketplaceId, $browsenodeId, $shouldBeUpdatedProductTypes);
            $cachedProductTypes = array_merge($cachedProductTypes, $result);
        }

        foreach ($cachedProductTypes as $nick => &$productTypeInfo) {
            if (!$productTypeInfo['is_applicable']) {
                unset($cachedProductTypes[$nick]);
                continue;
            }

            $productTypeInfo['title'] = isset($allAvailableProductTypes[$nick])
                ? $allAvailableProductTypes[$nick]['title'] : $nick;

            $productTypeInfo['group'] = isset($allAvailableProductTypes[$nick])
                ? $allAvailableProductTypes[$nick]['group'] : 'Other';

            $productTypeInfo['required_attributes'] = (array)Mage::helper('M2ePro')->jsonDecode(
                $productTypeInfo['required_attributes']
            );
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'product_data' => $cachedProductTypes,
                'grouped_data' => $this->getGroupedProductDataNicksInfo($cachedProductTypes),
                'recent_data'  => $this->getRecentProductDataNicksInfo($marketplaceId, $cachedProductTypes)
                )
            )
        );
    }

    protected function updateProductDataNicksInfo($marketplaceId, $browsenodeId, $productDataNicks)
    {
        $marketplaceNativeId = Mage::helper('M2ePro/Component_Amazon')
               ->getCachedObject('Marketplace', $marketplaceId)
               ->getNativeId();

        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'category', 'get', 'productsDataInfo',
            array(
                                                                   'marketplace'        => $marketplaceNativeId,
                                                                   'browsenode_id'      => $browsenodeId,
                                                                   'product_data_nicks' => $productDataNicks
            )
        );
        try {
            $dispatcherObject->process($connectorObj);
        } catch (Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
            return array();
        }

        $response = $connectorObj->getResponseData();

        if ($response === false || empty($response['info'])) {
            return array();
        }

        $insertsData = array();
        foreach ($response['info'] as $dataNickKey => $info) {
            $insertsData[$dataNickKey] = array(
                'marketplace_id'      => $marketplaceId,
                'browsenode_id'       => $browsenodeId,
                'product_data_nick'   => $dataNickKey,
                'is_applicable'       => (int)$info['applicable'],
                'required_attributes' => Mage::helper('M2ePro')->jsonEncode($info['required_attributes'])
            );
        }

        $resource = Mage::getSingleton('core/resource');
        $tableName = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_dictionary_category_product_data');

        $resource->getConnection('core_write')->insertMultiple($tableName, $insertsData);

        return $insertsData;
    }

    protected function getGroupedProductDataNicksInfo(array $cachedProductTypes)
    {
        $groupedData = array();

        foreach ($cachedProductTypes as $nick => $productTypeInfo) {
            $groupedData[$productTypeInfo['group']][$productTypeInfo['title']] = $productTypeInfo;
        }

        ksort($groupedData);
        foreach ($groupedData as $group => &$productTypes) {
            ksort($productTypes);
        }

        return $groupedData;
    }

    protected function getRecentProductDataNicksInfo($marketplaceId, array $cachedProductTypes)
    {
        $recentProductDataNicks = array();

        foreach (Mage::helper('M2ePro/Component_Amazon_ProductData')->getRecent($marketplaceId) as $nick) {
            if (!isset($cachedProductTypes[$nick]) || !$cachedProductTypes[$nick]['is_applicable']) {
                continue;
            }

            $recentProductDataNicks[$nick] = array(
                'title'               => $cachedProductTypes[$nick]['title'],
                'group'               => $cachedProductTypes[$nick]['group'],
                'product_data_nick'   => $nick,
                'is_applicable'       => 1,
                'required_attributes' => $cachedProductTypes[$nick]['required_attributes']
            );
        }

        return $recentProductDataNicks;
    }

    public function saveRecentProductDataNickAction()
    {
        $marketplaceId   = $this->getRequest()->getPost('marketplace_id');
        $productDataNick = $this->getRequest()->getPost('product_data_nick');

        if (!$marketplaceId || !$productDataNick) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => false)));
        }

        Mage::helper('M2ePro/Component_Amazon_ProductData')->addRecent($marketplaceId, $productDataNick);
        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => true)));
    }

    public function getVariationThemesAction()
    {
        $model = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
        $model->setMarketplaceId($this->getRequest()->getParam('marketplace_id'));

        $variationThemes = $model->getVariationThemes($this->getRequest()->getParam('product_data_nick'));
        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($variationThemes));
    }

    // ---------------------------------------

    protected function formatCategoryRow(&$row)
    {
        $row['product_data_nicks'] = $row['product_data_nicks'] !== null
            ? (array)Mage::helper('M2ePro')->jsonDecode($row['product_data_nicks']) : array();
    }

    protected function isItOtherCategory($row)
    {
        $parentTitle = explode('>', $row['path']);
        $parentTitle = array_pop($parentTitle);

        return preg_match("/^.* \({$parentTitle}\)$/i", $row['title']);
    }

    //########################################

    public function getAllSpecificsAction()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $tempSpecifics = $connRead->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_amazon_dictionary_specific')
            )
            ->where('marketplace_id = ?', $this->getRequest()->getParam('marketplace_id'))
            ->where('product_data_nick = ?', $this->getRequest()->getParam('product_data_nick'))
            ->query()->fetchAll();

        $helper = Mage::helper('M2ePro');

        $specifics = array();
        foreach ($tempSpecifics as $tempSpecific) {
            $tempSpecific['values']             = (array)$helper->jsonDecode($tempSpecific['values']);
            $tempSpecific['recommended_values'] = (array)$helper->jsonDecode($tempSpecific['recommended_values']);
            $tempSpecific['params']             = (array)$helper->jsonDecode($tempSpecific['params']);
            $tempSpecific['data_definition']    = (array)$helper->jsonDecode($tempSpecific['data_definition']);

            $specifics[$tempSpecific['specific_id']] = $tempSpecific;
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($specifics));
    }

    public function getAddSpecificsHtmlAction()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Amazon_Template_Description_Category_Specific_Add $addBlock */
        $blockName = 'M2ePro/adminhtml_amazon_template_description_category_specific_add';
        $addBlock = $this->getLayout()->createBlock($blockName);

        $gridBlock = $this->prepareGridBlock();
        $addBlock->setChild('specifics_grid', $gridBlock);

        $this->getResponse()->setBody($addBlock->toHtml());
    }

    public function getAddSpecificsGridHtmlAction()
    {
        $gridBlock = $this->prepareGridBlock();
        $this->getResponse()->setBody($gridBlock->toHtml());
    }

    protected function prepareGridBlock()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Amazon_Template_Description_Category_Specific_Add_Grid $grid */
        $blockName = 'M2ePro/adminhtml_amazon_template_description_category_specific_add_grid';
        $grid = $this->getLayout()->createBlock($blockName);

        $helper = Mage::helper('M2ePro');

        $grid->setMarketplaceId($this->getRequest()->getParam('marketplace_id'));
        $grid->setProductDataNick($this->getRequest()->getParam('product_data_nick'));
        $grid->setCurrentXpath($this->getRequest()->getParam('current_indexed_xpath'));
        $grid->setAllRenderedSpecifics(
            (array)$helper->jsonDecode($this->getRequest()->getParam('all_rendered_specifics'))
        );
        $grid->setBlockRenderedSpecifics(
            (array)$helper->jsonDecode($this->getRequest()->getParam('block_rendered_specifics'))
        );
        $grid->setSelectedSpecifics((array)$helper->jsonDecode($this->getRequest()->getParam('selected_specifics')));
        $grid->setOnlyDesired($this->getRequest()->getParam('only_desired', false));
        $grid->setSearchQuery($this->getRequest()->getParam('query'));

        return $grid;
    }

    public function getVariationThemeAttributesAction()
    {
        $model = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
        $model->setMarketplaceId($this->getRequest()->getParam('marketplace_id'));

        $variationThemes = $model->getVariationThemes($this->getRequest()->getParam('product_data_nick'));

        $attributes = array();
        foreach ($variationThemes as $themeName => $themeInfo) {
            foreach ($themeInfo['attributes'] as $attributeName) {
                if (isset($attributes[$attributeName]) && in_array($themeName, $attributes[$attributeName])) {
                    continue;
                }

                $attributes[$attributeName][] = $themeName;
            }
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($attributes));
    }

    //########################################
}
