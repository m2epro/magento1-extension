<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Ebay_Listing_SourceMode as SourceModeBlock;

class Ess_M2ePro_Adminhtml_Ebay_Listing_ProductAddController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    protected $_sessionKey = 'ebay_listing_product_add';
    protected $_categorySettingsSessionKey = 'ebay_listing_category_settings';

    //########################################

    protected function _initAction()
    {
        $this->loadLayout();

        $this->getLayout()->getBlock('head')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')

            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Ebay/Listing/ProductAdd.js')
            ->addJs('M2ePro/Listing/ProductGrid.js')

            ->addJs('M2ePro/Attribute.js')
            ->addJs('M2ePro/Action.js')
            ->addJs('M2ePro/Listing/Action.js')
            ->addJs('M2ePro/Ebay/Listing/Action.js')
            ->addJs('M2ePro/Listing/Moving.js')
            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/Listing/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/ViewGrid.js')
            ->addJs('M2ePro/Ebay/Listing/Bids.js')
            ->addJs('M2ePro/Ebay/Listing/VariationProductManage.js')
            ->addJs('M2ePro/Ebay/Listing/Settings/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/ProductAdd/Settings/Grid.js')

            ->addJs('M2ePro/TemplateManager.js')
            ->addJs('M2ePro/Ebay/Listing/Template/Switcher.js')
            ->addJs('M2ePro/Ebay/Template/Return.js')
            ->addJs('M2ePro/Ebay/Template/Shipping.js')
            ->addJs('M2ePro/Ebay/Template/Shipping/ExcludedLocations.js')
            ->addJs('M2ePro/Ebay/Template/SellingFormat.js')
            ->addJs('M2ePro/Ebay/Template/Description.js')
            ->addJs('M2ePro/Ebay/Template/Synchronization.js')

            ->addJs('M2ePro/Listing/Category/Tree.js')
            ->addJs('M2ePro/Listing/AutoAction.js')
            ->addJs('M2ePro/Ebay/Listing/AutoAction.js')
            ->addJs('M2ePro/Ebay/Listing/Category.js')
            ->addJs('M2ePro/Ebay/Template/Category/Specifics.js')
            ->addJs('M2ePro/Ebay/Template/Category/Chooser.js')
            ->addJs('M2ePro/Ebay/Template/Category/Chooser/Browse.js');

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        if (Mage::helper('M2ePro/Magento')->isTinyMceAvailable()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "add-magento-products-manually");

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
        if (!$listingId = $this->getRequest()->getParam('listing_id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->getRequest()->getParam('clear')) {
            $this->clear();
            $this->getRequest()->setParam('clear', null);

            return $this->_redirect(
                '*/*/index',
                array('_current' => true, 'step' => 1, 'source' => $this->getRequest()->getParam('source'))
            );
        }

        if ($source = $this->getRequest()->getParam('source')) {
            $listing = $this->getListingFromRequest();
            $listing->setSetting('additional_data', 'source', $source);
            $listing->save();
        }

        $this->setListingData();

        switch ((int)$this->getRequest()->getParam('step')) {
            case 1:
                return $this->stepOne();
        }

        return $this->_redirect('*/*/index', array('_current' => true,'step' => 1));
    }

    //########################################

    protected function stepOne()
    {
        $listing = $this->getListingFromRequest();

        $method = 'stepOneSource'.ucfirst($listing->getSetting('additional_data', 'source'));
        if (!method_exists($this, $method)) {
            return $this->_redirect('*/*/sourceMode', array('_current' => true));
        }

        $this->$method();
    }

    protected function stepOneSourceProducts()
    {
        $ids = $this->getListingFromRequest()->getChildObject()->getAddedListingProductsIds();

        if (!empty($ids)) {
            if ($this->getRequest()->isXmlHttpRequest()) {
                return $this->getResponse()->setBody(
                    (Mage::helper('M2ePro')->jsonEncode(
                        array(
                        'ajaxExpired' => 1,
                        'ajaxRedirect' => $this->getUrl('*/*/index', array('_current' => true,'step' => 1))
                        )
                    ))
                );
            } else {
                $urlParams = array(
                    '_current'   => true,
                    'step'       => 1,
                    'listing_id' => $this->getRequest()->getParam('listing_id')
                );
                $this->getRequest()->getParam('wizard') && $urlParams['wizard'] = true;

                return $this->_redirect('*/adminhtml_ebay_listing_categorySettings/', $urlParams);
            }
        }

        $this->setWizardStep('productSelection');

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('ebay_product_add_step_one');
        // ---------------------------------------

        // Set Hide Products In Other Listings
        // ---------------------------------------
        $prefix = $this->getHideProductsInOtherListingsPrefix();

        if ($this->getRequest()->isPost()) {
            $hideProductsOtherParam = $this->getRequest()->getPost('hide_products_others_listings', 1);
            Mage::helper('M2ePro/Data_Session')->setValue($prefix, $hideProductsOtherParam);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('hide_products_others_listings_prefix', $prefix);
        // ---------------------------------------

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->getResponse()->setBody(
                $this->loadLayout()
                     ->getLayout()
                     ->createBlock('M2ePro/adminhtml_ebay_listing_product_sourceProducts_grid')
                     ->toHtml()
            );
        }

        $this->_initAction();

        $this->setComponentPageHelpLink('Adding+Products+from+the+List');

        $this->getLayout()->getBlock('head')->addJs('M2ePro/VideoTutorial.js');

        $this->_title(Mage::helper('M2ePro')->__('Select Products'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product'))
             ->renderLayout();
    }

    protected function stepOneSourceCategories()
    {
        $this->setWizardStep('productSelection');

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('ebay_product_add_step_one');
        // ---------------------------------------

        // Set Hide Products In Other Listings
        // ---------------------------------------
        $prefix = $this->getHideProductsInOtherListingsPrefix();

        if ($this->getRequest()->isPost()) {
            $hideProductsOtherParam = $this->getRequest()->getPost('hide_products_others_listings', 1);
            Mage::helper('M2ePro/Data_Session')->setValue($prefix, $hideProductsOtherParam);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('hide_products_others_listings_prefix', $prefix);
        // ---------------------------------------

        $tempSession = $this->getSessionValue('source_categories');
        $selectedProductsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->getParam('current_category_id')) {
                $this->setSessionValue('current_category_id', $this->getRequest()->getParam('current_category_id'));
            }

            $this->loadLayout();
            /** @var $grid Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Grid */
            $grid = $this->getLayout()
                     ->createBlock('M2ePro/adminhtml_ebay_listing_product_sourceCategories_grid');

            $grid->setSelectedIds($selectedProductsIds);
            $grid->setCurrentCategoryId($this->getSessionValue('current_category_id'));

            return $this->getResponse()->setBody($grid->toHtml());
        }

        $this->_initAction();

        $this->setPageHelpLink(null, null, "add-magento-products-manually");

        $this->getLayout()->getBlock('head')
             ->setCanLoadExtJs(true)
             ->addJs('M2ePro/Ebay/Listing/Product/SourceCategories/Summary/Grid.js')
             ->addJs('M2ePro/VideoTutorial.js');

        $this->_title(Mage::helper('M2ePro')->__('Select Products'));

        $gridContainer = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product');
        $this->_addContent($gridContainer);

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product_sourceCategories_tree');

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

    public function getCategoriesJsonAction()
    {
        $tempSession = $this->getSessionValue('source_categories');
        $selectedProductsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product_sourceCategories_tree');
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

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product_sourceCategories_tree');
        $treeBlock->setSelectedIds($tempSession['products_ids']);

        $this->getResponse()->setBody($treeBlock->getInfoJson());
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

    public function getCategoriesSummaryHtmlAction()
    {
        $this->loadLayout();

        $tempSession = $this->getSessionValue('source_categories');
        $productsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product_sourceCategories_tree');
        $treeBlock->setSelectedIds($productsIds);

        /** @var $block Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Summary_Grid*/
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product_sourceCategories_summary_grid');
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

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product_sourceCategories_tree');
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

    protected function setListingData()
    {
        $listingData = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Listing', $this->getRequest()->getParam('listing_id'))
            ->getData();

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $listingData);
    }

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

    protected function getHideProductsInOtherListingsPrefix()
    {
        $id = $this->getRequest()->getParam('listing_id');

        $prefix = 'ebay_hide_products_others_listings_';
        $prefix .= $id === null ? 'add' : $id;
        $prefix .= '_listing_product';

        return $prefix;
    }

    //########################################

    public function addAction()
    {
        $listing = $this->getListingFromRequest();

        $productsIds = $this->getRequest()->getParam('products');
        $productsIds = explode(',', $productsIds);
        $productsIds = array_unique($productsIds);
        $productsIds = array_filter($productsIds);

        $ids = array();
        foreach ($productsIds as $productId) {
            $listingProduct = $listing->addProduct($productId, Ess_M2ePro_Helper_Data::INITIATOR_USER);
            if ($listingProduct instanceof Ess_M2ePro_Model_Listing_Product) {
                $ids[] = $listingProduct->getId();
            }
        }

        $existingIds = $listing->getChildObject()->getAddedListingProductsIds();
        $existingIds = array_values(array_unique(array_merge($existingIds, $ids)));
        $listing->setData('product_add_ids', Mage::helper('M2ePro')->jsonEncode($existingIds))->save();
    }

    //########################################

    public function deleteAllAction()
    {
        $this->deleteListingProducts($this->getListingFromRequest()->getChildObject()->getAddedListingProductsIds());

        $listing = $this->getListingFromRequest();
        if ($listing->getSetting('additional_data', 'source') == SourceModeBlock::SOURCE_OTHER) {
            $additionalData = $listing->getSettings('additional_data');
            unset($additionalData['source']);
            $listing->setSettings('additional_data', $additionalData);
            $listing->save();

            return $this->_redirect(
                '*/adminhtml_ebay_listing_other/view', array(
                    'account'     => $listing->getAccountId(),
                    'marketplace' => $listing->getMarketplaceId(),
                )
            );
        }

        return $this->_redirect('*/*/', array('_current' => true));
    }

    public function deleteAction()
    {
        $this->deleteListingProducts($this->getRequestIds());
    }

    // ---------------------------------------

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

    //########################################

    protected function setWizardStep($step)
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isActive(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $wizardHelper->setStep(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK, $step);
    }

    //########################################

    public function setAutoActionPopupShownAction()
    {
        Mage::helper('M2ePro/Module')->getRegistry()->setValue('/ebay/listing/autoaction_popup/is_shown/', 1);
    }

    //########################################

    public function validateAction()
    {
        $ids = $this->getListingFromRequest()->getChildObject()->getAddedListingProductsIds();

        if (empty($ids)) {
            $response = array(
                'validation' => false,
                'message' => Mage::helper('M2ePro')->__(
                    'There are no Items to continue. Please, go back and select the Item(s).'
                )
            );
        } else {
            $response = array(
                'validation' => true
            );
        }

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($response));
    }

    //########################################

    public function sourceModeAction()
    {
        if (!$this->getRequest()->getParam('listing_creation')) {
            return $this->_redirect(
                '*/adminhtml_ebay_listing/view', array('id' => $this->getRequest()->getParam('listing_id'))
            );
        }

        $this->setWizardStep('sourceMode');

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_sourceMode'));
        $this->renderLayout();
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

    public function clear()
    {
        $listing = $this->getListingFromRequest();

        Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey . $listing->getId(), true);
        Mage::helper('M2ePro/Data_Session')->getValue($this->_categorySettingsSessionKey . $listing->getId(), true);

        if ($additionalData = $listing->getSettings('additional_data')) {
            unset($additionalData['source']);
            $listing->setSettings('additional_data', $additionalData);
        }

        $listing->setData('product_add_ids', Mage::helper('M2ePro')->jsonEncode(array()));
        $listing->save();
    }

    //########################################
}
