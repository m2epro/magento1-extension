<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_GeneralController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //#############################################

    public function getAccountsAction()
    {
        $component = $this->getRequest()->getParam('component');

        $collection = Mage::helper('M2ePro/Component')->getComponentCollection($component,'Account');

        $accounts = array();
        foreach ($collection->getItems() as $account) {
            $data = array(
                'id' => $account->getId(),
                'title' => Mage::helper('M2ePro')->escapeHtml($account->getTitle())
            );

            if ($component == Ess_M2ePro_Helper_Component_Amazon::NICK) {
                $data['marketplace_title'] = $account->getChildObject()->getMarketplace()->getTitle();
                $data['marketplace_url'] = $account->getChildObject()->getMarketplace()->getUrl();
            }

            $accounts[] = $data;
        }

        $this->loadLayout();
        $this->getResponse()->setBody(json_encode($accounts));
    }

    //#############################################

    public function validationCheckRepetitionValueAction()
    {
        $model = $this->getRequest()->getParam('model','');

        $component = $this->getRequest()->getParam('component');

        $dataField = $this->getRequest()->getParam('data_field','');
        $dataValue = $this->getRequest()->getParam('data_value','');

        if ($model == '' || $dataField == '' || $dataValue == '') {
            return $this->getResponse()->setBody(json_encode(array('result'=>false)));
        }

        $collection = Mage::getModel('M2ePro/'.$model)->getCollection();

        if ($dataField != '' && $dataValue != '') {
            $collection->addFieldToFilter($dataField, array('in'=>array($dataValue)));
        }

        $idField = $this->getRequest()->getParam('id_field','id');
        $idValue = $this->getRequest()->getParam('id_value','');

        if ($idField != '' && $idValue != '') {
            $collection->addFieldToFilter($idField, array('nin'=>array($idValue)));
        }

        if ($component) {
            $collection->addFieldToFilter('component_mode', $component);
        }

        return $this->getResponse()->setBody(json_encode(array('result'=>!(bool)$collection->getSize())));
    }

    //#############################################

    public function synchCheckStateAction()
    {
        $lockItem = Mage::getModel('M2ePro/Synchronization_LockItem');

        if ($lockItem->isExist()) {
            return $this->getResponse()->setBody('executing');
        }

        return $this->getResponse()->setBody('inactive');
    }

    public function synchGetLastResultAction()
    {
        $operationHistoryCollection = Mage::getModel('M2ePro/Synchronization_OperationHistory')->getCollection();
        $operationHistoryCollection->addFieldToFilter('nick', 'synchronization');
        $operationHistoryCollection->setOrder('id', 'DESC');
        $operationHistoryCollection->getSelect()->limit(1);

        $operationHistory = $operationHistoryCollection->getFirstItem();

        $logCollection = Mage::getModel('M2ePro/Synchronization_Log')->getCollection();
        $logCollection->addFieldToFilter('operation_history_id', (int)$operationHistory->getId());
        $logCollection->addFieldToFilter('type', array('in' => array(Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR)));

        if ($logCollection->getSize() > 0) {
            return $this->getResponse()->setBody('error');
        }

        $logCollection = Mage::getModel('M2ePro/Synchronization_Log')->getCollection();
        $logCollection->addFieldToFilter('operation_history_id', (int)$operationHistory->getId());
        $logCollection->addFieldToFilter('type', array('in' => array(Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING)));

        if ($logCollection->getSize() > 0) {
            return $this->getResponse()->setBody('warning');
        }

        return $this->getResponse()->setBody('success');
    }

    public function synchGetExecutingInfoAction()
    {
        $response = array();
        $lockItem = Mage::getModel('M2ePro/Synchronization_LockItem');

        if (!$lockItem->isExist()) {
            $response['mode'] = 'inactive';
        } else {
            $response['mode'] = 'executing';
            $response['title'] = $lockItem->getTitle();
            $response['percents'] = $lockItem->getPercents();
            $response['status'] = $lockItem->getStatus();
        }

        return $this->getResponse()->setBody(json_encode($response));
    }

    //#############################################

    public function modelGetAllAction()
    {
        $model = $this->getRequest()->getParam('model','');
        $componentMode = $this->getRequest()->getParam('component_mode', '');

        $idField = $this->getRequest()->getParam('id_field','id');
        $dataField = $this->getRequest()->getParam('data_field','');

        if ($model == '' || $idField == '' || $dataField == '') {
            return $this->getResponse()->setBody(json_encode(array()));
        }

        $collection = Mage::getModel('M2ePro/'.$model)->getCollection();
        $componentMode != '' && $collection->addFieldToFilter('component_mode', $componentMode);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                ->columns(array($idField, $dataField));

        $sortField = $this->getRequest()->getParam('sort_field','');
        $sortDir = $this->getRequest()->getParam('sort_dir','ASC');

        if ($sortField != '' && $sortDir != '') {
            $collection->setOrder('main_table.'.$sortField,$sortDir);
        }

        $limit = $this->getRequest()->getParam('limit',NULL);
        !is_null($limit) && $collection->setPageSize((int)$limit);

        $data = $collection->toArray();

        return $this->getResponse()->setBody(json_encode($data['items']));
    }

    //#############################################

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

    //#############################################

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
        $categoryId = (int) $this->getRequest()->getParam('id',false);
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

    //#############################################

    public function requirementsPopupCloseAction()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/view/requirements/popup/', 'closed', 1);
    }

    //#############################################

    public function checkCustomerIdAction()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        return $this->getResponse()->setBody(json_encode(array(
            'ok' => (bool)Mage::getModel('customer/customer')->load($customerId)->getId()
        )));
    }

    //#############################################
}