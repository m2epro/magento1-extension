<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Component_Ebay_Category as eBayCategory;
use Ess_M2ePro_Model_Ebay_Template_Category as TemplateCategory;

class Ess_M2ePro_Adminhtml_Ebay_CategoryController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Configuration'));

        $this->getLayout()->getBlock('head')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/Listing/ProductGrid.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')
            ->addJs('M2ePro/Ebay/Category/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/Category.js')
            ->addJs('M2ePro/Ebay/Template/Category/Chooser.js')
            ->addJs('M2ePro/Ebay/Template/Category/Chooser/Browse.js')
            ->addJs('M2ePro/Ebay/Template/Category/Specifics.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "configuration");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/configuration'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
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

    public function deleteAction()
    {
        $ids = $this->getRequestIds();

        if (empty($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Item(s) to remove.'));
            $this->_redirect('*/*/index');
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Collection_Abstract $collection */
        $collection = Mage::getModel('M2ePro/Ebay_Template_Category')->getCollection();
        $collection->addFieldToFilter('id', array('in' => $ids));

        $deleted = $locked = 0;
        foreach ($collection->getItems() as $template) {
            if ($template->isLocked()) {
                $locked++;
                continue;
            }

            $template->deleteInstance();
            $deleted++;
        }

        $tempString = Mage::helper('M2ePro')->__('%s record(s) were deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__(
            '[%count%] Category cannot be removed until it’s unassigned from the existing products.
            Read the <a href="%url%" target="_blank">article</a> for more information.',
            $locked,
            Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, 'set-categories')
        );
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/*/index');
    }

    //########################################

    public function viewAction()
    {
        $this->setRuleData('ebay_rule_category');
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_configuration_category_view'));
        $this->renderLayout();
    }

    public function viewPrimaryGridAction()
    {
        $this->setRuleData('ebay_rule_category');

        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_configuration_category_view_tabs_productsPrimary_grid')
            ->toHtml();

        $this->getResponse()->setBody($response);
    }

    public function viewSecondaryGridAction()
    {
        $this->setRuleData('ebay_rule_category');

        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_configuration_category_view_tabs_productsSecondary_grid')
            ->toHtml();

        $this->getResponse()->setBody($response);
    }

    //########################################

    public function saveTemplateCategorySpecificsAction()
    {
        $post = $this->getRequest()->getPost();

        if (empty($post['template_id'])) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Template not found.'));
            $this->_redirect('*/*/index');
            return;
        }

        $model = Mage::getModel('M2ePro/Ebay_Template_Category')->loadInstance(
            (int)$post['template_id']
        );

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_SnapshotBuilder $snapshotBuilder */
        $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Template_Category_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $oldSnapshot = $snapshotBuilder->getSnapshot();

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_Builder $builder */
        $builder = Mage::getModel('M2ePro/Ebay_Template_Category_Builder');
        $builder->build($model, $post);

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_SnapshotBuilder $snapshotBuilder */
        $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Template_Category_SnapshotBuilder');
        $snapshotBuilder->setModel($model);
        $newSnapshot = $snapshotBuilder->getSnapshot();

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_Diff $diff */
        $diff = Mage::getModel('M2ePro/Ebay_Template_Category_Diff');
        $diff->setNewSnapshot($newSnapshot);
        $diff->setOldSnapshot($oldSnapshot);

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_AffectedListingsProducts $affectedListingsProducts */
        $affectedListingsProducts = Mage::getModel(
            'M2ePro/Ebay_Template_Category_AffectedListingsProducts'
        );
        $affectedListingsProducts->setModel($model);

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_ChangeProcessor $changeProcessor */
        $changeProcessor = Mage::getModel('M2ePro/Ebay_Template_Category_ChangeProcessor');
        $changeProcessor->process($diff, $affectedListingsProducts->getData(array('id', 'status')));

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Category data was saved.'));

        if ($this->getRequest()->getParam('back') === 'edit') {
            return $this->_redirect('*/*/view', array('template_id' => $post['template_id']));
        }

        return $this->_redirect('*/*/index');
    }

    //########################################

    public function getChooserHtmlAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $accountId     = $this->getRequest()->getParam('account_id');
        $categoryMode  = $this->getRequest()->getParam('category_mode');
        $isEditAllowed = $this->getRequest()->getParam('is_edit_category_allowed', true);

        $selectedCategories = array();
        if ($categoriesJson = $this->getRequest()->getParam('selected_categories')) {
            $selectedCategories = Mage::helper('M2ePro')->jsonDecode($categoriesJson);
        }

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser $chooserBlock */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_category_chooser');
        $marketplaceId && $chooserBlock->setMarketplaceId($marketplaceId);
        $accountId && $chooserBlock->setAccountId($accountId);
        $chooserBlock->setCategoryMode($categoryMode);
        $chooserBlock->setIsEditCategoryAllowed($isEditAllowed);

        if (!empty($selectedCategories)) {
            /** @var Ess_M2ePro_Model_Ebay_Template_Category_Chooser_Converter $converter */
            $converter = Mage::getModel('M2ePro/Ebay_Template_Category_Chooser_Converter');
            $marketplaceId && $converter->setMarketplaceId($marketplaceId);
            $accountId && $converter->setAccountId($accountId);

            $helper = Mage::helper('M2ePro/Component_Ebay_Category');
            foreach ($selectedCategories as $type => $selectedCategory) {
                if (empty($selectedCategory)) {
                    continue;
                }

                $converter->setCategoryDataFromChooser($selectedCategory, $type);

                if ($selectedCategory['mode'] == TemplateCategory::CATEGORY_MODE_EBAY) {
                    $helper->isEbayCategoryType($type)
                        ? $helper->addRecent($selectedCategory['value'], $marketplaceId, $type)
                        : $helper->addRecent($selectedCategory['value'], $accountId, $type);
                }
            }

            $chooserBlock->setCategoriesData($converter->getCategoryDataForChooser());
        }

        $this->getResponse()->setBody($chooserBlock->toHtml());
    }

    public function getChooserEditHtmlAction()
    {
        $categoryType = $this->getRequest()->getParam('category_type');
        $selectedMode = $this->getRequest()->getParam('selected_mode', TemplateCategory::CATEGORY_MODE_NONE);
        $selectedValue = $this->getRequest()->getParam('selected_value');
        $selectedPath = $this->getRequest()->getParam('selected_path');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $accountId = $this->getRequest()->getParam('account_id');

        $editBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_category_chooser_edit');
        $editBlock->setCategoryType($categoryType);

        $helper = Mage::helper('M2ePro/Component_Ebay_Category');
        if ($helper->isEbayCategoryType($categoryType)) {
            $recentCategories = $helper->getRecent($marketplaceId, $categoryType, $selectedValue);
        } else {
            $recentCategories = $helper->getRecent($accountId, $categoryType, $selectedValue);
        }

        if (empty($recentCategories)) {
            Mage::helper('M2ePro/Data_Global')->setValue('category_chooser_hide_recent', true);
        }

        if ($selectedMode != TemplateCategory::CATEGORY_MODE_NONE) {
            $editBlock->setSelectedCategory(
                array(
                    'mode'  => $selectedMode,
                    'value' => $selectedValue,
                    'path'  => $selectedPath
                )
            );
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

        if ((in_array($categoryType, $ebayCategoryTypes) && $marketplaceId === null) ||
            (in_array($categoryType, $storeCategoryTypes) && $accountId === null)
        ) {
            $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($data));
            return;
        }

        if (in_array($categoryType, $ebayCategoryTypes)) {
            $data = Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Marketplace', $marketplaceId)
                ->getChildObject()
                ->getChildCategories($parentCategoryId);
        } elseif (in_array($categoryType, $storeCategoryTypes)) {
            $tableAccountStoreCategories = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_ebay_account_store_category');

            /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
            $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

            $dbSelect = $connRead->select()
                ->from($tableAccountStoreCategories, '*')
                ->where('`account_id` = ?', (int)$accountId)
                ->where('`parent_id` = ?', $parentCategoryId)
                ->order(array('sorder ASC'));

            $data = $connRead->fetchAll($dbSelect);
        }

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($data));
    }

    public function getSelectedCategoryDetailsAction()
    {
        $details = array(
            'path'               => '',
            'interface_path'     => '',
            'template_id'        => null,
            'is_custom_template' => null
        );
        $categoryHelper = Mage::helper('M2ePro/Component_Ebay_Category');

        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $accountId = $this->getRequest()->getParam('account_id');
        $value = $this->getRequest()->getParam('value');
        $mode = $this->getRequest()->getParam('mode');
        $categoryType = $this->getRequest()->getParam('category_type');

        switch ($mode) {
            case TemplateCategory::CATEGORY_MODE_EBAY:

                $details['path'] = $categoryHelper->isEbayCategoryType($categoryType)
                    ? Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath($value, $marketplaceId)
                    : Mage::helper('M2ePro/Component_Ebay_Category_Store')->getPath($value, $accountId);

                $details['interface_path'] = $details['path'] . ' (' . $value . ')';
                break;

            case TemplateCategory::CATEGORY_MODE_ATTRIBUTE:
                $details['path'] = Mage::helper('M2ePro')->__('Magento Attribute') .' > '.
                                   Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($value);

                $details['interface_path'] = $details['path'];
                break;
        }

        if ($categoryType == EbayCategory::TYPE_EBAY_MAIN) {
            /** @var Ess_M2ePro_Model_Ebay_Template_Category $template */
            $template = Mage::getModel('M2ePro/Ebay_Template_Category');
            $template->loadByCategoryValue($value, $mode, $marketplaceId, 0);

            $details['is_custom_template'] = $template->getIsCustomTemplate();
            $details['template_id']        = $template->getId();
        }

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($details));
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

        if ($query === null
            || (in_array($categoryType, $ebayCategoryTypes) && $marketplaceId === null)
            || (in_array($categoryType, $storeCategoryTypes) && $accountId === null)
        ) {
            $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
            return;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        if (in_array($categoryType, $ebayCategoryTypes)) {
            $tableName = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_ebay_dictionary_category');
        } else {
            $tableName = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_ebay_account_store_category');
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

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
    }

    public function getRecentAction()
    {
        $categoryType = $this->getRequest()->getParam('category_type');
        $selectedCategory = $this->getRequest()->getParam('selected_category');

        if (in_array($categoryType, Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes())) {
            $categories = Mage::helper('M2ePro/Component_Ebay_Category')->getRecent(
                $this->getRequest()->getParam('marketplace'),
                $categoryType,
                $selectedCategory
            );
        } else {
            $categories = Mage::helper('M2ePro/Component_Ebay_Category')->getRecent(
                $this->getRequest()->getParam('account'),
                $categoryType,
                $selectedCategory
            );
        }

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($categories));
    }

    //########################################

    public function getCategorySpecificHtmlAction()
    {
        $this->loadLayout();

        /** @var $specific Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Specific_Edit */
        $specific = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_template_category_chooser_specific_edit', '', array(
                'selected_specifics' => $this->getRequest()->getParam('selected_specifics'),
                'marketplace_id'     => $this->getRequest()->getParam('marketplace_id'),
                'template_id'        => $this->getRequest()->getParam('template_id'),
                'category_mode'      => $this->getRequest()->getParam('category_mode'),
                'category_value'     => $this->getRequest()->getParam('category_value')
            )
        );
        $specific->prepareFormData();

        $this->getResponse()->setBody($specific->toHtml());
    }

    //########################################

    protected function setRuleData($prefix)
    {
        $prefix .= $this->getRequest()->getParam('active_tab', '');
        $prefix .= $this->getRequest()->getParam('template_id', '');
        Mage::helper('M2ePro/Data_Global')->setValue('rule_prefix', $prefix);

        $ruleModel = Mage::getModel('M2ePro/Ebay_Magento_Product_Rule')->setData(
            array(
                'prefix' => $prefix
            )
        );

        $ruleParam = $this->getRequest()->getPost('rule');
        if (!empty($ruleParam)) {
            Mage::helper('M2ePro/Data_Session')->setValue(
                $prefix, $ruleModel->getSerializedFromPost($this->getRequest()->getPost())
            );
        } elseif ($ruleParam !== null) {
            Mage::helper('M2ePro/Data_Session')->setValue($prefix, array());
        }

        $sessionRuleData = Mage::helper('M2ePro/Data_Session')->getValue($prefix);
        if (!empty($sessionRuleData)) {
            $ruleModel->loadFromSerialized($sessionRuleData);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('rule_model', $ruleModel);
    }

    //########################################
}
