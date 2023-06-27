<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Template_Category_Specific as Category_Specific;

class Ess_M2ePro_Adminhtml_Walmart_Template_CategoryController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Policies'))
             ->_title(Mage::helper('M2ePro')->__('Category Policies'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Template/Edit.js')
            ->addJs('M2ePro/Walmart/Template/Edit.js')
            ->addJs('M2ePro/Walmart/Template/Category.js')
            ->addJs('M2ePro/Walmart/Template/Category/Categories/Chooser.js')
            ->addJs('M2ePro/Walmart/Template/Category/Categories/Specific.js')
            ->addJs('M2ePro/Walmart/Template/Category/Categories/Specific/Renderer.js')
            ->addJs('M2ePro/Walmart/Template/Category/Categories/Specific/Dictionary.js')
            ->addJs('M2ePro/Walmart/Template/Category/Categories/Specific/BlockRenderer.js')
            ->addJs('M2ePro/Walmart/Template/Category/Categories/Specific/Block/GridRenderer.js')
            ->addJs('M2ePro/Walmart/Template/Category/Categories/Specific/Block/AddSpecificRenderer.js')
            ->addJs('M2ePro/Walmart/Template/Category/Categories/Specific/Grid/RowRenderer.js')
            ->addJs('M2ePro/Walmart/Template/Category/Categories/Specific/Grid/RowAttributeRenderer.js')

            ->addJs('M2ePro/Attribute.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "walmart-integration");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Walmart::MENU_ROOT_NODE_NICK . '/configuration'
        );
    }

    //########################################

    public function indexAction()
    {
        return $this->_redirect('*/adminhtml_walmart_template/index');
    }

    public function gridAction()
    {
        $block = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_template_category_grid');

        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Walmart_Template_Category')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy does not exist'));
            return $this->_redirect('*/adminhtml_walmart_template/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_template_category_edit')
            )
             ->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->indexAction();
        }

        /** @var Ess_M2ePro_Model_Walmart_Template_Category $categoryTemplate */
        $categoryTemplate = Mage::getModel('M2ePro/Walmart_Template_Category')
            ->load($this->getRequest()->getParam('id'));

        $oldData = array();
        if ($categoryTemplate->getId()) {
            $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Category_SnapshotBuilder');
            $snapshotBuilder->setModel($categoryTemplate);

            $oldData = $snapshotBuilder->getSnapshot();
        }

        Mage::getModel('M2ePro/Walmart_Template_Category_Builder')->build($categoryTemplate, $post);
        // ---------------------------------------

        $id = $categoryTemplate->getId();

        // Saving specifics info
        // ---------------------------------------
        foreach ($categoryTemplate->getSpecifics(true) as $specific) {
            $specific->deleteInstance();
        }

        $specifics = !empty($post['encoded_data']) ? $post['encoded_data'] : '';
        $specifics = (array)Mage::helper('M2ePro')->jsonDecode($specifics);

        $this->sortSpecifics($specifics, $post['product_data_nick'], $post['marketplace_id']);

        /** @var Ess_M2ePro_Model_Walmart_Template_Category_Specific_Builder $categorySpecificBuilder */
        $categorySpecificBuilder = Mage::getModel('M2ePro/Walmart_Template_Category_Specific_Builder');

        foreach ($specifics as $xpath => $specificData) {
            if (!$this->validateSpecificData($specificData)) {
                continue;
            }

            $specificData['xpath'] = $xpath;

            /** @var Ess_M2ePro_Model_Walmart_Template_Category_Specific $specificInstance */
            $specificInstance = Mage::getModel('M2ePro/Walmart_Template_Category_Specific');
            $categorySpecificBuilder->setTemplateCategoryId($id);
            $categorySpecificBuilder->build($specificInstance, $specificData);
        }

        // Is Need Synchronize
        // ---------------------------------------
        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Category_SnapshotBuilder');
        $snapshotBuilder->setModel($categoryTemplate);
        $newData = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Walmart_Template_Category_Diff');
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = Mage::getModel('M2ePro/Walmart_Template_Category_AffectedListingsProducts');
        $affectedListingsProducts->setModel($categoryTemplate);

        $changeProcessor = Mage::getModel('M2ePro/Walmart_Template_Category_ChangeProcessor');
        $changeProcessor->process(
            $diff, $affectedListingsProducts->getData(array('id', 'status'), array('only_physical_units' => true))
        );
        // ---------------------------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was saved'));
        return $this->_redirectUrl(
            Mage::helper('M2ePro')->getBackUrl('index', array(), array('edit' => array('id' => $id)))
        );
    }

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
            $template = Mage::getModel('M2ePro/Walmart_Template_Category')->load($id);
            if ($template->isLocked()) {
                $locked++;
            } else {
                $template->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%amount% record(s) were deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__('%amount% record(s) are used in Listing(s).', $locked) . ' ';
        $tempString .= Mage::helper('M2ePro')->__('Policy must not be in use to be deleted.');
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/adminhtml_walmart_template/index');
    }

    //########################################

    public function getCategoryChooserHtmlAction()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Template_Category_Categories_Chooser_Edit $editBlock */
        $blockName = 'M2ePro/adminhtml_walmart_template_category_categories_chooser_edit';
        $editBlock = $this->getLayout()->createBlock($blockName);
        $editBlock->setMarketplaceId($this->getRequest()->getPost('marketplace_id'));

        $browseNodeId = $this->getRequest()->getPost('browsenode_id');
        $categoryPath = $this->getRequest()->getPost('category_path');

        $recentlySelectedCategories = Mage::helper('M2ePro/Component_Walmart_Category')->getRecent(
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
                    ->getTableNameWithPrefix('m2epro_walmart_dictionary_category')
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
                    ->getTableNameWithPrefix('m2epro_walmart_dictionary_category')
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
                    ->getTableNameWithPrefix('m2epro_walmart_dictionary_category')
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
                    ->getTableNameWithPrefix('m2epro_walmart_dictionary_category')
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

        Mage::helper('M2ePro/Component_Walmart_Category')->addRecent(
            $marketplaceId, $browseNodeId, $categoryPath
        );
        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => true)));
    }

    public function getVariationThemesAction()
    {
        $model = Mage::getModel('M2ePro/Walmart_Marketplace_Details');
        $model->setMarketplaceId($this->getRequest()->getParam('marketplace_id'));

        $variationThemes = $model->getVariationAttributes($this->getRequest()->getParam('product_data_nick'));
        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($variationThemes));
    }

    public function getVariationThemeAttributesAction()
    {
        $model = Mage::getModel('M2ePro/Walmart_Marketplace_Details');
        $model->setMarketplaceId($this->getRequest()->getParam('marketplace_id'));

        $variationThemes = $model->getVariationAttributes($this->getRequest()->getParam('product_data_nick'));

        $attributes = array();
        foreach ($variationThemes as $themeName => $themeInfo) {
            $attributeName = $themeInfo;
            if (isset($attributes[$attributeName]) && in_array($themeName, $attributes[$attributeName])) {
                continue;
            }

            $attributes[$attributeName][] = $themeName;
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($attributes));
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
                    ->getTableNameWithPrefix('m2epro_walmart_dictionary_specific')
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
        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Template_Category_Categories_Specific_Add $addBlock */
        $blockName = 'M2ePro/adminhtml_walmart_template_category_categories_specific_add';
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
        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Template_Category_Categories_Specific_Add_Grid $grid */
        $blockName = 'M2ePro/adminhtml_walmart_template_category_categories_specific_add_grid';
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

    protected function validateSpecificData($specificData)
    {
        if (empty($specificData['mode'])) {
            return false;
        }

        if ($specificData['mode'] == Category_Specific::DICTIONARY_MODE_RECOMMENDED_VALUE &&
            (!isset($specificData['recommended_value']) || $specificData['recommended_value'] == '')
        ) {
            return false;
        }

        if ($specificData['mode'] == Category_Specific::DICTIONARY_MODE_CUSTOM_ATTRIBUTE &&
            (!isset($specificData['custom_attribute']) || $specificData['custom_attribute'] == '')
        ) {
            return false;
        }

        if ($specificData['mode'] == Category_Specific::DICTIONARY_MODE_CUSTOM_VALUE &&
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
            ->getTableNameWithPrefix('m2epro_walmart_dictionary_specific');

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

    //########################################
}
