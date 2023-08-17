<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Add_CategoryTemplate as CategoryTemplateBlock;
use Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Add_SourceMode as SourceModeBlock;

class Ess_M2ePro_Adminhtml_Walmart_Listing_ProductAddController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    protected $_sessionKey = 'walmart_listing_product_add';

    protected $_listing;

    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('Listings'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Action.js')
            ->addJs('M2ePro/Grid.js')

            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')

            ->addJs('M2ePro/Listing/Action.js')
            ->addJs('M2ePro/Listing/Grid.js')
            ->addJs('M2ePro/Listing/ProductGrid.js')
            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/Listing/Category/Tree.js')
            ->addJs('M2ePro/Listing/AutoAction.js')

            ->addJs('M2ePro/Walmart/Listing/Category/Summary/Grid.js')
            ->addJs('M2ePro/Walmart/Listing/Category/Tree.js')
            ->addJs('M2ePro/Walmart/Listing/Product/Add.js')

            ->addJs('M2ePro/Walmart/Listing/Action.js')
            ->addJs('M2ePro/Walmart/Listing/Template/Category.js')
            ->addJs('M2ePro/Walmart/Listing/CategoryTemplateGrid.js')
            ->addJs('M2ePro/Walmart/Listing/VariationProductManage.js')
            ->addJs('M2ePro/Walmart/Listing/AutoAction.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "walmart-integration");

        return $this;
    }

    //########################################

    public function indexAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $listingProductsIds = Mage::helper('M2ePro/Data_Session')->getValue('temp_products');

        if (empty($listingId)) {
            $this->_redirect('*/adminhtml_walmart_listing/index');
            return;
        }

        if ($this->getRequest()->getParam('clear')) {
            $this->clear();
            $this->getRequest()->setParam('clear', null);
            $this->_redirect('*/*/index', array('_current' => true));
            return;
        }

        if ($source = $this->getRequest()->getParam('source')) {
            $this->getListing()->setSetting('additional_data', 'source', $source)->save();
        }

        if (!empty($listingProductsIds)) {
            $this->getListing()->setSetting('additional_data', 'adding_listing_products_ids', $listingProductsIds);
            $this->getListing()->save();

            Mage::helper('M2ePro/Data_Session')->setValue('temp_products', array());
        }

        $step = (int)$this->getRequest()->getParam('step');

        switch ($step) {
            case 1:
                $this->sourceMode();
                break;
            case 2:
                $source = $this->getListing()->getSetting('additional_data', 'source');
                if (!empty($source) && $source !== SourceModeBlock::SOURCE_OTHER) {
                    $action = 'addProductsFrom' . ucfirst($source);
                    $this->$action();
                    return;
                }

                $this->_redirect('*/*/index', array('_current' => true,'step' => 1));
                break;
            case 3:
                $this->addCategoryTemplateView();
                break;
            case 4:
                $this->stepFour();
                break;
            // ....
            default:
                return $this->_redirect('*/*/index', array('_current' => true,'step' => 1));
        }
    }

    //########################################

    public function sourceMode()
    {
        if (!$this->getRequest()->getParam('new_listing')) {
            return $this->_redirect(
                '*/adminhtml_walmart_listing/view',
                array('id' => $this->getRequest()->getParam('id'))
            );
        }

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_product_add_sourceMode'))
            ->renderLayout();
    }

    //########################################

    public function addProductsFromList()
    {
        if ($this->getRequest()->getParam('id') === null) {
            $this->_redirect('*/adminhtml_walmart_listing/index');
            return;
        }

        $this->setRuleData('walmart_rule_add_listing_product');

        $prefix = $this->getHideProductsInOtherListingsPrefix();

        if ($this->getRequest()->isPost()) {
            $hideProductsOtherParam = $this->getRequest()->getPost('hide_products_others_listings', 1);
            Mage::helper('M2ePro/Data_Session')->setValue($prefix, $hideProductsOtherParam);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('hide_products_others_listings_prefix', $prefix);

        if ($this->getRequest()->isXmlHttpRequest()) {
            $grid = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_product_grid');
            return $this->getResponse()->setBody($grid->toHtml());
        }

        $this->_initAction();

        $this->setPageHelpLink(null, null, "walmart-integration");

        $this->_addContent(
            $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_product_add_sourceMode_product')
        )->renderLayout();
    }

    public function addProductsFromCategories()
    {
        if ($this->getRequest()->getParam('id') === null) {
            $this->_redirect('*/adminhtml_walmart_listing/index');
            return;
        }

        $this->setRuleData('walmart_rule_add_listing_product');

        $prefix = $this->getHideProductsInOtherListingsPrefix();

        if ($this->getRequest()->isPost()) {
            $hideProductsOtherParam = $this->getRequest()->getPost('hide_products_others_listings', 1);
            Mage::helper('M2ePro/Data_Session')->setValue($prefix, $hideProductsOtherParam);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('hide_products_others_listings_prefix', $prefix);

        $tempSession = $this->getSessionValue('source_categories');
        $selectedProductsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->getParam('current_category_id')) {
                $this->setSessionValue('current_category_id', $this->getRequest()->getParam('current_category_id'));
            }

            $this->loadLayout();
            /** @var $grid Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Category_Grid */
            $grid = $this->getLayout()
                ->createBlock('M2ePro/adminhtml_walmart_listing_product_category_grid');

            $grid->setSelectedIds($selectedProductsIds);
            $grid->setCurrentCategoryId($this->getSessionValue('current_category_id'));

            return $this->getResponse()->setBody($grid->toHtml());
        }

        $this->_initAction();

        $this->setPageHelpLink(null, null, "walmart-integration");

        $gridContainer = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_product_add_sourceMode_category'
        );
        $this->_addContent($gridContainer);

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_category_tree', '', array(
            'tree_settings' => array(
                'show_products_amount' => true,
                'hide_products_this_listing' => true
            )
            )
        );

        if ($this->getSessionValue('current_category_id') === null) {
            $currentNode = $treeBlock->getRoot()->getChildren()->getIterator()->current();
            if (!$currentNode) {
                throw new Ess_M2ePro_Model_Exception('No Categories found');
            }

            $this->setSessionValue('current_category_id', $currentNode->getId());
        }

        $treeBlock->setGridId($gridContainer->getChild('grid')->getId());
        $treeBlock->setSelectedIds($selectedProductsIds);
        $treeBlock->setCurrentNodeById($this->getSessionValue('current_category_id'));

        $gridContainer->getChild('grid')->setTreeBlock($treeBlock);
        $gridContainer->getChild('grid')->setSelectedIds($selectedProductsIds);
        $gridContainer->getChild('grid')->setCurrentCategoryId($this->getSessionValue('current_category_id'));

        $this->renderLayout();
    }

    //########################################

    protected function addCategoryTemplateView()
    {
        $listingId = $this->getRequest()->getParam('id');

        if (empty($listingId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_product_add_categoryTemplate');

        $this->_initAction();

        $this->setPageHelpLink(null, null, "walmart-integration");

        $this->_addContent($block)->renderLayout();
    }

    protected function stepFour()
    {
        $listingId = $this->getRequest()->getParam('id');
        $additionalData = $this->getListing()->getSettings('additional_data');

        if (empty($additionalData['adding_listing_products_ids'])) {
            return $this->_redirect('*/adminhtml_walmart_listing/view', array('id' => $listingId));
        }

        /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'id' => 'main_table.id'
            )
        );
        $collection->getSelect()->where(
            "`main_table`.`id` IN (?) AND `second_table`.`template_category_id` IS NULL",
            $additionalData['adding_listing_products_ids']
        );

        $failedProductsIds = $collection->getColumnValues('id');
        $this->deleteListingProducts($failedProductsIds);

        //-- Remove successfully moved Unmanaged items
        if (isset($additionalData['source']) && $additionalData['source'] == SourceModeBlock::SOURCE_OTHER) {
            $this->deleteListingOthers();
        }

        //--

        $this->review();
    }

    protected function review()
    {
        $this->_initAction();

        $additionalData = $this->getListing()->getSettings('additional_data');

        $this->addVariationAttributes($additionalData['adding_listing_products_ids']);

        Mage::helper('M2ePro/Data_Session')->setValue(
            'added_products_ids',
            $additionalData['adding_listing_products_ids']
        );

        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Add_Review $blockReview */
        $blockReview = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_product_add_review');

        if (isset($additionalData['source'])) {
            $blockReview->setSource($additionalData['source']);
        }

        $this->clear();

        $this->_addContent($blockReview)->renderLayout();
    }

    //########################################

    public function viewListingAction()
    {
        $listingId = $this->getRequest()->getParam('id');

        if (empty($listingId)) {
            $this->_redirect('*/adminhtml_walmart_listing/index');
            return;
        }

        return $this->_redirect(
            '*/adminhtml_walmart_listing/view', array(
            'id' => $listingId
            )
        );
    }

    public function viewListingAndListAction()
    {
        $listingId = $this->getRequest()->getParam('id');

        if (empty($listingId)) {
            $this->_redirect('*/adminhtml_walmart_listing/index');
            return;
        }

        return $this->_redirect(
            '*/adminhtml_walmart_listing/view', array(
            'id' => $listingId,
            'do_list' => true
            )
        );
    }

    //########################################

    public function addProductsAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing', $listingId);

        $productsIds = $this->getRequest()->getParam('products');
        $productsIds = explode(',', $productsIds);
        $productsIds = array_unique($productsIds);

        $listingProductIds = array();
        if (!empty($productsIds)) {
            foreach ($productsIds as $productId) {
                if ($productId == '' || $productsIds[0] == 'true') {
                    continue;
                }

                $tempResult = $listing->addProduct($productId, Ess_M2ePro_Helper_Data::INITIATOR_USER);
                if ($tempResult instanceof Ess_M2ePro_Model_Listing_Product) {
                    $listingProductIds[] = $tempResult->getId();
                }
            }
        }

        $tempProducts = Mage::helper('M2ePro/Data_Session')->getValue('temp_products');
        $tempProducts = array_merge((array)$tempProducts, $listingProductIds);
        Mage::helper('M2ePro/Data_Session')->setValue('temp_products', $tempProducts);

        $isLastPart = $this->getRequest()->getParam('is_last_part');
        if ($isLastPart == 'yes') {
            $listing->setSetting('additional_data', 'adding_listing_products_ids', $tempProducts);
            $listing->save();

            $backUrl = $this->getUrl(
                '*/*/index', array(
                'id' => $listingId,
                'skip_products_steps' => empty($tempProducts),
                'step' => 3
                )
            );

            $this->clearSession();

            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('redirect' => $backUrl)));
        }

        $response = array('redirect' => '');
        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($response));
    }

    public function getSessionProductsIdsAction()
    {
        $tempSession = $this->getSessionValue('source_categories');
        $selectedProductsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'ids' => $selectedProductsIds
                )
            )
        );
    }

    //########################################

    public function removeAddedProductsAction()
    {
        $this->deleteListingProducts($this->getListing()->getSetting('additional_data', 'adding_listing_products_ids'));

        if ($this->getListing()->getSetting('additional_data', 'source') == SourceModeBlock::SOURCE_OTHER) {
            $additionalData = $this->getListing()->getSettings('additional_data');
            unset($additionalData['source']);
            $this->getListing()->setSettings('additional_data', $additionalData)->save();

            return $this->_redirect(
                '*/adminhtml_walmart_listing_other/view', array(
                'account'     => $this->getListing()->getAccountId(),
                'marketplace' => $this->getListing()->getMarketplaceId(),
                )
            );
        }

        $this->_redirect(
            '*/adminhtml_walmart_listing_productAdd/index', array(
            'step'   => 2,
            'id'     => $this->getRequest()->getParam('id')
            )
        );
    }

    // ---------------------------------------

    protected function deleteListingProducts($ids)
    {
        $ids = array_map('intval', $ids);

        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => $ids));

        foreach ($collection->getItems() as $listingProduct) {
            /**@var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct->canBeForceDeleted(true);
            $listingProduct->deleteInstance();
        }

        $listingProductAddIds = $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');
        if (empty($listingProductAddIds)) {
            return;
        }

        $listingProductAddIds = array_map('intval', $listingProductAddIds);
        $listingProductAddIds = array_diff($listingProductAddIds, $ids);

        $this->getListing()->setSetting('additional_data', 'adding_listing_products_ids', $listingProductAddIds);
        $this->getListing()->save();
    }

    protected function deleteListingOthers()
    {
        $listingProductsIds = $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');
        if (empty($listingProductsIds)) {
            return;
        }

        $otherProductsIds = array();

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $listingProductsIds));
        foreach ($collection->getItems() as $listingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $otherProductsIds[] = (int)$listingProduct->getSetting(
                'additional_data', $listingProduct::MOVING_LISTING_OTHER_SOURCE_KEY
            );
        }

        if (empty($otherProductsIds)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Other_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Other');
        $collection->addFieldToFilter('id', array('in' => $otherProductsIds));
        foreach ($collection->getItems() as $listingOther) {
            /** @var Ess_M2ePro_Model_Listing_Other $listingOther */
            $listingOther->moveToListingSucceed();
        }
    }

    //########################################

    public function getCategoriesJsonAction()
    {
        $tempSession = $this->getSessionValue('source_categories');
        $selectedProductsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_category_tree', '', array(
            'tree_settings' => array(
                'show_products_amount' => true,
                'hide_products_this_listing' => false
            )
            )
        );
        $treeBlock->setSelectedIds($selectedProductsIds);

        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody(
            $treeBlock->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    public function saveProductsToSessionAndGetInfoAction()
    {
        $tempSession = $this->getSessionValue('source_categories');
        $all = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        $checked = $this->getRequest()->getParam('checked_ids');
        $initial = $this->getRequest()->getParam('initial_checked_ids');

        $checked = array_filter(explode(',', $checked));
        $initial = array_filter(explode(',', $initial));

        $initial = array_values(array_unique(array_merge($initial, $checked)));
        $all     = array_values(array_unique(array_merge($all, $initial)));

        $all = array_flip($all);

        foreach (array_diff($initial, $checked) as $id) {
            unset($all[$id]);
        }

        $tempSession['products_ids'] = array_values(array_filter(array_flip($all)));
        $this->setSessionValue('source_categories', $tempSession);

        // ---------------------------------------

        $this->_forward('getTreeInfo');
    }

    public function getTreeInfoAction()
    {
        $tempSession = $this->getSessionValue('source_categories');
        $tempSession['products_ids'] = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_category_tree', '', array(
            'tree_settings' => array(
                'show_products_amount' => true,
                'hide_products_this_listing' => false
            )
            )
        );
        $treeBlock->setSelectedIds($tempSession['products_ids']);

        $this->getResponse()->setBody($treeBlock->getInfoJson());
    }

    //########################################

    public function getCategoriesSummaryHtmlAction()
    {
        $this->loadLayout();

        $tempSession = $this->getSessionValue('source_categories');
        $productsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_category_tree');
        $treeBlock->setSelectedIds($productsIds);

        /** @var $block Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Category_Summary_Grid */
        $block = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_listing_product_category_summary_grid');
        $block->setStoreId($this->getListingFromRequest()->getStoreId());
        $block->setProductsIds($productsIds);
        $block->setProductsForEachCategory($treeBlock->getProductsCountForEachCategory());

        $this->getResponse()->setBody($block->toHtml());
    }

    public function removeSessionProductsByCategoryAction()
    {
        $categoriesIds = $this->getRequestIds();

        $tempSession = $this->getSessionValue('source_categories');
        if (!isset($tempSession['products_ids'])) {
            return;
        }

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_category_tree');
        $treeBlock->setSelectedIds($tempSession['products_ids']);

        $productsForEachCategory = $treeBlock->getProductsForEachCategory();

        $products = array();
        foreach ($categoriesIds as $categoryId) {
            $products = array_merge($products, $productsForEachCategory[$categoryId]);
        }

        $tempSession['products_ids'] = array_diff($tempSession['products_ids'], $products);

        $this->setSessionValue('source_categories', $tempSession);
    }

    //########################################

    protected function setRuleData($prefix)
    {
        $listingData = $this->getListingFromRequest()->getData();

        $storeId = isset($listingData['store_id']) ? (int)$listingData['store_id'] : 0;
        $prefix .= isset($listingData['id']) ? '_'.$listingData['id'] : '';
        Mage::helper('M2ePro/Data_Global')->setValue('rule_prefix', $prefix);

        $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
            array(
                'prefix' => $prefix,
                'store_id' => $storeId,
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


    private function addVariationAttributes($productsIds)
    {
        $listingProductCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('listing_product_id', array('in' => $productsIds));
        $listingProductCollection->addFieldToFilter('is_variation_product', 1);

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        foreach ($listingProductCollection as $listingProduct) {
            $listingProduct->getChildObject()->addVariationAttributes();
        }
    }

    protected function getHideProductsInOtherListingsPrefix()
    {
        $id = $this->getRequest()->getParam('id');

        $prefix = 'walmart_hide_products_others_listings_';
        $prefix .= $id === null ? 'add' : $id;
        $prefix .= '_listing_product';

        return $prefix;
    }

    //########################################

    public function viewTemplateCategoryPopupAction()
    {
        $mainBlock = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_listing_template_category_main');

        return $this->getResponse()->setBody($mainBlock->toHtml());
    }

    public function viewTemplateCategoriesGridAction()
    {
        $listingProductsIds    = $this->getRequest()->getParam('products_ids');
        $magentoCategoryIds    = $this->getRequest()->getParam('magento_categories_ids');
        $createNewTemplateJsFn = $this->getRequest()->getParam('create_new_template_js_function');

        if (empty($listingProductsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        !is_array($listingProductsIds) && $listingProductsIds = array_filter(explode(',', $listingProductsIds));
        !is_array($magentoCategoryIds) && $magentoCategoryIds = array_filter(explode(',', $magentoCategoryIds));

        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Template_Category_Grid $grid */
        $grid = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_template_category_grid'
        );
        $grid->setProductsIds($listingProductsIds);
        $grid->setMagentoCategoryIds($magentoCategoryIds);
        $grid->setMapToTemplateJsFn('selectTemplateCategory');
        $createNewTemplateJsFn !== null && $grid->setCreateNewTemplateJsFn($createNewTemplateJsFn);

        return $this->getResponse()->setBody($grid->toHtml());
    }

    //########################################

    public function categoryTemplateAssignTypeAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $listingProductsIds = $this->getRequest()->getParam('products_ids');

        $mode = $this->getRequest()->getParam('mode');
        $categoryTemplateId = $this->getRequest()->getParam('category_template_id');

        if (empty($listingId) || empty($mode)) {
            $this->_forward('index');
            return;
        }

        if (!is_array($listingProductsIds)) {
            $listingProductsIds = explode(',', $listingProductsIds);
        }

        $listing = Mage::helper('M2ePro/Component_Walmart')->getObject('Listing', $listingId);
        $listingAdditionalData = $listing->getData('additional_data');
        $listingAdditionalData = Mage::helper('M2ePro')->jsonDecode($listingAdditionalData);

        $listingAdditionalData['category_template_mode'] = $mode;

        $listing->setData('additional_data', Mage::helper('M2ePro')->jsonEncode($listingAdditionalData))->save();

        if ($mode == CategoryTemplateBlock::MODE_SAME && !empty($categoryTemplateId)) {
            /** @var Ess_M2ePro_Model_Walmart_Template_Category $categoryTemplate */
            $categoryTemplate = Mage::getModel('M2ePro/Walmart_Template_Category')->load($categoryTemplateId);

            if (!$categoryTemplate->isEmpty()) {
                if (!empty($listingProductsIds)) {
                    $this->setCategoryTemplate($listingProductsIds, $categoryTemplateId);
                }

                return $this->_redirect(
                    '*/adminhtml_walmart_listing_productAdd/index', array(
                    '_current' => true,
                    'step' => 4
                    )
                );
            }

            unset($listingAdditionalData['category_template_mode']);

            $listing->setData('additional_data', Mage::helper('M2ePro')->jsonEncode($listingAdditionalData))->save();
        } elseif ($mode == CategoryTemplateBlock::MODE_CATEGORY) {
            return $this->_redirect(
                '*/*/categoryTemplateAssignByMagentoCategory', array(
                '_current' => true,
                )
            );
        } else if ($mode == CategoryTemplateBlock::MODE_MANUALLY) {
            return $this->_redirect(
                '*/*/categoryTemplateAssignManually', array(
                '_current' => true,
                )
            );
        }

        $this->_forward('index');
    }

    public function categoryTemplateAssignByMagentoCategoryAction()
    {
        $listingProductsIds = $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');

        if (empty($listingProductsIds)) {
            $this->_forward('index');
            return;
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $grid = $this->getLayout()
                ->createBlock('M2ePro/adminhtml_walmart_listing_product_add_categoryTemplate_category_grid');
            return $this->getResponse()->setBody($grid->toHtml());
        }

        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_product_add_categoryTemplate_category'
        );

        $this->_initAction();

        $this->setPageHelpLink(null, null, "walmart-integration");

        $this->_addContent($block)->renderLayout();
    }

    public function categoryTemplateAssignManuallyAction()
    {
        $listingProductsIds = $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');

        if (empty($listingProductsIds)) {
            $this->_forward('index');
            return;
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $grid = $this->getLayout()
                ->createBlock('M2ePro/adminhtml_walmart_listing_product_add_categoryTemplate_manual_grid');
            return $this->getResponse()->setBody($grid->toHtml());
        }

        $this->_initAction();

        $this->setPageHelpLink(null, null, "walmart-integration");

        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_product_add_categoryTemplate_manual'
        );
        $this->_addContent($block)->renderLayout();
    }

    //########################################

    public function assignByMagentoCategorySaveCategoryAction()
    {
        $templateId = $this->getRequest()->getParam('template_id');
        $magentoCategoryIds = $this->getRequest()->getParam('magento_categories_ids');

        if (empty($templateId) || empty($magentoCategoryIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        !is_array($magentoCategoryIds) && $magentoCategoryIds = array_filter(explode(',', $magentoCategoryIds));
        $templatesData = $this->getListing()->getSetting('additional_data', 'adding_category_templates_data', array());

        foreach ($magentoCategoryIds as $magentoCategoryId) {
            $templatesData[$magentoCategoryId] = $templateId;
        }

        $this->getListing()->setSetting('additional_data', 'adding_category_templates_data', $templatesData);
        $this->getListing()->save();

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => true)));
    }

    //########################################

    public function checkCategoryTemplateProductsAction()
    {
        $listingProductsIds = $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');

        /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
            'id' => 'main_table.id'
            )
        );
        $collection->getSelect()->where(
            "`main_table`.`id` IN (?) AND `second_table`.`template_category_id` IS NULL", $listingProductsIds
        );

        $failedProductsIds = $collection->getColumnValues('id');

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'validation'      => empty($failedProductsIds),
                'total_count'     => count($listingProductsIds),
                'failed_count'    => count($failedProductsIds),
                'failed_products' => $failedProductsIds
                )
            )
        );
    }

    //########################################

    public function resetCategoryTemplateAction()
    {
        $listingProductsIds = $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');

        $this->setCategoryTemplate($listingProductsIds, null);

        $this->getListing()->setSetting('additional_data', 'adding_category_templates_data', array());
        $this->getListing()->save();

        return $this->_redirect(
            '*/adminhtml_walmart_listing_productAdd/index', array(
            '_current' => true,
            'step' => 3
            )
        );
    }

    protected function exitToListingAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        if ($listingId === null) {
            return $this->_redirect('*/adminhtml_walmart_listing/index');
        }

        $additionalData = $this->getListing()->getSettings('additional_data');
        $this->clearProductsAdding($additionalData);
        $this->clear();

        return $this->_redirect(
            '*/adminhtml_walmart_listing/view',
            array('id' => $listingId)
        );
    }

    protected function setCategoryTemplate($productsIds, $templateId)
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableWalmartListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_walmart_listing_product');

        $productsIds = array_chunk($productsIds, 1000);
        foreach ($productsIds as $productsIdsChunk) {
            $connWrite->update(
                $tableWalmartListingProduct, array(
                    'template_category_id' => $templateId
                ), '`listing_product_id` IN ('.implode(',', $productsIdsChunk).')'
            );
        }
    }

    //########################################

    protected function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();
        $sessionData[$key] = $value;

        Mage::helper('M2ePro/Data_Session')->setValue($this->_sessionKey, $sessionData);

        return $this;
    }

    protected function getSessionValue($key = null)
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey);

        if ($sessionData === null) {
            $sessionData = array();
        }

        if ($key === null) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : null;
    }

    // ---------------------------------------

    protected function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->setValue($this->_sessionKey, null);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component_Walmart')->getObject('Listing', $listingId);
        }

        return $this->_listing;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing
     * @throws Exception
     */
    protected function getListingFromRequest()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        return $this->getListing()->getChildObject();
    }

    //########################################

    public function clear()
    {
        $this->clearSession();

        if ($additionalData = $this->getListing()->getSettings('additional_data')) {
            $additionalData['adding_listing_products_ids'] = array();
            unset($additionalData['source']);
            unset($additionalData['adding_category_templates_data']);
            $this->getListing()->setSettings('additional_data', $additionalData)->save();
        }
    }

    /**
     * @param array $additionalData
     * @return void
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function clearProductsAdding($additionalData)
    {
        Mage::helper('M2ePro/Data_Session')->setValue('added_products_ids', array());

        if (!empty($additionalData['adding_listing_products_ids'])
            && is_array($additionalData['adding_listing_products_ids'])
        ) {
            $this->deleteListingProducts($additionalData['adding_listing_products_ids']);
        }
    }
}
