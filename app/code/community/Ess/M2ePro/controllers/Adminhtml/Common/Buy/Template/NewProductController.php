<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Buy_Template_NewProductController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    private $listingProductIds = array();

    //########################################

    public function preDispatch()
    {
        parent::preDispatch();
        $this->listingProductIds = Mage::helper('M2ePro/Data_Session')->getValue('buy_listing_product_ids');
    }

    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('Rakuten.com Listings'));

        $this->getLayout()->getBlock('head')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/AttributeHandler.js')
            ->addJs('M2ePro/Common/Buy/Template/NewProduct/Handler.js')
            ->addJs('M2ePro/Common/Buy/Template/NewProduct/AttributeHandler.js');

        $this->_initPopUp();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/listings');
    }

    //########################################

    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $this->saveListingProductIds();
        }

        if (empty($this->listingProductIds)) {
            return $this->_redirect('*/adminhtml_common_listing/',array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_BUY
            ));
        }

        $collection = Mage::helper('M2ePro/Component_Buy')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('status',Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)
            ->addFieldToFilter('general_id',array('null' => true))
            ->addFieldToFilter('id',array('in' => $this->listingProductIds));

        if ($collection->getSize() < 1) {
            $listingId = Mage::helper('M2ePro/Component_Buy')
                ->getObject('Listing_Product',reset($this->listingProductIds))
                ->getListingId();

            $errorMessage = Mage::helper('M2ePro')->__(
                'Please select Not Listed Items without Rakuten.com SKU assigned.'
            );
            $this->_getSession()->addError($errorMessage);
            return $this->_redirect('*/adminhtml_common_buy_listing/view',array(
                'id' => $listingId
            ));
        }

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_template_newProduct'))
            ->renderLayout();
    }

    public function templateNewProductGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_buy_template_newProduct_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function addAction()
    {
        if (count($this->listingProductIds) < 1 && is_null($this->getRequest()->getParam('id'))) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Not Listed Items.'));
            return $this->_redirect('*/adminhtml_common_listing', array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_BUY
            ));
        }

        if ($this->getRequest()->isPost()) {
            return $this->_forward('save');
        }

        $this->_initAction()
            ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_template_newProduct_edit_tabs'))
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_template_newProduct_edit'))
            ->renderLayout();
    }

    public function mapAction()
    {
        $buyTemplateNewProductInstance = Mage::getModel('M2ePro/Buy_Template_NewProduct')->loadInstance(
            (int)$this->getRequest()->getParam('id')
        );

        return $this->map($buyTemplateNewProductInstance);
    }

    public function saveAction()
    {
        $post = $this->getRequest()->getPost();
        if (!isset($post['category'])) {
            return $this->_redirect('*/adminhtml_common_buy_template_newProduct/index');
        }

        // ---------------------------------------
        /** @var $buyTemplateNewProductInstance Ess_M2ePro_Model_Buy_Template_NewProduct */
        $buyTemplateNewProductInstance = Mage::getModel('M2ePro/Buy_Template_NewProduct');
        if ($post['category']['category_id']) {
            $buyTemplateNewProductInstance->loadInstance((int)$post['category']['category_id']);
        }

        // Saving general data
        // ---------------------------------------

        $buyTemplateNewProductInstance->addData(array(
            'title'          => $post['category']['title'],
            'node_title'     => $post['category']['node_title'],
            'category_path'  => $post['category']['path'],
            'category_id'    => (int)$post['category']['native_id'],
        ));
        $buyTemplateNewProductInstance->save();

        // Saving core info
        // ---------------------------------------
        $data = array();
        $keys = array(
            'seller_sku_custom_attribute',

            'gtin_mode',
            'gtin_custom_attribute',

            'isbn_mode',
            'isbn_custom_attribute',

            'mfg_name_template',

            'mfg_part_number_mode',
            'mfg_part_number_custom_value',
            'mfg_part_number_custom_attribute',

            'product_set_id_mode',
            'product_set_id_custom_value',
            'product_set_id_custom_attribute',

            'title_mode',
            'title_template',

            'description_mode',
            'description_template',

            'main_image_mode',
            'main_image_attribute',

            'additional_images_mode',
            'additional_images_attribute',
            'additional_images_limit',

            'features_mode',
            'features_template',

            'keywords_mode',
            'keywords_custom_attribute',
            'keywords_custom_value',

            'weight_mode',
            'weight_custom_value',
            'weight_custom_attribute',
        );

        foreach ($keys as $key) {
            if (isset($post['category'][$key])) {
                $data[$key] = $post['category'][$key];
            }
        }

        $data['title'] = $post['category']['path'];
        $data['features_template'] = json_encode(array_filter($post['category']['features_template']));
        $data['template_new_product_id'] = $buyTemplateNewProductInstance->getId();
        // ---------------------------------------

        // Add or update model
        // ---------------------------------------
        /* @var $templateCoreInstance Ess_M2ePro_Model_Buy_Template_NewProduct_Core */
        $templateCoreInstance = Mage::getModel('M2ePro/Buy_Template_NewProduct_Core');
        if ($post['category']['category_id']) {
            $templateCoreInstance->loadInstance($buyTemplateNewProductInstance->getId());
        }

        $templateCoreInstance->addData($data)->save();
        // ---------------------------------------

        // Saving attributes info
        // ---------------------------------------
        /* @var $templateAttributesInstance Ess_M2ePro_Model_Buy_Template_NewProduct */
        $templateAttributesInstance = Mage::getModel('M2ePro/Buy_Template_NewProduct')
            ->loadInstance($buyTemplateNewProductInstance->getId());

        $attributes = $templateAttributesInstance->getAttributes(true);

        foreach ($attributes as $attribute) {
            $attribute->deleteInstance();
        }

        if (!isset($post['attributes'])) {
            $post['attributes'] = array();
        }

        foreach ($post['attributes'] as $name => $aData) {

            if (empty($aData['mode'])) {
                continue;
            }

            if (empty($aData['recommended_value']) &&
                !in_array($aData['mode'],array(Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_NONE,
                    Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_CUSTOM_VALUE,
                    Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_CUSTOM_ATTRIBUTE))) {
                continue;
            }
            if (empty($aData['custom_value']) &&
                !in_array($aData['mode'],array(
                    Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_NONE,
                    Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_RECOMMENDED_VALUE,
                    Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_CUSTOM_ATTRIBUTE))) {
                continue;
            }
            if (empty($aData['custom_attribute']) &&
                !in_array($aData['mode'],array(
                    Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_NONE,
                    Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_RECOMMENDED_VALUE,
                    Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_CUSTOM_VALUE))) {
                continue;
            }

            /** @var $buyTemplateNewProductAttributeInstance Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute */
            $buyTemplateNewProductAttributeInstance = Mage::getModel('M2ePro/Buy_Template_NewProduct_Attribute');

            $recommendedValue = $aData['mode'] ==
                Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_RECOMMENDED_VALUE
                ? json_encode($aData['recommended_value']) : '';
            $customValue = $aData['mode'] ==
                Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_CUSTOM_VALUE
                ? $aData['custom_value'] : '';
            $customAttribute = $aData['mode'] ==
                Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_CUSTOM_ATTRIBUTE
                ? $aData['custom_attribute'] : '';

            $buyTemplateNewProductAttributeInstance->addData(array(
                'template_new_product_id' => $buyTemplateNewProductInstance->getId(),
                'attribute_name'          => $name,
                'mode'                    => $aData['mode'],
                'recommended_value'       => $recommendedValue,
                'custom_value'            => $customValue,
                'custom_attribute'        => $customAttribute,
            ));
            $buyTemplateNewProductAttributeInstance->save();
        }
        // ---------------------------------------

        if ($this->getRequest()->getParam('do_map')) {
            return $this->map($buyTemplateNewProductInstance);
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy has been successfully saved.'));

        if ($listingProductId = $this->getRequest()->getParam('listing_product_id')) {

            $listingId = Mage::helper('M2ePro/Component_Buy')
                ->getObject('Listing_Product',$listingProductId)
                ->getListingId();

            return $this->_redirect('*/adminhtml_common_buy_listing/view',array(
                'id' => $listingId
            ));
        }

        return $this->_redirect('*/adminhtml_common_buy_template_newProduct/index');
    }

    public function editAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if (!$id) {
            return '';
        }

        /* @var $buyTemplateNewProductInstance Ess_M2ePro_Model_Buy_Template_NewProduct */
        $buyTemplateNewProductInstance = Mage::getModel('M2ePro/Buy_Template_NewProduct')->loadInstance($id);

        $formData['category']   = $buyTemplateNewProductInstance->getCoreTemplate()->getData();
        $formData['category']   = array_merge($formData['category'], $buyTemplateNewProductInstance->getData());
        $formData['attributes'] = array();

        $attributes = $buyTemplateNewProductInstance->getAttributes(true);
        foreach ($attributes as $attribute) {
            $formData['attributes'][] = $attribute->getData();
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data',$formData);

        return $this->_forward('add');
    }

    public function deleteAction()
    {
        $ids = $this->getRequest()->getParam('ids','');
        !is_array($ids) && $ids = array($ids);

        if (empty($ids)) {
            return;
        }

        $buyTemplateNewProductInstances = Mage::getModel('M2ePro/Buy_Template_NewProduct')
            ->getCollection()
                ->addFieldToFilter('id', array('in' => $ids))
                ->getItems();

        $countOfSuccessfullyDeletedTemplates = 0;

        foreach ($buyTemplateNewProductInstances as $buyTemplateNewProductInstance) {
            if ($buyTemplateNewProductInstance->deleteInstance()) {
                $countOfSuccessfullyDeletedTemplates++;
                continue;
            }
        }

        if (!$countOfSuccessfullyDeletedTemplates) {
            $this->_getSession()->addError(
                'New SKU Policy(ies) cannot be deleted as it has assigned Product(s)'
            );
            return $this->_redirectUrl($this->_getRefererUrl());
        }

        if ($countOfSuccessfullyDeletedTemplates == count($buyTemplateNewProductInstances)) {
            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__(
                '%amount% record(s) were successfully deleted.', $countOfSuccessfullyDeletedTemplates
            ));
            return $this->_redirectUrl($this->_getRefererUrl());
        }

        $this->_getSession()->addError(Mage::helper('M2ePro')->__(
            'Some of the New SKU Policy(ies) cannot be deleted as they have assigned Product(s)'
        ));
        return $this->_redirectUrl($this->_getRefererUrl());
    }

    public function getCategoriesAction()
    {
        $nodeId = $this->getRequest()->getParam('node_id');

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_buy_dictionary_category');

        return $this->getResponse()->setBody(json_encode(
            $connRead->select()
                ->from($table,'*')
                ->where('node_id = ?', $nodeId)
                ->order('title ASC')
                ->query()
                ->fetchAll()
        ));
    }

    public function getAttributesAction()
    {
        $native_id = $this->getRequest()->getParam('native_id');

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_buy_dictionary_category');

        return $this->getResponse()->setBody(json_encode(
            $connRead->select()
                ->from($table,'attributes')
                ->where('native_id = ?', (int)$native_id)
                ->query()
                ->fetchAll()
        ));
    }

    public function searchCategoryAction()
    {
        $keywords = $this->getRequest()->getParam('keywords','');

        if ($keywords == '' || strlen($keywords) < 3) {
            return $this->getResponse()->setBody(json_encode(array(
                'result' => 'error',
                'message' => Mage::helper('M2ePro')->__('Each keyword should be at least three characters.')
            )));
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $connRead->select()->limit(1000);
        $select->from(Mage::getSingleton('core/resource')->getTableName('m2epro_buy_dictionary_category'),'*');
        $select->where('is_leaf = 1');

        $where = '';
        $parts = explode(' ', $keywords);
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part == '') {
                continue;
            } elseif (strlen($part) < 3) {
                return $this->getResponse()->setBody(json_encode(array(
                    'result' => 'error',
                    'message' => Mage::helper('M2ePro')->__('Each keyword should be at least three characters.')
                )));
            }
            $where != '' && $where .= ' OR ';

            $part = $connRead->quote('%'.$part.'%');

            $where .= 'title LIKE '.$part;
            $where .= ' OR path LIKE '.$part;
        }

        $select->where($where);
        $select->order('id ASC');

        $results = $select->query()->fetchAll();

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data',$results);

        $block = $this->loadLayout()
                      ->getLayout()
                      ->createBlock('M2ePro/adminhtml_common_buy_template_newProduct_search_grid');
        return $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    private function map(Ess_M2ePro_Model_Buy_Template_NewProduct $buyTemplateNewProductInstance)
    {
        if (count($this->listingProductIds) < 1) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('There are no Items to assign.'));
            return $this->_redirect('*/adminhtml_common_listing');
        }

        $result = $buyTemplateNewProductInstance->map($this->listingProductIds);

        if (!$result) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Product(s) was not assigned'));
            return $this->_redirect('*/adminhtml_common_buy_template_newProduct/index');
        }

        $listingId = Mage::helper('M2ePro/Component_Buy')
            ->getObject('Listing_Product',reset($this->listingProductIds))
            ->getListingId();

        $tempMessage = Mage::helper('M2ePro')->__('Policy has been successfully assigned.');
        $this->_getSession()->addSuccess($tempMessage);

        return $this->_redirect('*/adminhtml_common_buy_listing/view',array(
            'id' => $listingId
        ));
    }

    //########################################

    private function saveListingProductIds()
    {
        $listingProductIds = $this->getRequest()->getParam('listing_product_ids');
        $listingProductIds = explode(',',$listingProductIds);
        $listingProductIds = array_filter(array_unique($listingProductIds));

        if (empty($listingProductIds)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__(
                'Please select at least 1 Listing Product.'
            ));
        }

        Mage::helper('M2ePro/Data_Session')->setValue('buy_listing_product_ids',$listingProductIds);
        $this->listingProductIds = Mage::helper('M2ePro/Data_Session')->getValue('buy_listing_product_ids');
    }

    //########################################
}