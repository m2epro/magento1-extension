<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_ProductAddController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    protected  $sessionKey = 'ebay_listing_product_add';

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
            ->addJs('M2ePro/Ebay/Listing/ProductAddHandler.js')
            ->addJs('M2ePro/Listing/ProductGridHandler.js')

            ->addJs('M2ePro/AttributeHandler.js')
            ->addJs('M2ePro/ActionHandler.js')
            ->addJs('M2ePro/Listing/ActionHandler.js')
            ->addJs('M2ePro/Listing/MovingHandler.js')
            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Listing/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/ViewGridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/BidsHandler.js')
            ->addJs('M2ePro/Ebay/Listing/VariationProductManageHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Settings/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/ProductAdd/Settings/GridHandler.js')

            ->addJs('M2ePro/TemplateHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Template/SwitcherHandler.js')
            ->addJs('M2ePro/Ebay/Template/PaymentHandler.js')
            ->addJs('M2ePro/Ebay/Template/ReturnHandler.js')
            ->addJs('M2ePro/Ebay/Template/ShippingHandler.js')
            ->addJs('M2ePro/Ebay/Template/SellingFormatHandler.js')
            ->addJs('M2ePro/Ebay/Template/DescriptionHandler.js')
            ->addJs('M2ePro/Ebay/Template/SynchronizationHandler.js')

            ->addJs('M2ePro/Listing/Category/TreeHandler.js')
            ->addJs('M2ePro/Listing/AutoActionHandler.js')
            ->addJs('M2ePro/Ebay/Listing/AutoActionHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/ChooserHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/SpecificHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Chooser/BrowseHandler.js');

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        if (Mage::helper('M2ePro/Magento')->isTinyMceAvailable()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        $this->_initPopUp();

        $this->setComponentPageHelpLink('Add+Magento+Products');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/listings');
    }

    //########################################

    public function indexAction()
    {
        if (!$listingId = $this->getRequest()->getParam('listing_id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ((bool)$this->getRequest()->getParam('clear', false)) {
            $this->clear();
            $this->getRequest()->setParam('clear',null);
            return $this->_redirect('*/*/',array('_current' => true, 'step' => 1));
        }

        $this->setListingData();

        $step = (int)$this->getRequest()->getParam('step');

        switch ($step) {
            case 1:
                $action = 'stepOne';
                break;
            case 2:
                $action = 'stepTwo';
                break;
            // ....
            default:
                return $this->_redirect('*/*/index', array('_current' => true,'step' => 1));
        }

        return $this->$action();
    }

    //########################################

    private function stepOne()
    {
        $method = 'stepOneSource'.ucfirst($this->getRequest()->getParam('source'));

        if (!method_exists($this,$method)) {
            return $this->_redirect('*/*/sourceMode', array('_current' => true));
        }

        $this->$method();
    }

    private function stepOneSourceProducts()
    {
        $ids = $this->getListingFromRequest()->getAddedListingProductsIds();

        if (!empty($ids)) {

            if ($this->getRequest()->isXmlHttpRequest()) {
                return $this->getResponse()->setBody((json_encode(array(
                    'ajaxExpired' => 1,
                    'ajaxRedirect' => $this->getUrl('*/*/index', array('_current' => true,'step' => 2))
                ))));
            } else {
                return $this->_redirect('*/*/index', array('_current' => true,'step' => 2));
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

        $this->getLayout()->getBlock('head')->addJs('M2ePro/VideoTutorialHandler.js');

        $this->_title(Mage::helper('M2ePro')->__('Select Products'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product'))
             ->renderLayout();
    }

    private function stepOneSourceCategories()
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
            /* @var $grid Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Grid */
            $grid = $this->getLayout()
                     ->createBlock('M2ePro/adminhtml_ebay_listing_product_sourceCategories_grid');

            $grid->setSelectedIds($selectedProductsIds);
            $grid->setCurrentCategoryId($this->getSessionValue('current_category_id'));

            return $this->getResponse()->setBody($grid->toHtml());
        }

        $this->_initAction();

        $this->setComponentPageHelpLink('Adding+Products+from+Category');

        $this->getLayout()->getBlock('head')
             ->setCanLoadExtJs(true)
             ->addJs('M2ePro/Ebay/Listing/Product/SourceCategories/Summary/GridHandler.js')
             ->addJs('M2ePro/VideoTutorialHandler.js');

        $this->_title(Mage::helper('M2ePro')->__('Select Products'));

        $gridContainer = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product');
        $this->_addContent($gridContainer);

        /* @var $treeBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product_sourceCategories_tree');

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

    public function getCategoriesJsonAction()
    {
        $tempSession = $this->getSessionValue('source_categories');
        $selectedProductsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        /* @var $treeBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product_sourceCategories_tree');
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

        /* @var $treeBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product_sourceCategories_tree');
        $treeBlock->setSelectedIds($tempSession['products_ids']);

        $this->getResponse()->setBody($treeBlock->getInfoJson());
    }

    public function getSessionProductsIdsAction()
    {
        $tempSession = $this->getSessionValue('source_categories');
        $selectedProductsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        $this->getResponse()->setBody(json_encode(array(
            'ids' => $selectedProductsIds
        )));
    }

    public function getCategoriesSummaryHtmlAction()
    {
        $this->loadLayout();

        $tempSession = $this->getSessionValue('source_categories');
        $productsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        /* @var $treeBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product_sourceCategories_tree');
        $treeBlock->setSelectedIds($productsIds);

        /* @var $block Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Summary_Grid*/
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
        /* @var $treeBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product_sourceCategories_tree');
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

    private function stepTwo()
    {
        $ids = $this->getListingFromRequest()->getAddedListingProductsIds();

        if (empty($ids)) {
            return $this->_redirect('*/*/index', array('_current' => true,'step' => 1));
        }

        $listingAdditionalData = $this->getListingFromRequest()->getData('additional_data');
        $listingAdditionalData = json_decode($listingAdditionalData, true);

        if (!is_null($this->getSessionValue('show_settings_step'))) {
            if (!$this->getSessionValue('show_settings_step')) {
                return $this->_redirect(
                    '*/adminhtml_ebay_listing_categorySettings/',array('_current' => true,'step' => 1)
                );
            }
        } elseif (isset($listingAdditionalData['show_settings_step'])) {
            if (!$listingAdditionalData['show_settings_step']) {
                return $this->_redirect(
                    '*/adminhtml_ebay_listing_categorySettings/',array('_current' => true,'step' => 1)
                );
            }
        }

        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return $this->_redirect('*/adminhtml_ebay_listing_categorySettings/',array('_current' => true,'step' => 1));
        }

        $this->setWizardStep('productSettings');

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('ebay_product_add_step_two');
        // ---------------------------------------

        $this->_initAction();

        $this->setPageHelpLink(NULL, 'pages/viewpage.action?pageId=17367111');

        $this->_title(Mage::helper('M2ePro')->__('Set Products Settings'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_settings'))
             ->renderLayout();
    }

    //########################################

    protected function setListingData()
    {
        $listingData = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Listing',$this->getRequest()->getParam('listing_id'))
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
        $id = $this->getRequest()->getParam('listing_id');

        $prefix = 'ebay_hide_products_others_listings_';
        $prefix .= is_null($id) ? 'add' : $id;
        $prefix .= '_listing_product';

        return $prefix;
    }

    //########################################

    public function stepTwoGridAction()
    {
        $this->setListingData();

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('ebay_product_add_step_two');
        // ---------------------------------------

        $response = $this->loadLayout()->getLayout()
                         ->createBlock('M2ePro/adminhtml_ebay_listing_settings_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function addAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $productsIds = $this->getRequest()->getParam('products');
        $productsIds = explode(',', $productsIds);
        $productsIds = array_unique($productsIds);
        $productsIds = array_filter($productsIds);

        $ids = array();
        foreach ($productsIds as $productId) {
            $listingProduct = $listing->addProduct($productId);
            if ($listingProduct instanceof Ess_M2ePro_Model_Listing_Product) {
                $ids[] = $listingProduct->getId();
            }
        }

        // ---------------------------------------
        $existingIds = $this->getListingFromRequest()->getAddedListingProductsIds();
        $existingIds = array_values(array_unique(array_merge($existingIds,$ids)));
        $listing->setData('product_add_ids',json_encode($existingIds))->save();
        // ---------------------------------------
    }

    //########################################

    public function deleteAllAction()
    {
        $this->delete($this->getListingFromRequest()->getAddedListingProductsIds());

        return $this->_redirect('*/*/',array('_current' => true));
    }

    public function deleteAction()
    {
        $this->delete($this->getRequestIds());
    }

    private function delete($ids)
    {
        $ids = array_map('intval',$ids);

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

    //########################################

    private function clear()
    {
        Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey,true);

        $categorySettingsSessionKey = 'ebay_listing_category_settings';
        Mage::helper('M2ePro/Data_Session')->getValue($categorySettingsSessionKey,true);

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $listing->setData('product_add_ids',json_encode(array()))->save();
    }

    //########################################

    private function setWizardStep($step)
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isActive(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $wizardHelper->setStep(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK,$step);
    }

    //########################################

    public function setAutoActionPopupShownAction()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/view/ebay/advanced/autoaction_popup/', 'shown', 1
        );
    }

    //########################################

    public function setShowSettingsStepAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $showSettingsStep = $this->getRequest()->getParam('show_settings_step');

        $this->setSessionValue('show_settings_step',(bool)$showSettingsStep);

        $remember = $this->getRequest()->getParam('remember');

        if (!$remember) {
            return;
        }

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',$listingId
        );
        $listingAdditionalData = $listing->getData('additional_data');
        $listingAdditionalData = json_decode($listingAdditionalData, true);

        $listingAdditionalData['show_settings_step'] = (bool)$showSettingsStep;

        $listing->setData('additional_data', json_encode($listingAdditionalData))->save();
    }

    //########################################

    public function validateAction()
    {
        $ids = $this->getListingFromRequest()->getAddedListingProductsIds();

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

        $this->getResponse()->setBody(json_encode($response));
    }

    //########################################

    public function sourceModeAction()
    {
        $this->setWizardStep('sourceMode');

        if ($this->getRequest()->isPost()) {
            $source = $this->getRequest()->getPost('source');

            if (!in_array($source,array('products','categories'))) {
                return $this->_redirect('*/*/*',array('_current' => true));
            }

            $this->setSessionValue('source', $source);

            return $this->_redirect(
                '*/adminhtml_ebay_listing_productAdd',
                array(
                    'source' => $source,
                    'clear' => true,
                    'listing_creation' => true,
                    '_current' => true
                )
            );
        }

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_sourceMode'));
        $this->renderLayout();
    }

    //########################################

    protected function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();
        $sessionData[$key] = $value;

        Mage::helper('M2ePro/Data_Session')->setValue($this->sessionKey, $sessionData);

        return $this;
    }

    protected function getSessionValue($key = NULL)
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

    //########################################

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

    //########################################
}