<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Buy_AccountController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Configuration'))
            ->_title(Mage::helper('M2ePro')->__('Rakuten.com Accounts'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addJs('M2ePro/Common/Buy/AccountHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(Ess_M2ePro_Helper_Component_Buy::NICK, 'Accounts');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/configuration');
    }

    //########################################

    public function indexAction()
    {
        return $this->_redirect('*/adminhtml_common_account/index');
    }

    //########################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Buy')->getModel('Account')->load($id);

        if ($id && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        $this->_initAction()
            ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_account_edit_tabs'))
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_account_edit'))
            ->renderLayout();
    }

    //########################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/*/index');
        }

        $id = $this->getRequest()->getParam('id');

        // Base prepare
        // ---------------------------------------
        $data = array();
        // ---------------------------------------

        // tab: general
        // ---------------------------------------
        $keys = array(
            'title',
            'web_login',
            'ftp_login',
            'ftp_inventory_access',
            'ftp_orders_access',
            'ftp_new_sku_access'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        // ---------------------------------------

        // tab: 3rd party listings
        // ---------------------------------------
        $keys = array(
            'related_store_id',
            'other_listings_synchronization',
            'other_listings_mapping_mode',
            'other_listings_move_mode'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        // ---------------------------------------

        // Mapping
        // ---------------------------------------
        $tempData = array();
        $keys = array(
            'mapping_general_id_mode',
            'mapping_general_id_priority',
            'mapping_general_id_attribute',

            'mapping_sku_mode',
            'mapping_sku_priority',
            'mapping_sku_attribute'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $tempData[$key] = $post[$key];
            }
        }

        $mappingSettings = array();

        $temp = Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_CUSTOM_ATTRIBUTE;
        if (isset($tempData['mapping_general_id_mode']) &&
            $tempData['mapping_general_id_mode'] == $temp) {
            $mappingSettings['general_id']['mode'] = (int)$tempData['mapping_general_id_mode'];
            $mappingSettings['general_id']['priority'] = (int)$tempData['mapping_general_id_priority'];
            $mappingSettings['general_id']['attribute'] = (string)$tempData['mapping_general_id_attribute'];
        }

        $temp1 = Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT;
        $temp2 = Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE;
        $temp3 = Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID;
        if (isset($tempData['mapping_sku_mode']) &&
            ($tempData['mapping_sku_mode'] == $temp1 ||
                $tempData['mapping_sku_mode'] == $temp2 ||
                $tempData['mapping_sku_mode'] == $temp3)) {
            $mappingSettings['sku']['mode'] = (int)$tempData['mapping_sku_mode'];
            $mappingSettings['sku']['priority'] = (int)$tempData['mapping_sku_priority'];

            $temp = Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE;
            if ($tempData['mapping_sku_mode'] == $temp) {
                $mappingSettings['sku']['attribute'] = (string)$tempData['mapping_sku_attribute'];
            }
        }

        $data['other_listings_mapping_settings'] = json_encode($mappingSettings);
        // ---------------------------------------

        // tab: orders
        // ---------------------------------------
        $data['magento_orders_settings'] = array();

        // m2e orders settings
        // ---------------------------------------
        $tempKey = 'listing';
        $tempSettings = !empty($post['magento_orders_settings'][$tempKey])
            ? $post['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'store_mode',
            'store_id'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }
        // ---------------------------------------

        // 3rd party orders settings
        // ---------------------------------------
        $tempKey = 'listing_other';
        $tempSettings = !empty($post['magento_orders_settings'][$tempKey])
            ? $post['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'product_mode',
            'product_tax_class_id',
            'store_id'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }
        // ---------------------------------------

        // order number settings
        // ---------------------------------------
        $tempKey = 'number';
        $tempSettings = !empty($post['magento_orders_settings'][$tempKey])
            ? $post['magento_orders_settings'][$tempKey] : array();

        $data['magento_orders_settings'][$tempKey]['source'] = $tempSettings['source'];

        $prefixKeys = array(
            'mode',
            'prefix',
        );
        $tempSettings = !empty($tempSettings['prefix']) ? $tempSettings['prefix'] : array();
        foreach ($prefixKeys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey]['prefix'][$key] = $tempSettings[$key];
            }
        }
        // ---------------------------------------

        // tax settings
        // ---------------------------------------
        $tempKey = 'tax';
        $tempSettings = !empty($post['magento_orders_settings'][$tempKey])
            ? $post['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }
        // ---------------------------------------

        // customer settings
        // ---------------------------------------
        $tempKey = 'customer';
        $tempSettings = !empty($post['magento_orders_settings'][$tempKey])
            ? $post['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'id',
            'website_id',
            'group_id',
//            'subscription_mode'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        $notificationsKeys = array(
//            'customer_created',
            'order_created',
            'invoice_created'
        );
        $tempSettings = !empty($tempSettings['notifications']) ? $tempSettings['notifications'] : array();
        foreach ($notificationsKeys as $key) {
            if (in_array($key, $tempSettings)) {
                $data['magento_orders_settings'][$tempKey]['notifications'][$key] = true;
            }
        }
        // ---------------------------------------

        // status mapping settings
        // ---------------------------------------
        $tempKey = 'status_mapping';
        $tempSettings = !empty($post['magento_orders_settings'][$tempKey])
            ? $post['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'new',
            'processing',
            'shipped'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }
        // ---------------------------------------

        // invoice/shipment settings
        // ---------------------------------------
        $temp = Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_INVOICE_MODE_YES;
        $data['magento_orders_settings']['invoice_mode'] = $temp;
        $temp = Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_SHIPMENT_MODE_YES;
        $data['magento_orders_settings']['shipment_mode'] = $temp;

        $temp = Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_CUSTOM;
        if (!empty($data['magento_orders_settings']['status_mapping']['mode']) &&
            $data['magento_orders_settings']['status_mapping']['mode'] == $temp) {

            $temp = Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_INVOICE_MODE_NO;
            if (!isset($post['magento_orders_settings']['invoice_mode'])) {
                $data['magento_orders_settings']['invoice_mode'] = $temp;
            }
            $temp = Ess_M2ePro_Model_Buy_Account::MAGENTO_ORDERS_SHIPMENT_MODE_NO;
            if (!isset($post['magento_orders_settings']['shipment_mode'])) {
                $data['magento_orders_settings']['shipment_mode'] = $temp;
            }
        }
        // ---------------------------------------

        // ---------------------------------------
        $data['magento_orders_settings'] = json_encode($data['magento_orders_settings']);
        // ---------------------------------------

        $isEdit = !is_null($id);

        // Add or update model
        // ---------------------------------------
        $model = Mage::helper('M2ePro/Component_Buy')->getModel('Account');
        is_null($id) && $model->setData($data);
        !is_null($id) && $model->loadInstance($id)->addData($data);
        $oldData = $model->getOrigData();
        $id = $model->save()->getId();

        // ---------------------------------------

        $model->getChildObject()->setSetting(
            'other_listings_move_settings', 'synch', $post['other_listings_move_synch']
        );
        $model->getChildObject()->save();

        // ---------------------------------------

        try {

            // Add or update server
            // ---------------------------------------

            /** @var $accountObj Ess_M2ePro_Model_Account */
            $accountObj = $model;
            if (!$accountObj->isLockedObject('server_synchronize')) {
                $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');

                if (!$isEdit) {

                    Mage::helper('M2ePro/Module_License')->setTrial(Ess_M2ePro_Helper_Component_Buy::NICK);

                    $params = array(
                        'title' => $post['title'],
                        'web_login' => $post['web_login'],
                        'web_password' => $post['web_password'],
                        'ftp_login' => $post['ftp_login'],
                        'ftp_password' => $post['ftp_password'],
                        'ftp_new_sku_access' => $post['ftp_new_sku_access'],
                        'ftp_inventory_access' => $post['ftp_inventory_access'],
                        'ftp_orders_access' => $post['ftp_orders_access']
                    );

                    $connectorObj = $dispatcherObject->getConnector('account', 'add' ,'entityRequester',
                                                                    $params, $id);
                    $dispatcherObject->process($connectorObj);

                } else {
                    $newData = array(
                        'title' => $post['title'],
                        'web_login' => $post['web_login'],
                        'ftp_login' => $post['ftp_login'],
                        'ftp_new_sku_access' => $post['ftp_new_sku_access'],
                        'ftp_inventory_access' => $post['ftp_inventory_access'],
                        'ftp_orders_access' => $post['ftp_orders_access']
                    );

                    if (!empty($post['web_password'])) {
                        $newData['web_password'] = $post['web_password'];
                    }

                    if (!empty($post['ftp_password'])) {
                        $newData['ftp_password'] = $post['ftp_password'];
                    }

                    $params = array_diff_assoc($newData, $oldData);

                    if (!empty($params)) {
                        $connectorObj = $dispatcherObject->getConnector('account', 'update' ,'entityRequester',
                                                                        $params, $id);
                        $dispatcherObject->process($connectorObj);
                    }
                }
            }

            // ---------------------------------------

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            // M2ePro_TRANSLATIONS
            // The Rakuten.com access obtaining is currently unavailable.<br/>Reason: %error_message%
            $error = 'The Rakuten.com access obtaining is currently unavailable.<br/>Reason: %error_message%';
            $error = Mage::helper('M2ePro')->__($error, Mage::helper('M2ePro')->__($exception->getMessage()));

            $this->_getSession()->addError($error);
            $model->deleteInstance();

            return $this->indexAction();
        }

        // ---------------------------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Account was successfully saved'));

        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $routerParams = array('id'=>$id);
        if ($wizardHelper->isActive('buy') &&
            $wizardHelper->getStep('buy') == 'account') {
            $routerParams['wizard'] = true;
        }

        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>$routerParams)));
    }

    public function deleteAction()
    {
        $this->_forward('delete','adminhtml_common_account');
    }

    //########################################

    public function checkAuthAction()
    {
        $mode = $this->getRequest()->getParam('mode');
        $login = $this->getRequest()->getParam('login');
        $password = $this->getRequest()->getParam('password');

        $result = array (
            'result' => false
        );

        if ($login && $password) {

            $mode == 'web' ? $commandName = 'webAccess' : $commandName = 'ftpAccess';

            $params = array(
                'login' => $login,
                'password' => $password,
            );

            try {

                $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
                $connectorObj = $dispatcherObject->getVirtualConnector('account','check',$commandName,
                                                                       $params,'status');

                $result['result'] = $dispatcherObject->process($connectorObj);

            } catch (Exception $exception) {
                $result['result'] = false;
                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }
        }

        return $this->getResponse()->setBody(json_encode($result));
    }

    //########################################
}