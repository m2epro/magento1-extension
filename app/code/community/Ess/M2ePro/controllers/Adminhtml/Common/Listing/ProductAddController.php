<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Listing_ProductAddController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    protected $component;
    protected $sessionKeyPostfix = '_listing_product_add';

    //########################################

    protected function _initAction()
    {
        $componentTitle = Mage::helper('M2ePro/Component_'.ucfirst($this->getComponent()))->getTitle();

        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('%component_title% Listings', $componentTitle));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Listing/ProductGridHandler.js')
            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Listing/Category/TreeHandler.js')
            ->addJs('M2ePro/Listing/AutoActionHandler.js')

            ->addJs('M2ePro/Common/Listing/Category/Summary/GridHandler.js')
            ->addJs('M2ePro/Common/Listing/Category/TreeHandler.js')
            ->addJs('M2ePro/Common/Listing/AddListingHandler.js');

        if ($this->getComponent() === Ess_M2ePro_Helper_Component_Amazon::NICK) {
            $this->getLayout()->getBlock('head')->addJs('M2ePro/Common/Amazon/Listing/AutoActionHandler.js');
        }

        $this->_initPopUp();

        $component = $this->getRequest()->getParam('component');
        if (!$component) {
            $this->setComponentPageHelpLink('Add+Magento+Products');
        } else {
            $this->setPageHelpLink($component, 'Add+Magento+Products');
        }

        return $this;
    }

    public function indexAction()
    {
        if (is_null($this->getRequest()->getParam('id'))) {
            $this->_redirect('*/adminhtml_common_listing/index', array('tab' => $this->getComponent()));
            return;
        }

        if ($this->getRequest()->getParam('clear')) {
            $this->clearSession();
            $this->getRequest()->setParam('clear',null);
            $this->_redirect('*/*/*',array('_current' => true));
            return;
        }

        $step = (int)$this->getRequest()->getParam('step');

        switch ($step) {
            case 1:
                $this->sourceMode();
                break;
            case 2:
                $source = $this->getRequest()->getParam('source');
                if (!empty($source)) {
                    $action = 'addProductsFrom' . ucfirst($source);
                    $this->$action();
                    return;
                }
                $this->_redirect('*/*/index', array('_current' => true,'step' => 1));
                break;
            case 3:
                $this->review();
                break;
            // ....
            default:
                return $this->_redirect('*/*/index', array('_current' => true,'step' => 1));
        }
    }

    //########################################

    public function sourceMode()
    {
        if ($this->getRequest()->isPost()) {

            $source = $this->getRequest()->getParam('source');

            if (!empty($source)) {
                $this->_redirect('*/*/index', array('_current' => true, 'step' => 2, 'source' => $source));
                return;
            }

            $this->_redirect('*/*/index',array('clear'=>'yes'));
            return;
        }

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_add_sourceMode'))
            ->renderLayout();
    }

    //########################################

    public function addProductsFromList()
    {
        if (is_null($this->getRequest()->getParam('id'))) {
            $this->_redirect('*/adminhtml_common_listing/index', array('tab' => $this->getComponent()));
            return;
        }

        if ($this->getRequest()->getParam('clear')) {
            $this->clearSession();
            $this->getRequest()->setParam('clear',null);
            $this->_redirect('*/*/*',array('_current' => true));
            return;
        }

        Mage::helper('M2ePro/Data_Session')->setValue('temp_products', array());
        Mage::helper('M2ePro/Data_Session')->setValue('products_source', 'list');

        $this->setRuleData($this->getComponent().'_rule_add_listing_product');

        $prefix = $this->getHideProductsInOtherListingsPrefix();

        if ($this->getRequest()->isPost()) {
            $hideProductsOtherParam = $this->getRequest()->getPost('hide_products_others_listings', 1);
            Mage::helper('M2ePro/Data_Session')->setValue($prefix, $hideProductsOtherParam);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('hide_products_others_listings_prefix', $prefix);

        if ($this->getRequest()->isXmlHttpRequest()) {
            $grid = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_product_grid', '', array(
                'component' => $this->getComponent()
            ));
            return $this->getResponse()->setBody($grid->toHtml());
        }

        $this->_initAction();

        $component = $this->getComponent();
        if (!$component) {
            $this->setComponentPageHelpLink('Adding+Products+from+the+List');
        } else {
            $this->setPageHelpLink($component, 'Adding+Products+from+the+List');
        }

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_add_sourceProduct', '',
                array(
                    'component' => $component
                )
            ))
            ->renderLayout();
    }

    public function addProductsFromCategories()
    {
        if (is_null($this->getRequest()->getParam('id'))) {
            $this->_redirect('*/adminhtml_common_listing/index', array('tab' => $this->getComponent()));
            return;
        }

        if ($this->getRequest()->getParam('clear')) {
            $this->clearSession();
            $this->getRequest()->setParam('clear',null);
            $this->_redirect('*/*/*',array('_current' => true));
            return;
        }

        Mage::helper('M2ePro/Data_Session')->setValue('temp_products', array());
        Mage::helper('M2ePro/Data_Session')->setValue('products_source', 'categories');

        $this->setRuleData($this->getComponent().'_rule_add_listing_product');

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
            /* @var $grid Ess_M2ePro_Block_Adminhtml_Common_Listing_Product_Category_Grid */
            $grid = $this->getLayout()
                ->createBlock('M2ePro/adminhtml_common_listing_product_category_grid', '', array(
                    'component' => $this->getComponent()
                ));

            $grid->setSelectedIds($selectedProductsIds);
            $grid->setCurrentCategoryId($this->getSessionValue('current_category_id'));

            return $this->getResponse()->setBody($grid->toHtml());
        }

        $this->_initAction();

        $component = $this->getComponent();
        if (!$component) {
            $this->setComponentPageHelpLink('Adding+Products+from+Category');
        } else {
            $this->setPageHelpLink($component, 'Adding+Products+from+Category');
        }

        $gridContainer = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_add_sourceCategory','',array(
            'component' => $this->getComponent()
        ));
        $this->_addContent($gridContainer);

        /* @var $treeBlock Ess_M2ePro_Block_Adminhtml_Common_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_category_tree', '', array(
            'component' => $component,
            'tree_settings' => array(
                'show_products_amount' => true,
                'hide_products_this_listing' => true
            )
        ));

        if (is_null($this->getSessionValue('current_category_id'))) {
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

    public function review()
    {
        if ($this->getRequest()->getParam('skip_products_steps', false)) {
            $this->_redirect('*/adminhtml_common_'.$this->getComponent().'_listing/view', array(
                'id' => $this->getRequest()->getParam('id')
            ));
            return;
        }

        if ($this->getRequest()->getParam('component') == Ess_M2ePro_Helper_Component_Amazon::NICK) {
            $this->_redirect('*/adminhtml_common_amazon_listing_productAdd/index', array(
                '_current' => true,
                'step' => 1
            ));
            return;
        }

        Mage::helper('M2ePro/Data_Session')->setValue('products_source', '');

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_add_review', '', array(
                'component' => $this->getComponent()
            )))
            ->renderLayout();
    }

    //########################################

    public function viewListingAction()
    {
        $listingId = $this->getRequest()->getParam('id');

        if (empty($listingId)) {
            $this->_redirect('*/adminhtml_common_listing/index', array(
                'tab' => $this->getComponent()
            ));
            return;
        }

        Mage::helper('M2ePro/Data_Session')->setValue('temp_products', array());

        return $this->_redirect('*/adminhtml_common_'.$this->getComponent().'_listing/view', array(
            'id' => $listingId
        ));
    }

    public function viewListingAndListAction()
    {
        $listingId = $this->getRequest()->getParam('id');

        if (empty($listingId)) {
            $this->_redirect('*/adminhtml_common_listing/index', array(
                'tab' => $this->getComponent()
            ));
            return;
        }

        Mage::helper('M2ePro/Data_Session')->setValue(
            'added_products_ids',
            Mage::helper('M2ePro/Data_Session')->getValue('temp_products')
        );

        Mage::helper('M2ePro/Data_Session')->setValue('temp_products', array());

        return $this->_redirect('*/adminhtml_common_'.$this->getComponent().'_listing/view', array(
            'id' => $listingId,
            'do_list' => true
        ));
    }

    //########################################

    public function addProductsAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing',$listingId);

        $productsIds = $this->getRequest()->getParam('products');
        $productsIds = explode(',', $productsIds);
        $productsIds = array_unique($productsIds);

        $listingProductIds = array();
        if (count($productsIds) > 0) {
            foreach ($productsIds as $productId) {
                if ($productId == '' || $productsIds[0] == 'true') {
                    continue;
                }

                $tempResult = $listing->addProduct($productId);
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

            $backUrl = $this->getUrl('*/*/index', array(
                'id' => $listingId,
                'skip_products_steps' => empty($tempProducts),
                'step' => 3,
                'component' => $this->getComponent()
            ));

            $this->clearSession();

            return $this->getResponse()->setBody(json_encode(array('redirect' => $backUrl)));
        }

        $response = array('redirect' => '');
        return $this->getResponse()->setBody(json_encode($response));
    }

    public function getSessionProductsIdsAction()
    {
        $tempSession = $this->getSessionValue('source_categories');
        $selectedProductsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        $this->getResponse()->setBody(json_encode(array(
            'ids' => $selectedProductsIds
        )));
    }

    //########################################

    public function getCategoriesJsonAction()
    {
        $tempSession = $this->getSessionValue('source_categories');
        $selectedProductsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        /* @var $treeBlock Ess_M2ePro_Block_Adminhtml_Common_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_category_tree', '', array(
            'component' => $this->getComponent(),
            'tree_settings' => array(
                'show_products_amount' => true,
                'hide_products_this_listing' => false
            )
        ));
        $treeBlock->setSelectedIds($selectedProductsIds);

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

        $checked = explode(',',$checked);
        $initial = explode(',',$initial);

        $initial = array_values(array_unique(array_merge($initial,$checked)));
        $all     = array_values(array_unique(array_merge($all,$initial)));

        $all = array_flip($all);

        foreach (array_diff($initial,$checked) as $id) {
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

        /* @var $treeBlock Ess_M2ePro_Block_Adminhtml_Common_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_category_tree', '', array(
            'component' => $this->getComponent(),
            'tree_settings' => array(
                'show_products_amount' => true,
                'hide_products_this_listing' => false
            )
        ));
        $treeBlock->setSelectedIds($tempSession['products_ids']);

        $this->getResponse()->setBody($treeBlock->getInfoJson());
    }

    //########################################

    public function getCategoriesSummaryHtmlAction()
    {
        $this->loadLayout();

        $tempSession = $this->getSessionValue('source_categories');
        $productsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        /* @var $treeBlock Ess_M2ePro_Block_Adminhtml_Common_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_category_tree', '', array(
            'component' => $this->getComponent()
        ));
        $treeBlock->setSelectedIds($productsIds);

        /* @var $block Ess_M2ePro_Block_Adminhtml_Common_Listing_Product_Category_Summary_Grid */
        $block = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_common_listing_product_category_summary_grid', '', array(
                'component' => $this->getComponent()
        ));
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
        /* @var $treeBlock Ess_M2ePro_Block_Adminhtml_Common_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_category_tree', '', array(
            'component' => $this->getComponent()
        ));
        $treeBlock->setSelectedIds($tempSession['products_ids']);

        $productsForEachCategory = $treeBlock->getProductsForEachCategory();

        $products = array();
        foreach ($categoriesIds as $categoryId) {
            $products = array_merge($products, $productsForEachCategory[$categoryId]);
        }

        $tempSession['products_ids'] = array_diff($tempSession['products_ids'], $products);

        $this->setSessionValue('source_categories',$tempSession);
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
        } elseif (!is_null($ruleParam)) {
            Mage::helper('M2ePro/Data_Session')->setValue($prefix, array());
        }

        $sessionRuleData = Mage::helper('M2ePro/Data_Session')->getValue($prefix);
        if (!empty($sessionRuleData)) {
            $ruleModel->loadFromSerialized($sessionRuleData);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('rule_model', $ruleModel);
    }

    protected function getHideProductsInOtherListingsPrefix()
    {
        $id = $this->getRequest()->getParam('id');

        $prefix = $this->getComponent().'_hide_products_others_listings_';
        $prefix .= is_null($id) ? 'add' : $id;
        $prefix .= '_listing_product';

        return $prefix;
    }

    //########################################

    protected function getComponent()
    {
        return $this->getRequest()->getParam('component');
    }

    protected function getSessionKey()
    {
        return $this->getComponent().$this->sessionKeyPostfix;
    }

    //########################################

    protected function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();
        $sessionData[$key] = $value;

        Mage::helper('M2ePro/Data_Session')->setValue($this->getSessionKey(), $sessionData);

        return $this;
    }

    protected function getSessionValue($key = NULL)
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->getSessionKey());

        if (is_null($sessionData)) {
            $sessionData = array();
        }

        if (is_null($key)) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : NULL;
    }

    // ---------------------------------------

    private function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->setValue($this->getSessionKey(), NULL);
    }

    //########################################

    /** @return Ess_M2ePro_Model_Amazon_Listing
     * @throws Exception
     */
    private function getListingFromRequest()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        return Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing',$listingId)->getChildObject();
    }

    //########################################
}