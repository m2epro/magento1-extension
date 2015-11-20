<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Amazon_Listing_ProductAddController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    protected $sessionKey = 'amazon_listing_product_add';

    protected $listing;

    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('%component_name% Listings',
                           Mage::helper('M2ePro/Component_Amazon')->getTitle()));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/ActionHandler.js')
            ->addJs('M2ePro/GridHandler.js')

            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')

            ->addJs('M2ePro/Listing/ActionHandler.js')
            ->addJs('M2ePro/Listing/GridHandler.js')
            ->addJs('M2ePro/Listing/ProductGridHandler.js')

            ->addJs('M2ePro/Common/Listing/GridHandler.js')

            ->addJs('M2ePro/Common/Amazon/Listing/ActionHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/Template/DescriptionHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/ChannelSettingsHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/SearchAsinGridHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/ProductSearchHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/VariationProductManageHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/FulfillmentHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/RepricingHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/NewAsinTemplateDescriptionGridHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(Ess_M2ePro_Helper_Component_Amazon::NICK, 'Add+Magento+Products');

        return $this;
    }

    //########################################

    public function indexAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $listingProductsIds = Mage::helper('M2ePro/Data_Session')->getValue('temp_products');

        if (empty($listingId)) {
            $this->_redirect('*/adminhtml_common_listing/index', array(
                'tab' => Ess_M2ePro_Helper_Component_Amazon::NICK
            ));
            return;
        }

        if (!empty($listingProductsIds)) {
            $this->getListing()->setSetting('additional_data', 'adding_listing_products_ids', $listingProductsIds);
            $this->getListing()->save();

            Mage::helper('M2ePro/Data_Session')->setValue('temp_products', array());
        } else {
            $listingProductsIds = $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');
        }

        if (empty($listingProductsIds)) {
            $this->_redirect('*/adminhtml_common_amazon_listing/view', array('id' => $listingId));
            return;
        }

        $step = (int)$this->getRequest()->getParam('step');

        switch ($step) {
            case 1:
                $this->asinSearchView();
                break;
            case 2:
                $this->addNewAsinView();
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

    protected function asinSearchView()
    {
        $listingProductsIds = $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');

        $this->getListing()->setSetting(
            'additional_data',
            'adding_listing_products_ids',
            $this->filterProductsForSearch($listingProductsIds)
        );
        $this->getListing()->save();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $grid = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_add_searchAsin_grid');
            return $this->getResponse()->setBody($grid->toHtml());
        }

        $this->_initAction();

        $this->setPageHelpLink(NULL, 'pages/viewpage.action?pageId=18188583');

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_add_searchAsin'))
            ->renderLayout();
    }

    protected function addNewAsinView()
    {
        $listingId = $this->getRequest()->getParam('id');

        if (empty($listingId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_add_newAsin');

        $this->_initAction();

        $this->setPageHelpLink(NULL, 'pages/viewpage.action?pageId=18188493');

        $this->_addContent($block)->renderLayout();
    }

    protected function review()
    {

        Mage::helper('M2ePro/Data_Session')->setValue(
            'added_products_ids',
            $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids')
        );

        $this->getListing()->setSetting('additional_data', 'adding_listing_products_ids', array());
        $this->getListing()->setSetting('additional_data', 'adding_new_asin_listing_products_ids', array());
        $this->getListing()->setSetting('additional_data', 'auto_search_was_performed', 0);
        $this->getListing()->save();

        $this->_initAction();

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_add_review'))
            ->renderLayout();
    }

    //########################################

    public function viewListingAction()
    {
        $listingId = $this->getRequest()->getParam('id');

        if (empty($listingId)) {
            $this->_redirect('*/adminhtml_common_listing/index', array(
                'tab' => Ess_M2ePro_Helper_Component_Amazon::NICK
            ));
            return;
        }

        return $this->_redirect('*/adminhtml_common_amazon_listing/view', array(
            'id' => $listingId
        ));
    }

    public function viewListingAndListAction()
    {
        $listingId = $this->getRequest()->getParam('id');

        if (empty($listingId)) {
            $this->_redirect('*/adminhtml_common_listing/index', array(
                'tab' => Ess_M2ePro_Helper_Component_Amazon::NICK
            ));
            return;
        }

        return $this->_redirect('*/adminhtml_common_amazon_listing/view', array(
            'id' => $listingId,
            'do_list' => true
        ));
    }

    //########################################

    public function removeAddedProductsAction()
    {
        $listingProductsIds = $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');

        foreach ($listingProductsIds as $listingProductId) {
            try {
                $listingProduct = Mage::helper('M2ePro/Component_Amazon')
                    ->getObject('Listing_Product',$listingProductId);
                $listingProduct->deleteInstance();
            } catch (Exception $e) {

            }
        }

        $this->getListing()->setSetting('additional_data', 'adding_listing_products_ids', array());
        $this->getListing()->setSetting('additional_data', 'adding_new_asin_listing_products_ids', array());
        $this->getListing()->setSetting('additional_data', 'auto_search_was_performed', 0);
        $this->getListing()->save();

        $this->_redirect('*/adminhtml_common_listing_productAdd/index', array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'step' => 2,
            'source' => Mage::helper('M2ePro/Data_Session')->getValue('products_source'),
            'id' => $this->getRequest()->getParam('id')
        ));
        return;
    }

    //########################################

    public function viewSearchSettingsAction()
    {
        $listingId = $this->getRequest()->getParam('id');

        if (empty($listingId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $settings = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_add_searchAsin_searchSettings');

        return $this->getResponse()->setBody($settings->toHtml());
    }

    public function saveSearchSettingsAction()
    {
        $post = $this->getRequest()->getPost();

        if (empty($post['id'])) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing', $post['id']);

        $listingProduct->setData('general_id_mode',                 $post['general_id_mode'])->save();
        $listingProduct->setData('general_id_custom_attribute',     $post['general_id_custom_attribute'])->save();
        $listingProduct->setData('worldwide_id_mode',               $post['worldwide_id_mode'])->save();
        $listingProduct->setData('worldwide_id_custom_attribute',   $post['worldwide_id_custom_attribute'])->save();
        $listingProduct->setData('search_by_magento_title_mode',    $post['search_by_magento_title_mode'])->save();

        $listingProduct->save();

        $this->_forward('viewSearchSettings');

        return;
    }

    //########################################

    public function checkSearchResultsAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $listingProductsIds = $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');

        if (empty($listingId) || empty($listingProductsIds)) {
            $this->_forward('index');
        }

        $listingProductsIds = $this->filterProductsForNewAsin($listingProductsIds);

        if (empty($listingProductsIds) ||
            !$this->getListing()->getMarketplace()->getChildObject()->isNewAsinAvailable()) {

            $redirectUrl = $this->getUrl('*/*/index', array(
                'step' => 3,
                'id' => $this->getRequest()->getParam('id')
            ));
            return $this->getResponse()->setBody(json_encode(array('redirect' => $redirectUrl)));
        }

        $this->getListing()->setSetting('additional_data', 'adding_new_asin_listing_products_ids', $listingProductsIds);
        $this->getListing()->save();

        $showNewAsinStep = $this->getListing()->getSetting('additional_data', 'show_new_asin_step');
        if (isset($showNewAsinStep)) {
            return $this->getResponse()->setBody(json_encode(array(
                'redirect' => $this->getUrl('*/*/index', array(
                    'id' => $this->getRequest()->getParam('id'),
                    'step' => $showNewAsinStep ? 2 : 3
                ))
            )));
        }

        $newAsinPopup = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_add_searchAsin_newAsinPopup');

        return $this->getResponse()->setBody(json_encode(array('data' => $newAsinPopup->toHtml())));
    }

    //########################################

    public function showNewAsinStepAction()
    {
        $showNewAsinStep = (int)$this->getRequest()->getParam('show_new_asin_step', 1);

        $remember = $this->getRequest()->getParam('remember');

        if ($remember) {
            $this->getListing()->setSetting('additional_data', 'show_new_asin_step', $showNewAsinStep);

            $this->getListing()->save();
        }

        return $this->getResponse()->setBody(json_encode(array(
            'redirect' => $this->getUrl('*/*/index',array(
                'id' => $this->getRequest()->getParam('id'),
                'step' => $showNewAsinStep ? 2 : 3
            ))
        )));
    }

    //########################################

    public function viewTemplateDescriptionPopupAction()
    {
        $mainBlock = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_template_description_main');

        return $this->getResponse()->setBody($mainBlock->toHtml());
    }

    public function viewTemplateDescriptionsGridAction()
    {
        $listingProductsIds = $this->getRequest()->getParam('products_ids');

        if (empty($listingProductsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($listingProductsIds)) {
            $listingProductsIds = explode(',', $listingProductsIds);
        }

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_template_description_grid');
        $grid->setCheckNewAsinAccepted(true);
        $grid->setProductsIds($listingProductsIds);
        $grid->setMapToTemplateJsFn('selectTemplateDescription');

        return $this->getResponse()->setBody($grid->toHtml());
    }

    //########################################

    public function descriptionTemplateAssignTypeAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $listingProductsIds = $this->getRequest()->getParam('products_ids');

        $mode = $this->getRequest()->getParam('mode');
        $descriptionTemplateId = $this->getRequest()->getParam('description_template_id');

        if (empty($listingId) || empty($mode)) {
            $this->_forward('index');
            return;
        }

        if (!is_array($listingProductsIds)) {
            $listingProductsIds = explode(',', $listingProductsIds);
        }

        $listing = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing', $listingId);
        $listingAdditionalData = $listing->getData('additional_data');
        $listingAdditionalData = json_decode($listingAdditionalData, true);

        $listingAdditionalData['new_asin_mode'] = $mode;

        $listing->setData('additional_data', json_encode($listingAdditionalData))->save();

        if ($mode == 'same' && !empty($descriptionTemplateId)) {
            /** @var Ess_M2ePro_Model_Amazon_Template_Description $descriptionTemplate */
            $descriptionTemplate = Mage::helper('M2ePro/Component_Amazon')
                ->getModel('Template_Description')->load($descriptionTemplateId);

            if (!$descriptionTemplate->isEmpty()) {
                if (!empty($listingProductsIds)) {
                    $this->setDescriptionTemplate($listingProductsIds, $descriptionTemplateId);
                    $this->_forward('mapToNewAsin', 'adminhtml_common_amazon_listing');
                }

                return $this->_redirect('*/adminhtml_common_amazon_listing_productAdd/index', array(
                    '_current' => true,
                    'step' => 3
                ));
            }

            unset($listingAdditionalData['new_asin_mode']);

            $listing->setData('additional_data', json_encode($listingAdditionalData))->save();

        } else if ($mode == 'category') {
            return $this->_redirect('*/*/descriptionTemplateAssignByMagentoCategory', array(
                '_current' => true,
            ));
        } else if ($mode == 'manually') {
            return $this->_redirect('*/*/descriptionTemplateAssignManually', array(
                '_current' => true,
            ));
        }

        $this->_forward('index');
    }

    public function descriptionTemplateAssignByMagentoCategoryAction()
    {
        $listingProductsIds = $this->getListing()->getSetting('additional_data','adding_new_asin_listing_products_ids');

        if (empty($listingProductsIds)) {
            $this->_forward('index');
            return;
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $grid = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_add_newAsin_category_grid');
            return $this->getResponse()->setBody($grid->toHtml());
        }

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_add_newAsin_category');

        $this->_initAction();

        $this->setPageHelpLink(NULL, 'pages/viewpage.action?pageId=18188493');

        $this->_addContent($block)->renderLayout();
    }

    public function descriptionTemplateAssignManuallyAction()
    {
        $listingProductsIds = $this->getListing()->getSetting('additional_data','adding_new_asin_listing_products_ids');

        if (empty($listingProductsIds)) {
            $this->_forward('index');
            return;
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $grid = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_add_newAsin_manual_grid');
            return $this->getResponse()->setBody($grid->toHtml());
        }

        $this->_initAction();

        $this->setPageHelpLink(NULL, 'pages/viewpage.action?pageId=18188493');

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_add_newAsin_manual'))
             ->renderLayout();
    }

    //########################################

    public function checkNewAsinCategoryProductsAction()
    {
        $descriptionTemplatesIds = $this->getListing()
            ->getSetting('additional_data', 'adding_new_asin_description_templates_data');

        foreach ($descriptionTemplatesIds as $listingProductId => $descriptionTemplateId) {
            if (empty($descriptionTemplateId)) {
                return $this->getResponse()->setBody(json_encode(array(
                    'type' => 'error',
                    'text' => Mage::helper('M2ePro')
                        ->__('You have not selected the Description Policy for some Magento Categories.')
                )));
            }
        }

        $this->getListing()->setSetting('additional_data', 'adding_new_asin_description_templates_data', array());
        $this->getListing()->save();

        return $this->getResponse()->setBody(1);
    }

    public function checkNewAsinManualProductsAction()
    {
        $listingProductsIds = $this->getListing()->getSetting('additional_data','adding_new_asin_listing_products_ids');

        /** @var Ess_M2ePro_Model_Mysql4_Amazon_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->where(
            "`main_table`.`id` IN (?) AND `second_table`.`template_description_id` IS NULL", $listingProductsIds
        );

        $data = $collection->getData();

        if (empty($data)) {
            return $this->getResponse()->setBody(1);
        }

        $popup = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_add_newAsin_manual_skipPopup');

        return $this->getResponse()->setBody(json_encode(array(
            'total_count' => count($listingProductsIds),
            'failed_count' => count($data),
            'html' => $popup->toHtml()
        )));
    }

    //########################################

    public function resetNewAsinAction()
    {
        $this->getListing()->setSetting('additional_data', 'adding_new_asin_description_templates_data', array());

        $listingProductsIds = $this->getListing()->getSetting('additional_data','adding_new_asin_listing_products_ids');
        $listingProductsIds = Mage::helper('M2ePro/Component_Amazon_Variation')
            ->filterLockedProducts($listingProductsIds);

        if (!empty($listingProductsIds)) {
            $this->setDescriptionTemplate($listingProductsIds, NULL);

            $this->_forward('unmapFromAsin', 'adminhtml_common_amazon_listing', null, array(
                'products_ids' => $listingProductsIds
            ));
        }

        return $this->_redirect('*/adminhtml_common_amazon_listing_productAdd/index', array(
            '_current' => true,
            'step' => 2
        ));
    }

    //########################################

    protected function setDescriptionTemplate($productsIds, $templateId)
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableAmazonListingProduct = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_listing_product');

        $productsIds = array_chunk($productsIds, 1000);
        foreach ($productsIds as $productsIdsChunk) {
            $connWrite->update($tableAmazonListingProduct, array(
                    'template_description_id' => $templateId
                ), '`listing_product_id` IN ('.implode(',', $productsIdsChunk).')'
            );
        }
    }

    //########################################

    protected function runProcessorForParents($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableAmazonListingProduct = Mage::getSingleton('core/resource')
            ->getTableName('m2epro_amazon_listing_product');

        $select = $connRead->select();
        $select->from(array('alp' => $tableAmazonListingProduct), array('listing_product_id'))
            ->where('listing_product_id IN (?)', $productsIds)
            ->where('is_variation_parent = ?', 1);

        $productsIds = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($select);

        foreach ($productsIds as $productId) {
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);
            $listingProduct->getChildObject()->getVariationManager()->getTypeModel()->getProcessor()->process();
        }
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

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if (is_null($this->listing)) {
            $this->listing = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing', $listingId);
        }

        return $this->listing;
    }

    //########################################

    protected function filterProductsForSearch($productsIds)
    {
        $variationHelper = Mage::helper('M2ePro/Component_Amazon_Variation');

        $productsIds = $variationHelper->filterProductsByStatus($productsIds);

        $unsetProducts = $this->getLockedProductsInAction($productsIds);
        $unsetProducts = array_unique($unsetProducts);

        foreach ($unsetProducts as $id) {
            $key = array_search($id, $productsIds);
            unset($productsIds[$key]);
        }

        return $productsIds;
    }

    protected function filterProductsForNewAsin($productsIds)
    {
        return Mage::helper('M2ePro/Component_Amazon_Variation')->filterProductsNotMatchingForNewAsin($productsIds);
    }

    //########################################

    protected function getLockedProductsInAction($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_locked_object');

        $select = $connRead->select();
        $select->from(array('lo' => $table), array('object_id'))
            ->where('model_name = "M2ePro/Listing_Product"')
            ->where('object_id IN (?)', $productsIds)
            ->where('tag = "in_action"');

        return Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($select);
    }

    //########################################
}