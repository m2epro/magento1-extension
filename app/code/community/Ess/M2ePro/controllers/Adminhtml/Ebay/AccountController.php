<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_AccountController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Accounts'));

        $this->getLayout()->getBlock('head')
             ->setCanLoadExtJs(true)
             ->addJs('M2ePro/Ebay/AccountHandler.js');

        $this->_initPopUp();

        $this->setComponentPageHelpLink('Accounts');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/configuration');
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_ebay_configuration', '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_ACCOUNT)
                )
            )->renderLayout();
    }

    //########################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Account')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->_redirect('*/adminhtml_ebay_account/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        $this->_initAction()
             ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_account_edit'))
             ->renderLayout();
    }

    //########################################

    public function beforeGetTokenAction()
    {
        // Get and save form data
        // ---------------------------------------
        $accountId = $this->getRequest()->getParam('id', 0);
        $accountTitle = $this->getRequest()->getParam('title', '');
        $accountMode = (int)$this->getRequest()->getParam('mode', Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX);
        // ---------------------------------------

        // Get and save session id
        // ---------------------------------------
        $mode = $accountMode == Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION ?
                                Ess_M2ePro_Model_Connector_Ebay_Abstract::MODE_PRODUCTION :
                                Ess_M2ePro_Model_Connector_Ebay_Abstract::MODE_SANDBOX;

        try {

            $backUrl = $this->getUrl('*/*/afterGetToken', array('_current' => true));

            $dispatcherObject = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('account','get','authUrl',
                                                                   array('back_url' => $backUrl),
                                                                   NULL,NULL,NULL,$mode);

            $response = $dispatcherObject->process($connectorObj);

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);
            // M2ePro_TRANSLATIONS
            // The eBay token obtaining is currently unavailable.<br/>Reason: %error_message%
            $error = 'The eBay token obtaining is currently unavailable.<br/>Reason: %error_message%';
            $error = Mage::helper('M2ePro')->__($error, $exception->getMessage());

            $this->_getSession()->addError($error);

            return $this->indexAction();
        }

        Mage::helper('M2ePro/Data_Session')->setValue('get_token_account_id', $accountId);
        Mage::helper('M2ePro/Data_Session')->setValue('get_token_account_title', $accountTitle);
        Mage::helper('M2ePro/Data_Session')->setValue('get_token_account_mode', $accountMode);
        Mage::helper('M2ePro/Data_Session')->setValue('get_token_session_id', $response['session_id']);

        $this->_redirectUrl($response['url']);
        // ---------------------------------------
    }

    public function afterGetTokenAction()
    {
        // Get eBay session id
        // ---------------------------------------
        $sessionId = Mage::helper('M2ePro/Data_Session')->getValue('get_token_session_id', true);
        is_null($sessionId) && $this->_redirect('*/*/index');
        // ---------------------------------------

        // Get account form data
        // ---------------------------------------
        Mage::helper('M2ePro/Data_Session')->setValue('get_token_account_token_session', $sessionId);
        // ---------------------------------------

        // Goto account add or edit page
        // ---------------------------------------
        $accountId = (int)Mage::helper('M2ePro/Data_Session')->getValue('get_token_account_id', true);

        if ($accountId == 0) {
            $this->_redirect('*/*/new',array('_current' => true));
        } else {
            $data = array();
            $data['mode'] = Mage::helper('M2ePro/Data_Session')->getValue('get_token_account_mode');
            $data['token_session'] = $sessionId;

            $data = $this->sendDataToServer($accountId, $data);
            $id = $this->updateAccount($accountId, $data);

            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Token was successfully saved'));
            $this->_redirect('*/*/edit', array('id' => $id, '_current' => true));
        }
        // ---------------------------------------
    }

    //########################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->indexAction();
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
            'mode',
            'token_session',
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        // ---------------------------------------

        // tab: 3rd party
        // ---------------------------------------
        $keys = array(
            'other_listings_synchronization',
            'other_listings_mapping_mode'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $marketplacesIds = Mage::getModel('M2ePro/Marketplace')->getCollection()
            ->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
            ->getColumnValues('id');

        $marketplacesData = array();
        foreach ($marketplacesIds as $marketplaceId) {
            $marketplacesData[$marketplaceId]['related_store_id'] = isset($post['related_store_id_' . $marketplaceId])
                ? (int)$post['related_store_id_' . $marketplaceId]
                : Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        $data['marketplaces_data'] = json_encode($marketplacesData);
        // ---------------------------------------

        // Mapping
        // ---------------------------------------
        $tempData = array();
        $keys = array(
            'mapping_sku_mode',
            'mapping_sku_priority',
            'mapping_sku_attribute',

            'mapping_title_mode',
            'mapping_title_priority',
            'mapping_title_attribute'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $tempData[$key] = $post[$key];
            }
        }

        $mappingSettings = array();

        $temp1 = Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT;
        $temp2 = Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE;
        $temp3 = Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID;
        if (isset($tempData['mapping_sku_mode']) &&
            ($tempData['mapping_sku_mode'] == $temp1 ||
                $tempData['mapping_sku_mode'] == $temp2 ||
                $tempData['mapping_sku_mode'] == $temp3)) {
            $mappingSettings['sku']['mode'] = (int)$tempData['mapping_sku_mode'];
            $mappingSettings['sku']['priority'] = (int)$tempData['mapping_sku_priority'];

            $temp = Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE;
            if ($tempData['mapping_sku_mode'] == $temp) {
                $mappingSettings['sku']['attribute'] = (string)$tempData['mapping_sku_attribute'];
            }
        }

        $temp1 = Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_DEFAULT;
        $temp2 = Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE;
        if (isset($tempData['mapping_title_mode']) &&
            ($tempData['mapping_title_mode'] == $temp1 ||
                $tempData['mapping_title_mode'] == $temp2)) {
            $mappingSettings['title']['mode'] = (int)$tempData['mapping_title_mode'];
            $mappingSettings['title']['priority'] = (int)$tempData['mapping_title_priority'];
            $mappingSettings['title']['attribute'] = (string)$tempData['mapping_title_attribute'];
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

        if (!empty($tempSettings['source'])) {
            $data['magento_orders_settings'][$tempKey]['source'] = $tempSettings['source'];
        }

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

        // creation settings
        // ---------------------------------------
        $tempKey = 'creation';
        $tempSettings = !empty($post['magento_orders_settings'][$tempKey])
            ? $post['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'reservation_days'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
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
            'paid',
            'shipped'
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }
        // ---------------------------------------

        // qty reservation
        // ---------------------------------------
        $tempKey = 'qty_reservation';
        $tempSettings = !empty($post['magento_orders_settings'][$tempKey])
            ? $post['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'days',
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }
        // ---------------------------------------

        // invoice/shipment settings
        // ---------------------------------------
        $temp = Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_INVOICE_MODE_YES;
        $data['magento_orders_settings']['invoice_mode'] = $temp;
        $temp = Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_SHIPMENT_MODE_YES;
        $data['magento_orders_settings']['shipment_mode'] = $temp;

        $temp = Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_CUSTOM;
        if (!empty($data['magento_orders_settings']['status_mapping']['mode']) &&
            $data['magento_orders_settings']['status_mapping']['mode'] == $temp) {

            if (!isset($post['magento_orders_settings']['invoice_mode'])) {
                $temp = Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_INVOICE_MODE_NO;
                $data['magento_orders_settings']['invoice_mode'] = $temp;
            }
            if (!isset($post['magento_orders_settings']['shipment_mode'])) {
                $temp = Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_SHIPMENT_MODE_NO;
                $data['magento_orders_settings']['shipment_mode'] = $temp;
            }
        }
        // ---------------------------------------

        // ---------------------------------------
        $data['magento_orders_settings'] = json_encode($data['magento_orders_settings']);
        // ---------------------------------------

        // tab: feedbacks
        // ---------------------------------------
        $keys = array(
            'feedbacks_receive',
            'feedbacks_auto_response',
            'feedbacks_auto_response_only_positive'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        // ---------------------------------------

        $data = $this->sendDataToServer($id, $data);

        $id = $this->updateAccount($id, $data);

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Account was successfully saved'));

        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl(
            'list',array(),array('edit'=>array('id'=>$id, 'update_ebay_store' => null, '_current'=>true))
        ));
    }

    public function deleteAction()
    {
        $ids = $this->getRequestIds();

        if (count($ids) == 0) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Account(s) to remove.'));
            $this->_redirect('*/*/index');
            return;
        }

        $deleted = $locked = 0;
        foreach ($ids as $id) {

            /** @var $account Ess_M2ePro_Model_Account */
            $account = Mage::getModel('M2ePro/Account')->loadInstance($id);

            if ($account->isLocked(true)) {
                $locked++;
                continue;
            }

            try {

                $dispatcherObject = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
                $connectorObj = $dispatcherObject->getVirtualConnector('account','delete','entity',
                                                                       array(),NULL,NULL,$account->getId());
                $dispatcherObject->process($connectorObj);

            } catch (Exception $e) {

                $account->deleteProcessingRequests();
                $account->deleteObjectLocks();
                $account->deleteInstance();

                throw $e;
            }

            $account->deleteProcessingRequests();
            $account->deleteObjectLocks();
            $account->deleteInstance();

            $deleted++;
        }

        $tempString = Mage::helper('M2ePro')->__('%amount% record(s) were successfully deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__('%amount% record(s) are used in M2E Pro Listing(s).', $locked) . ' ';
        $tempString .= Mage::helper('M2ePro')->__('Account must not be in use to be deleted.');
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/*/index');
    }

    // ---------------------------------------

    private function sendDataToServer($id, $data)
    {
        // Add or update server
        // ---------------------------------------
        $requestMode = $data['mode'] == Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION ?
            Ess_M2ePro_Model_Connector_Ebay_Abstract::MODE_PRODUCTION :
            Ess_M2ePro_Model_Connector_Ebay_Abstract::MODE_SANDBOX;

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');

        if ((bool)$id) {

            /** @var Ess_M2ePro_Model_Account $model */
            $model = Mage::helper('M2ePro/Component_Ebay')->getObject('Account',$id);

            $connectorObj = $dispatcherObject->getVirtualConnector('account','update','entity',
                                                                   array('title'         => $model->getTitle(),
                                                                         'mode'          => $requestMode,
                                                                         'token_session' => $data['token_session']),
                                                                   NULL,NULL,$id);
        } else {

            Mage::helper('M2ePro/Module_License')->setTrial(Ess_M2ePro_Helper_Component_Ebay::NICK);

            $connectorObj = $dispatcherObject->getVirtualConnector('account','add','entity',
                                                                   array('mode' => $requestMode,
                                                                         'token_session' => $data['token_session']),
                                                                   NULL,NULL,NULL,$requestMode);
        }

        $response = $dispatcherObject->process($connectorObj);

        if (!isset($response['token_expired_date'])) {
            throw new Ess_M2ePro_Model_Exception('Account is not added or updated. Try again later.');
        }

        isset($response['hash']) && $data['server_hash'] = $response['hash'];
        isset($response['info']['UserID']) && $data['user_id'] = $response['info']['UserID'];

        $data['info'] = json_encode($response['info']);
        $data['token_expired_date'] = $response['token_expired_date'];
        // ---------------------------------------

        return $data;
    }

    private function updateAccount($id, $data)
    {
        // Change token
        // ---------------------------------------
        $isChangeTokenSession = false;
        if ((bool)$id) {
            $oldTokenSession = Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Account',$id)
                ->getChildObject()
                ->getTokenSession();
            $newTokenSession = $data['token_session'];
            if ($newTokenSession != $oldTokenSession) {
                $isChangeTokenSession = true;
            }
        } else {
            $isChangeTokenSession = true;
        }
        // ---------------------------------------

        // Add or update model
        // ---------------------------------------
        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Account');
        if (is_null($id)) {
            $model->setData($data);

        } else {
            $model->load($id);
            $model->addData($data);
        }
        // ---------------------------------------

        $id = $model->save()->getId();

        // Update eBay store
        // ---------------------------------------
        if ($isChangeTokenSession || (int)$this->getRequest()->getParam('update_ebay_store')) {
            $ebayAccount = $model->getChildObject();
            $ebayAccount->updateEbayStoreInfo();

            if (Mage::helper('M2ePro/Component_Ebay_Category_Store')->isExistDeletedCategories()) {

                $url = $this->getUrl('*/adminhtml_ebay_category/index', array('filter' => base64_encode('state=0')));

        // M2ePro_TRANSLATIONS
        // Some eBay Store Categories were deleted from eBay. Click <a target="_blank" href="%url%">here</a> to check.
                $this->_getSession()->addWarning(
                    Mage::helper('M2ePro')->__(
                        'Some eBay Store Categories were deleted from eBay. Click '.
                        '<a target="_blank" href="%url%">here</a> to check.', $url
                    )
                );
            }
        }
        // ---------------------------------------

        return $id;
    }

    //########################################

    public function accountGridAction()
    {
        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_account_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function feedbackTemplateGridAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Account')->load($id);

        if (!$model->getId() && $id) {
            return;
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        // Response for grid
        // ---------------------------------------
        $response = $this->loadLayout()->getLayout()
                         ->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs_feedback_grid')->toHtml();
        $this->getResponse()->setBody($response);
        // ---------------------------------------
    }

    public function feedbackTemplateCheckAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account',$id);

        return $this->getResponse()->setBody(json_encode(array(
            'ok' => (bool)$model->getChildObject()->hasFeedbackTemplate()
        )));
    }

    public function feedbackTemplateEditAction()
    {
        $id = $this->getRequest()->getParam('id');
        $accountId = $this->getRequest()->getParam('account_id');
        $body = $this->getRequest()->getParam('body');

        $data = array('account_id'=>$accountId,'body'=>$body);

        $model = Mage::getModel('M2ePro/Ebay_Feedback_Template');
        is_null($id) && $model->setData($data);
        !is_null($id) && $model->load($id)->addData($data);
        $model->save();

        return $this->getResponse()->setBody('ok');
    }

    public function feedbackTemplateDeleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        Mage::getModel('M2ePro/Ebay_Feedback_Template')->loadInstance($id)->deleteInstance();
        return $this->getResponse()->setBody('ok');
    }

    //########################################
}
