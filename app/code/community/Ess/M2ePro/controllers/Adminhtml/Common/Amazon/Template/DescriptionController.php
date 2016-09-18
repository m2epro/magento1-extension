<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Amazon_Template_DescriptionController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Policies'))
             ->_title(Mage::helper('M2ePro')->__('Description Policies'));

        $this->getLayout()->getBlock('head')
                ->addJs('M2ePro/Common/Amazon/Template/Description/Handler.js')
                ->addJs('M2ePro/Common/Amazon/Template/Description/DefinitionHandler.js')
                ->addJs('M2ePro/Common/Amazon/Template/Description/Category/ChooserHandler.js')
                ->addJs('M2ePro/Common/Amazon/Template/Description/Category/SpecificHandler.js')
                ->addJs('M2ePro/Common/Amazon/Template/Description/Category/Specific/Renderer.js')
                ->addJs('M2ePro/Common/Amazon/Template/Description/Category/Specific/Dictionary.js')
                ->addJs('M2ePro/Common/Amazon/Template/Description/Category/Specific/BlockRenderer.js')
                ->addJs('M2ePro/Common/Amazon/Template/Description/Category/Specific/Block/GridRenderer.js')
                ->addJs('M2ePro/Common/Amazon/Template/Description/Category/Specific/Block/AddSpecificRenderer.js')
                ->addJs('M2ePro/Common/Amazon/Template/Description/Category/Specific/Grid/RowRenderer.js')
                ->addJs('M2ePro/Common/Amazon/Template/Description/Category/Specific/Grid/RowAttributeRenderer.js')

                ->addJs('M2ePro/AttributeHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(Ess_M2ePro_Helper_Component_Amazon::NICK, 'Description+Policy');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/configuration');
    }

    //########################################

    public function indexAction()
    {
        return $this->_redirect('*/adminhtml_common_template/index', array(
            'channel' => Ess_M2ePro_Helper_Component_Amazon::NICK
        ));
    }

    public function gridAction()
    {
        $block = $this->loadLayout()->getLayout()
                                    ->createBlock('M2ePro/adminhtml_common_amazon_template_description_grid');

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
            ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_template_description_edit_tabs'))
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_template_description_edit'))
            ->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/*/index');
        }

        $id = $this->getRequest()->getParam('id');

        // Saving general data
        // ---------------------------------------
        $keys = array(
            'title',
            'marketplace_id',
            'is_new_asin_accepted',

            'category_path',
            'product_data_nick',
            'browsenode_id',

            'registered_parameter',

            'worldwide_id_mode',
            'worldwide_id_custom_attribute'
        );

        $dataForAdd = array();
        foreach ($keys as $key) {
            isset($post['general'][$key]) && $dataForAdd[$key] = $post['general'][$key];
        }

        $dataForAdd['title'] = strip_tags($dataForAdd['title']);

        /** @var Ess_M2ePro_Model_Template_Description $descriptionTemplate */
        $descriptionTemplate = Mage::helper('M2ePro/Component_Amazon')->getModel('Template_Description')->load($id);

        $oldData = array();
        if ($descriptionTemplate->getId()) {
            $oldData = $descriptionTemplate->getChildObject()->getDataSnapshot();
        }

        $descriptionTemplate->addData($dataForAdd)->save();
        // ---------------------------------------

        $id = $descriptionTemplate->getId();

        // Saving definition info
        // ---------------------------------------
        $keys = array(
            'title_mode',
            'title_template',

            'brand_mode',
            'brand_custom_value',
            'brand_custom_attribute',

            'manufacturer_mode',
            'manufacturer_custom_value',
            'manufacturer_custom_attribute',

            'manufacturer_part_number_mode',
            'manufacturer_part_number_custom_value',
            'manufacturer_part_number_custom_attribute',

            'item_package_quantity_mode',
            'item_package_quantity_custom_value',
            'item_package_quantity_custom_attribute',

            'number_of_items_mode',
            'number_of_items_custom_value',
            'number_of_items_custom_attribute',

            'item_dimensions_volume_mode',
            'item_dimensions_volume_length_custom_value',
            'item_dimensions_volume_width_custom_value',
            'item_dimensions_volume_height_custom_value',
            'item_dimensions_volume_length_custom_attribute',
            'item_dimensions_volume_width_custom_attribute',
            'item_dimensions_volume_height_custom_attribute',
            'item_dimensions_volume_unit_of_measure_mode',
            'item_dimensions_volume_unit_of_measure_custom_value',
            'item_dimensions_volume_unit_of_measure_custom_attribute',

            'item_dimensions_weight_mode',
            'item_dimensions_weight_custom_value',
            'item_dimensions_weight_custom_attribute',
            'item_dimensions_weight_unit_of_measure_mode',
            'item_dimensions_weight_unit_of_measure_custom_value',
            'item_dimensions_weight_unit_of_measure_custom_attribute',

            'package_dimensions_volume_mode',
            'package_dimensions_volume_length_custom_value',
            'package_dimensions_volume_width_custom_value',
            'package_dimensions_volume_height_custom_value',
            'package_dimensions_volume_length_custom_attribute',
            'package_dimensions_volume_width_custom_attribute',
            'package_dimensions_volume_height_custom_attribute',
            'package_dimensions_volume_unit_of_measure_mode',
            'package_dimensions_volume_unit_of_measure_custom_value',
            'package_dimensions_volume_unit_of_measure_custom_attribute',

            'package_weight_mode',
            'package_weight_custom_value',
            'package_weight_custom_attribute',
            'package_weight_unit_of_measure_mode',
            'package_weight_unit_of_measure_custom_value',
            'package_weight_unit_of_measure_custom_attribute',

            'shipping_weight_mode',
            'shipping_weight_custom_value',
            'shipping_weight_custom_attribute',
            'shipping_weight_unit_of_measure_mode',
            'shipping_weight_unit_of_measure_custom_value',
            'shipping_weight_unit_of_measure_custom_attribute',

            'target_audience_mode',
            'target_audience',

            'search_terms_mode',
            'search_terms',

            'image_main_mode',
            'image_main_attribute',

            'image_variation_difference_mode',
            'image_variation_difference_attribute',

            'gallery_images_mode',
            'gallery_images_attribute',
            'gallery_images_limit',

            'bullet_points_mode',
            'bullet_points',

            'description_mode',
            'description_template',
        );

        $dataForAdd = array();
        foreach ($keys as $key) {
            isset($post['definition'][$key]) && $dataForAdd[$key] = $post['definition'][$key];
        }

        $dataForAdd['template_description_id'] = $id;

        $dataForAdd['target_audience'] = json_encode(array_filter($dataForAdd['target_audience']));
        $dataForAdd['search_terms']    = json_encode(array_filter($dataForAdd['search_terms']));
        $dataForAdd['bullet_points']   = json_encode(array_filter($dataForAdd['bullet_points']));

        /* @var $descriptionDefinition Ess_M2ePro_Model_Amazon_Template_Description_Definition */
        $descriptionDefinition = Mage::getModel('M2ePro/Amazon_Template_Description_Definition');
        $descriptionDefinition->load($id);
        $descriptionDefinition->addData($dataForAdd)->save();
        // ---------------------------------------

        /** @var Ess_M2ePro_Model_Amazon_Template_Description $amazonDescriptionTemplate */
        $amazonDescriptionTemplate = $descriptionTemplate->getChildObject();
        $amazonDescriptionTemplate->setDefinitionTemplate($descriptionDefinition);

        // Saving specifics info
        // ---------------------------------------
        foreach ($amazonDescriptionTemplate->getSpecifics(true) as $specific) {
            $specific->deleteInstance();
        }

        $specifics = !empty($post['specifics']['encoded_data']) ? $post['specifics']['encoded_data'] : '';
        $specifics = (array)json_decode($specifics, true);

        $this->sortSpecifics($specifics, $post['general']['product_data_nick'], $post['general']['marketplace_id']);

        foreach ($specifics as $xpath => $specificData) {

            if (!$this->validateSpecificData($specificData)) {
                continue;
            }

            $specificInstance = Mage::getModel('M2ePro/Amazon_Template_Description_Specific');

            $type       = isset($specificData['type']) ? $specificData['type'] : '';
            $isRequired = isset($specificData['is_required']) ? $specificData['is_required'] : 0;
            $attributes = isset($specificData['attributes']) ? json_encode($specificData['attributes']) : '[]';

            $recommendedValue = $specificData['mode'] == $specificInstance::DICTIONARY_MODE_RECOMMENDED_VALUE
                ? $specificData['recommended_value'] : '';

            $customValue      = $specificData['mode'] == $specificInstance::DICTIONARY_MODE_CUSTOM_VALUE
                ? $specificData['custom_value'] : '';

            $customAttribute  = $specificData['mode'] == $specificInstance::DICTIONARY_MODE_CUSTOM_ATTRIBUTE
                ? $specificData['custom_attribute'] : '';

            $specificInstance->addData(array(
                'template_description_id' => $id,
                'xpath'                   => $xpath,
                'mode'                    => $specificData['mode'],
                'is_required'             => $isRequired,
                'recommended_value'       => $recommendedValue,
                'custom_value'            => $customValue,
                'custom_attribute'        => $customAttribute,
                'type'                    => $type,
                'attributes'              => $attributes
            ));
            $specificInstance->save();
        }
        // ---------------------------------------

        // Is Need Synchronize
        // ---------------------------------------
        $newData = $amazonDescriptionTemplate->getDataSnapshot();
        $amazonDescriptionTemplate->setSynchStatusNeed($newData, $oldData);
        // ---------------------------------------

        // Run Processor for Variation Relation Parents
        // ---------------------------------------
        if ($amazonDescriptionTemplate->getResource()->isDifferent($newData, $oldData)) {

            /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
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

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was successfully saved'));
        return $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
    }

    private function validateSpecificData($specificData)
    {
        if (empty($specificData['mode'])) {
            return false;
        }

        if (empty($specificData['recommended_value']) &&
            !in_array($specificData['mode'], array('none','custom_value','custom_attribute'))) {
            return false;
        }
        if (empty($specificData['custom_value']) &&
            !in_array($specificData['mode'], array('none','recommended_value','custom_attribute'))) {
            return false;
        }
        if (empty($specificData['custom_attribute']) &&
            !in_array($specificData['mode'], array('none','recommended_value','custom_value'))) {
            return false;
        }

        return true;
    }

    private function sortSpecifics(&$specifics, $productData, $marketplaceId)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_specific');

        $dictionarySpecifics = $connRead->select()
            ->from($table,array('id', 'xpath'))
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

            $aXpathParts = explode('/',$aXpath);
            foreach ($aXpathParts as &$part) {
                $part = preg_replace('/\-\d+$/','',$part);
            }
            unset($part);
            $aXpath = implode('/',$aXpathParts);

            $bXpathParts = explode('/',$bXpath);
            foreach ($bXpathParts as &$part) {
                $part = preg_replace('/\-\d+$/','',$part);
            }
            unset($part);
            $bXpath = implode('/',$bXpathParts);

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

        if (count($ids) == 0) {
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

        $tempString = Mage::helper('M2ePro')->__('%s record(s) were successfully deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__('%s record(s) are used in Listing(s).', $locked) . ' ';
        $tempString .= Mage::helper('M2ePro')->__('Policy must not be in use to be deleted.');
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/*/index');
    }

    //########################################

    public function getCategoryChooserHtmlAction()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Description_Category_Chooser_Edit $editBlock */
        $blockName = 'M2ePro/adminhtml_common_amazon_template_description_category_chooser_edit';
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
            $editBlock->setSelectedCategory(array(
                                                'browseNodeId' => $browseNodeId,
                                                'categoryPath' => $categoryPath
                                            ));
        }

        $this->getResponse()->setBody($editBlock->toHtml());
    }

    //########################################

    public function getCategoryInfoByBrowseNodeIdAction()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $queryStmt = $connRead->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category'))
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

            $tempCategoryPath = !is_null($category['path']) ? $category['path'] .'>'. $category['title']
                                                            : $category['title'];
            if ($tempCategoryPath == $dbCategoryPath) {
                return $this->getResponse()->setBody(json_encode($category));
            }
        }

        return $this->getResponse()->setBody(json_encode($tempCategories[0]));
    }

    public function getCategoryInfoByCategoryIdAction()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $category = $connRead->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category'))
            ->where('marketplace_id = ?', $this->getRequest()->getPost('marketplace_id'))
            ->where('category_id = ?', $this->getRequest()->getPost('category_id'))
            ->query()
            ->fetch();

        if (!$category) {
            return $this->getResponse()->setBody(null);
        }

        $this->formatCategoryRow($category);
        return $this->getResponse()->setBody(json_encode($category));
    }

    public function getChildCategoriesAction()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $connRead->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category'))
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
        return $this->getResponse()->setBody(json_encode(array_values($tempCategories)));
    }

    public function searchCategoryAction()
    {
        if (!$keywords = $this->getRequest()->getParam('query', '')) {
            $this->getResponse()->setBody(json_encode(array()));
            return;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $connRead->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category'))
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

        $this->getResponse()->setBody(json_encode($categories));
    }

    public function saveRecentCategoryAction()
    {
        $marketplaceId = $this->getRequest()->getPost('marketplace_id');
        $browseNodeId  = $this->getRequest()->getPost('browsenode_id');
        $categoryPath  = $this->getRequest()->getPost('category_path');

        if (!$marketplaceId || !$browseNodeId || !$categoryPath) {
            return $this->getResponse()->setBody(json_encode(array('result' => false)));
        }

        Mage::helper('M2ePro/Component_Amazon_Category')->addRecent(
            $marketplaceId, $browseNodeId, $categoryPath
        );
        return $this->getResponse()->setBody(json_encode(array('result' => true)));
    }

    public function getAvailableProductTypesAction()
    {
        $marketplaceId = (int)$this->getRequest()->getPost('marketplace_id');
        $browsenodeId  = $this->getRequest()->getPost('browsenode_id');

        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName('m2epro_amazon_dictionary_category_product_data');

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
        $shouldBeUpdatedProductTypes = array_diff(array_keys($allAvailableProductTypes),
                                                  array_keys($cachedProductTypes));

        if (count($shouldBeUpdatedProductTypes) > 0) {

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

            $productTypeInfo['required_attributes'] = (array)json_decode($productTypeInfo['required_attributes'], true);
        }

        return $this->getResponse()->setBody(json_encode(array(
            'product_data' => $cachedProductTypes,
            'grouped_data' => $this->getGroupedProductDataNicksInfo($cachedProductTypes),
            'recent_data'  => $this->getRecentProductDataNicksInfo($marketplaceId, $cachedProductTypes)
         )));
    }

    private function updateProductDataNicksInfo($marketplaceId, $browsenodeId, $productDataNicks)
    {
        $marketplaceNativeId = Mage::helper('M2ePro/Component_Amazon')
               ->getCachedObject('Marketplace', $marketplaceId)
               ->getNativeId();

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('category','get','productsDataInfo',
                                                               array(
                                                                   'marketplace'        => $marketplaceNativeId,
                                                                   'browsenode_id'      => $browsenodeId,
                                                                   'product_data_nicks' => $productDataNicks
                                                               ));
        $response = $dispatcherObject->process($connectorObj);

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
                'required_attributes' => json_encode($info['required_attributes'])
            );
        }

        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName('m2epro_amazon_dictionary_category_product_data');

        $resource->getConnection('core_write')->insertMultiple($tableName, $insertsData);

        return $insertsData;
    }

    private function getGroupedProductDataNicksInfo(array $cachedProductTypes)
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

    private function getRecentProductDataNicksInfo($marketplaceId, array $cachedProductTypes)
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
            return $this->getResponse()->setBody(json_encode(array('result' => false)));
        }

        Mage::helper('M2ePro/Component_Amazon_ProductData')->addRecent($marketplaceId, $productDataNick);
        return $this->getResponse()->setBody(json_encode(array('result' => true)));
    }

    public function getVariationThemesAction()
    {
        $model = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
        $model->setMarketplaceId($this->getRequest()->getParam('marketplace_id'));

        $variationThemes = $model->getVariationThemes($this->getRequest()->getParam('product_data_nick'));
        return $this->getResponse()->setBody(json_encode($variationThemes));
    }

    // ---------------------------------------

    private function formatCategoryRow(&$row)
    {
        $row['product_data_nicks'] = !is_null($row['product_data_nicks'])
            ? (array)json_decode($row['product_data_nicks'], true) : array();
    }

    private function isItOtherCategory($row)
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
            ->from(Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_specific'))
            ->where('marketplace_id = ?', $this->getRequest()->getParam('marketplace_id'))
            ->where('product_data_nick = ?', $this->getRequest()->getParam('product_data_nick'))
            ->query()->fetchAll();

        $specifics = array();
        foreach ($tempSpecifics as $tempSpecific) {

            $tempSpecific['values']             = (array)json_decode($tempSpecific['values'], true);
            $tempSpecific['recommended_values'] = (array)json_decode($tempSpecific['recommended_values'], true);
            $tempSpecific['params']             = (array)json_decode($tempSpecific['params'], true);
            $tempSpecific['data_definition']    = (array)json_decode($tempSpecific['data_definition'], true);

            $specifics[$tempSpecific['specific_id']] = $tempSpecific;
        }

        return $this->getResponse()->setBody(json_encode($specifics));
    }

    public function getAddSpecificsHtmlAction()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Description_Category_Specific_Add $addBlock */
        $blockName = 'M2ePro/adminhtml_common_amazon_template_description_category_specific_add';
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

    private function prepareGridBlock()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Description_Category_Specific_Add_Grid $grid */
        $blockName = 'M2ePro/adminhtml_common_amazon_template_description_category_specific_add_grid';
        $grid = $this->getLayout()->createBlock($blockName);

        $grid->setMarketplaceId($this->getRequest()->getParam('marketplace_id'));
        $grid->setProductDataNick($this->getRequest()->getParam('product_data_nick'));
        $grid->setCurrentXpath($this->getRequest()->getParam('current_indexed_xpath'));
        $grid->setRenderedSpecifics((array)json_decode($this->getRequest()->getParam('rendered_specifics'), true));
        $grid->setSelectedSpecifics((array)json_decode($this->getRequest()->getParam('selected_specifics'), true));
        $grid->setOnlyDesired($this->getRequest()->getParam('only_desired'), false);
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

        return $this->getResponse()->setBody(json_encode($attributes));
    }

    //########################################
}