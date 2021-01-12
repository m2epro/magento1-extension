<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_GeneralController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    public function isMarketplaceEnabledAction()
    {
        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = Mage::helper('M2ePro/Component')->getUnknownObject(
            'Marketplace', (int)$this->getRequest()->getParam('marketplace_id')
        );

        $this->loadLayout();
        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'status' => $marketplace->isStatusEnabled()
                                && $marketplace->getResource()->isDictionaryExist($marketplace)
                )
            )
        );
    }

    //########################################

    public function getAccountsAction()
    {
        $component = $this->getRequest()->getParam('component');

        $collection = Mage::helper('M2ePro/Component')->getComponentCollection($component, 'Account');

        $accounts = array();
        foreach ($collection->getItems() as $account) {
            $data = array(
                'id' => $account->getId(),
                'title' => Mage::helper('M2ePro')->escapeHtml($account->getTitle())
            );

            if ($component == Ess_M2ePro_Helper_Component_Amazon::NICK ||
                $component == Ess_M2ePro_Helper_Component_Walmart::NICK
            ) {
                $data['marketplace_title'] = $account->getChildObject()->getMarketplace()->getTitle();
                $data['marketplace_url'] = $account->getChildObject()->getMarketplace()->getUrl();
                $data['marketplace_id'] = $account->getChildObject()->getMarketplace()->getId();
            }

            $accounts[] = $data;
        }

        $this->loadLayout();
        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($accounts));
    }

    //########################################

    public function validationCheckRepetitionValueAction()
    {
        $model = $this->getRequest()->getParam('model', '');

        $component = $this->getRequest()->getParam('component');

        $dataField = $this->getRequest()->getParam('data_field', '');
        $dataValue = $this->getRequest()->getParam('data_value', '');

        if ($model == '' || $dataField == '' || $dataValue == '') {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result'=>false)));
        }

        $collection = Mage::getModel('M2ePro/'.$model)->getCollection();

        if ($dataField != '' && $dataValue != '') {
            $collection->addFieldToFilter($dataField, array('in'=>array($dataValue)));
        }

        $idField = $this->getRequest()->getParam('id_field', 'id');
        $idValue = $this->getRequest()->getParam('id_value', '');

        if ($idField != '' && $idValue != '') {
            $collection->addFieldToFilter($idField, array('nin'=>array($idValue)));
        }

        if ($component) {
            $collection->addFieldToFilter('component_mode', $component);
        }

        $filterField = $this->getRequest()->getParam('filter_field');
        $filterValue = $this->getRequest()->getParam('filter_value');

        if ($filterField && $filterValue) {
            $collection->addFieldToFilter($filterField, $filterValue);
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array('result'=>!(bool)$collection->getSize())
            )
        );
    }

    //########################################

    public function modelGetAllAction()
    {
        $model = $this->getRequest()->getParam('model', '');
        $componentMode = $this->getRequest()->getParam('component_mode', '');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id', '');
        $isCustomTemplate = $this->getRequest()->getParam('is_custom_template', null);

        $idField = $this->getRequest()->getParam('id_field', 'id');
        $dataField = $this->getRequest()->getParam('data_field', '');

        if ($model == '' || $idField == '' || $dataField == '') {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array()));
        }

        if ($componentMode != '') {
            $collection = Mage::helper('M2ePro/Component')
                ->getComponentModel($componentMode, $model)
                ->getCollection();
        } else {
            $collection = Mage::getModel('M2ePro/'.$model)->getCollection();
        }

        $marketplaceId != '' && $collection->addFieldToFilter('marketplace_id', $marketplaceId);
        $isCustomTemplate != null && $collection->addFieldToFilter('is_custom_template', $isCustomTemplate);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                ->columns(array($idField, $dataField));

        $sortField = $this->getRequest()->getParam('sort_field', '');
        $sortDir = $this->getRequest()->getParam('sort_dir', 'ASC');

        if ($sortField != '' && $sortDir != '') {
            $collection->setOrder('main_table.'.$sortField, $sortDir);
        }

        $limit = $this->getRequest()->getParam('limit', null);
        $limit !== null && $collection->setPageSize((int)$limit);

        $data = $collection->toArray();

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($data['items']));
    }

    //########################################

    public function magentoRuleGetNewConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $prefix = $this->getRequest()->getParam('prefix');
        $storeId = $this->getRequest()->getParam('store', 0);

        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $ruleModelPrefix = '';
        $attributeCode = !empty($typeArr[1]) ? $typeArr[1] : '';
        if (count($typeArr) == 3) {
            $ruleModelPrefix = ucfirst($typeArr[1]) . '_';
            $attributeCode = !empty($typeArr[2]) ? $typeArr[2] : '';
        }

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('M2ePro/'.$ruleModelPrefix.'Magento_Product_Rule'))
            ->setPrefix($prefix);

        if ($type == 'M2ePro/'.$ruleModelPrefix.'Magento_Product_Rule_Condition_Combine') {
            $model->setData($prefix, array());
        }

        if (!empty($attributeCode)) {
            $model->setAttribute($attributeCode);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Interface) {
            $model->setJsFormObject($prefix);
            $model->setStoreId($storeId);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }

        $this->getResponse()->setBody($html);
    }

    public function getRuleConditionChooserHtmlAction()
    {
        $request = $this->getRequest();

        switch ($request->getParam('attribute')) {
            case 'sku':
                $block = $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_magento_product_rule_chooser_sku',
                    'product_rule_chooser_sku',
                    array(
                        'js_form_object' => $request->getParam('form'),
                        'store' => $request->getParam('store', 0)
                    )
                );
                break;

            case 'category_ids':
                $ids = $request->getParam('selected', array());
                if (is_array($ids)) {
                    foreach ($ids as $key => &$id) {
                        $id = (int) $id;
                        if ($id <= 0) {
                            unset($ids[$key]);
                        }
                    }

                    $ids = array_unique($ids);
                } else {
                    $ids = array();
                }

                $block = $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_magento_product_rule_chooser_category',
                    'promo_widget_chooser_category_ids',
                    array('js_form_object' => $request->getParam('form'))
                )->setCategoryIds($ids);
                break;

            default:
                $block = false;
                break;
        }

        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

    //########################################

    public function categoriesJsonAction()
    {
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }

            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_category_tree')
                    ->getTreeJson($category)
            );
        }
    }

    protected function _initCategory()
    {
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        $storeId    = (int) $this->getRequest()->getParam('store');

        $category   = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    $this->_redirect('*/*/', array('_current'=>true, 'id'=>null));
                    return false;
                }
            }
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);

        return $category;
    }

    //########################################

    public function requirementsPopupCloseAction()
    {
        Mage::helper('M2ePro/Module')->getRegistry()->setValue('/view/requirements/popup/closed/', 1);
    }

    //########################################

    public function checkCustomerIdAction()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'ok' => (bool)Mage::getModel('customer/customer')->load($customerId)->getId()
                )
            )
        );
    }

    //########################################

    public function getCreateAttributeHtmlPopupAction()
    {
        $post = $this->getRequest()->getPost();

        /** @var Ess_M2ePro_Block_Adminhtml_General_CreateAttribute $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_general_createAttribute');
        $block->handlerId($post['handler_id']);

        if (isset($post['allowed_attribute_types'])) {
            $block->allowedTypes(explode(',', $post['allowed_attribute_types']));
        }

        if (isset($post['apply_to_all_attribute_sets']) && !$post['apply_to_all_attribute_sets']) {
            $block->applyToAll(false);
        }

        $this->getResponse()->setBody($block->toHtml());
    }

    public function generateAttributeCodeByLabelAction()
    {
        $label = $this->getRequest()->getParam('store_label');
        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                Ess_M2ePro_Model_Magento_Attribute_Builder::generateCodeByLabel($label)
            )
        );
    }

    public function isAttributeCodeUniqueAction()
    {
        $attributeObj = Mage::getModel('eav/entity_attribute')->loadByCode(
            Mage::getModel('catalog/product')->getResource()->getTypeId(),
            $this->getRequest()->getParam('code')
        );

        $isAttributeUnique = $attributeObj->getId() === null;
        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($isAttributeUnique));
    }

    public function createAttributeAction()
    {
        /** @var Ess_M2ePro_Model_Magento_Attribute_Builder $model */
        $model = Mage::getModel('M2ePro/Magento_Attribute_Builder');

        $model->setLabel($this->getRequest()->getParam('store_label'))
              ->setCode($this->getRequest()->getParam('code'))
              ->setInputType($this->getRequest()->getParam('input_type'))
              ->setDefaultValue($this->getRequest()->getParam('default_value'))
              ->setScope($this->getRequest()->getParam('scope'));

        $attributeResult = $model->save();

        if (!isset($attributeResult['result']) || !$attributeResult['result']) {
            $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($attributeResult));
            return;
        }

        foreach ($this->getRequest()->getParam('attribute_sets', array()) as $seId) {

            /** @var Mage_Eav_Model_Entity_Attribute_Set $set */
            $set = Mage::getModel('eav/entity_attribute_set')->load($seId);

            if (!$set->getId()) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Magento_Attribute_Relation $model */
            $model = Mage::getModel('M2ePro/Magento_Attribute_Relation');
            $model->setAttributeObj($attributeResult['obj'])
                  ->setAttributeSetObj($set);

            $setResult = $model->save();

            if (!isset($setResult['result']) || !$setResult['result']) {
                $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($setResult));
                return;
            }
        }

        unset($attributeResult['obj']);
        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($attributeResult));
    }

    //########################################
}
