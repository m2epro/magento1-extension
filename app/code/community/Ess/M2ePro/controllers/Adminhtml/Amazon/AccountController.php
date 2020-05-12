<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Account as Account;

class Ess_M2ePro_Adminhtml_Amazon_AccountController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Accounts'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addJs('M2ePro/Plugin/ActionColumn.js')
            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/AccountHandler.js')
            ->addJs('M2ePro/AccountGridHandler.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Amazon/AccountHandler.js')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css');
        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "x/mIIVAQ");
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Amazon::MENU_ROOT_NODE_NICK . '/configuration'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_amazon_configuration', '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Amazon_Configuration_Tabs::TAB_ID_ACCOUNT)
                )
            )->renderLayout();
    }

    public function accountGridAction()
    {
        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_amazon_account_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($id);

        if ($id && !$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        $marketplaces = Mage::helper('M2ePro/Component_Amazon')->getMarketplacesAvailableForApiCreation();
        if ($marketplaces->getSize() <= 0) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('You should select and update at least one Amazon marketplace.')
            );

            return $this->indexAction();
        }

        if ($id) {
            Mage::helper('M2ePro/Data_Global')->setValue('license_message', $this->getLicenseMessage($account));
        }

        Mage::helper('M2ePro/Data_Global')->setValue('model_account', $account);

        $this->_initAction()
             ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_account_edit_tabs'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_account_edit'))
             ->renderLayout();
    }

    //########################################

    public function beforeGetTokenAction()
    {
        // Get and save form data
        // ---------------------------------------
        $accountId = $this->getRequest()->getParam('id', 0);
        $accountTitle = $this->getRequest()->getParam('title', '');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id', 0);
        // ---------------------------------------

        $marketplace = Mage::getModel('M2ePro/Marketplace')->load($marketplaceId);

        try {
            $backUrl = $this->getUrl('*/*/afterGetToken', array('_current' => true));

            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'account', 'get', 'authUrl',
                array('back_url' => $backUrl, 'marketplace' => $marketplace->getData('native_id'))
            );

            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'The Amazon token obtaining is currently unavailable.<br/>Reason: %error_message%',
                    $exception->getMessage()
                )
            );

            return $this->indexAction();
        }

        Mage::helper('M2ePro/Data_Session')->setValue('account_id', $accountId);
        Mage::helper('M2ePro/Data_Session')->setValue('account_title', $accountTitle);
        Mage::helper('M2ePro/Data_Session')->setValue('marketplace', $marketplace);

        $this->_redirectUrl($response['url']);
    }

    public function afterGetTokenAction()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params)) {
            return $this->indexAction();
        }

        $requiredFields = array(
            'Merchant',
            'Marketplace',
            'MWSAuthToken',
            'Signature',
            'SignedString'
        );

        foreach ($requiredFields as $requiredField) {
            if (!isset($params[$requiredField])) {
                $error = Mage::helper('M2ePro')->__('The Amazon token obtaining is currently unavailable.');
                $this->_getSession()->addError($error);

                return $this->indexAction();
            }
        }

        // Goto account add or edit page
        // ---------------------------------------
        $accountId = (int)Mage::helper('M2ePro/Data_Session')->getValue('account_id', true);

        Mage::helper('M2ePro/Data_Session')->setValue('merchant_id', $params['Merchant']);
        Mage::helper('M2ePro/Data_Session')->setValue('mws_token', $params['MWSAuthToken']);

        $urlParams = array('wizard' => (bool)$this->getRequest()->getParam('wizard', false));
        if ($accountId == 0) {
            return $this->_redirect('*/*/new', $urlParams);
        }

        try {
            $data = array(
                'merchant_id' => $params['Merchant'],
                'token'       => $params['MWSAuthToken']
            );

            $model = $this->updateAccount($accountId, $data);
            $this->sendDataToServer($model);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'The Amazon access obtaining is currently unavailable.<br/>Reason: %error_message%',
                    $exception->getMessage()
                )
            );

            return $this->indexAction();
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Token was successfully saved'));
        $urlParams['id'] = $accountId;
        $this->_redirect('*/*/edit', $urlParams);
    }

    //########################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/*/index');
        }

        // Base prepare
        // ---------------------------------------
        $data = array();
        // ---------------------------------------

        // tab: general
        // ---------------------------------------
        $keys = array(
            'title',
            'marketplace_id',
            'merchant_id',
            'token',
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
            'other_listings_mapping_mode'
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

        if (isset($tempData['mapping_general_id_mode']) &&
            $tempData['mapping_general_id_mode'] == Account::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_CUSTOM_ATTRIBUTE
        ) {
            $mappingSettings['general_id']['mode'] = (int)$tempData['mapping_general_id_mode'];
            $mappingSettings['general_id']['priority'] = (int)$tempData['mapping_general_id_priority'];
            $mappingSettings['general_id']['attribute'] = (string)$tempData['mapping_general_id_attribute'];
        }

        if (isset($tempData['mapping_sku_mode']) &&
            ($tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT ||
            $tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE ||
            $tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID)
        ) {
            $mappingSettings['sku']['mode'] = (int)$tempData['mapping_sku_mode'];
            $mappingSettings['sku']['priority'] = (int)$tempData['mapping_sku_priority'];

            if ($tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['sku']['attribute'] = (string)$tempData['mapping_sku_attribute'];
            }
        }

        if (isset($tempData['mapping_title_mode']) &&
            ($tempData['mapping_title_mode'] == Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_DEFAULT ||
            $tempData['mapping_title_mode'] == Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE)
        ) {
            $mappingSettings['title']['mode'] = (int)$tempData['mapping_title_mode'];
            $mappingSettings['title']['priority'] = (int)$tempData['mapping_title_priority'];
            $mappingSettings['title']['attribute'] = (string)$tempData['mapping_title_attribute'];
        }

        $data['other_listings_mapping_settings'] = Mage::helper('M2ePro')->jsonEncode($mappingSettings);
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
        $data['magento_orders_settings'][$tempKey]['apply_to_amazon'] = $tempSettings['apply_to_amazon'];

        $prefixKeys = array(
            'prefix',
            'afn-prefix',
            'prime-prefix',
            'b2b-prefix',
        );
        $tempSettings = !empty($tempSettings['prefix']) ? $tempSettings['prefix'] : array();
        foreach ($prefixKeys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey]['prefix'][$key] = $tempSettings[$key];
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

        // refund & cancellation
        // ---------------------------------------
        $tempKey = 'refund_and_cancellation';
        $tempSettings = !empty($post['magento_orders_settings'][$tempKey])
            ? $post['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'refund_mode',
        );
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // ---------------------------------------

        // fba
        // ---------------------------------------
        $tempKey = 'fba';
        $tempSettings = !empty($post['magento_orders_settings'][$tempKey])
            ? $post['magento_orders_settings'][$tempKey] : array();

        $keys = array(
            'mode',
            'stock_mode'
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
            'billing_address_mode',
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
        $data['magento_orders_settings']['invoice_mode'] = 1;
        $data['magento_orders_settings']['shipment_mode'] = 1;

        $temp = Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_CUSTOM;
        if (!empty($data['magento_orders_settings']['status_mapping']['mode']) &&
            $data['magento_orders_settings']['status_mapping']['mode'] == $temp) {
            if (!isset($post['magento_orders_settings']['invoice_mode'])) {
                $data['magento_orders_settings']['invoice_mode'] = 0;
            }

            if (!isset($post['magento_orders_settings']['shipment_mode'])) {
                $data['magento_orders_settings']['shipment_mode'] = 0;
            }
        }

        // ---------------------------------------

        // ---------------------------------------
        $data['magento_orders_settings'] = Mage::helper('M2ePro')->jsonEncode($data['magento_orders_settings']);
        // ---------------------------------------

        // tab: vat calculation service
        // ---------------------------------------
        $keys = array(
            'auto_invoicing',
            'is_magento_invoice_creation_disabled',
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        if (empty($data['auto_invoicing'])) {
            $data['is_magento_invoice_creation_disabled'] = false;
        }

        // ---------------------------------------

        // Add or update model
        // ---------------------------------------
        $model = $this->updateAccount($this->getRequest()->getParam('id'), $data);

        // Repricing
        // ---------------------------------------
        if (!empty($post['repricing']) && $model->getChildObject()->isRepricing()) {

            /** @var Ess_M2ePro_Model_Amazon_Account_Repricing $repricingModel */
            $repricingModel = $model->getChildObject()->getRepricing();

            $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Account_Repricing_SnapshotBuilder');
            $snapshotBuilder->setModel($repricingModel);

            $repricingOldData = $snapshotBuilder->getSnapshot();

            $repricingModel->addData($post['repricing']);
            $repricingModel->save();

            $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Account_Repricing_SnapshotBuilder');
            $snapshotBuilder->setModel($repricingModel);

            $repricingNewData = $snapshotBuilder->getSnapshot();

            $diff = Mage::getModel('M2ePro/Amazon_Account_Repricing_Diff');
            $diff->setOldSnapshot($repricingOldData);
            $diff->setNewSnapshot($repricingNewData);

            $affectedListingsProducts = Mage::getModel('M2ePro/Amazon_Account_Repricing_AffectedListingsProducts');
            $affectedListingsProducts->setModel($repricingModel);

            $changeProcessor = Mage::getModel('M2ePro/Amazon_Account_Repricing_ChangeProcessor');
            $changeProcessor->process($diff, $affectedListingsProducts->getData(array('id', 'status')));
        }

        // ---------------------------------------

        try {
            // Add or update server
            // ---------------------------------------
            $this->sendDataToServer($model);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'The Amazon access obtaining is currently unavailable.<br/>Reason: %error_message%',
                    $exception->getMessage()
                )
            );

            if (!$model->getData('isEdit')) {
                $model->deleteInstance();
            }

            return $this->indexAction();
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Account was successfully saved'));

        /** @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $routerParams = array('id' => $model->getId());
        if ($wizardHelper->isActive('installationAmazon') &&
            $wizardHelper->getStep('installationAmazon') == 'account') {
            $routerParams['wizard'] = true;
        }

        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list', array(), array('edit'=>$routerParams)));
    }

    //########################################

    public function deleteAction()
    {
        $ids = $this->getRequestIds();

        if (empty($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select account(s) to remove.'));
            $this->_redirect('*/*/index');
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Account_Collection $accountCollection */
        $accountCollection = Mage::getModel('M2ePro/Account')->getCollection();
        $accountCollection->addFieldToFilter('id', array('in' => $ids));

        $accounts = $accountCollection->getItems();

        if (empty($accounts)) {
            $this->_redirect('*/*/index');
            return;
        }

        $deleted = $locked = 0;
        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account */

            if ($account->isLocked(true)) {
                $locked++;
                continue;
            }

            try {
                $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');

                $connectorObj = $dispatcherObject->getConnector(
                    'account', 'delete', 'entityRequester',
                    array(), $account
                );
                $dispatcherObject->process($connectorObj);
            } catch (Exception $e) {
                $account->deleteProcessings();
                $account->deleteProcessingLocks();
                $account->deleteInstance();

                throw $e;
            }

            $account->deleteProcessings();
            $account->deleteProcessingLocks();
            $account->deleteInstance();

            $deleted++;
        }

        $deleted && $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('%amount% record(s) were successfully deleted.', $deleted)
        );
        $locked && $this->_getSession()->addError(
            Mage::helper('M2ePro')->__(
                '%amount% record(s) are used in M2E Pro Listing(s). Account must not be in use to be deleted.', $locked
            )
        );

        $this->_redirect('*/*/index');
    }

    //########################################

    public function checkAuthAction()
    {
        $merchantId    = $this->getRequest()->getParam('merchant_id');
        $token         = $this->getRequest()->getParam('token');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        $result = array (
            'result' => false,
            'reason' => null
        );

        if ($merchantId && $token && $marketplaceId) {
            $marketplaceNativeId = Mage::helper('M2ePro/Component_Amazon')
                ->getCachedObject('Marketplace', $marketplaceId)
                ->getNativeId();

            $params = array(
                'marketplace' => $marketplaceNativeId,
                'merchant_id' => $merchantId,
                'token'       => $token,
            );

            try {
                $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
                $connectorObj = $dispatcherObject->getVirtualConnector('account', 'check', 'access', $params);
                $dispatcherObject->process($connectorObj);

                $response = $connectorObj->getResponseData();

                $result['result'] = isset($response['status']) ? $response['status'] : null;
                if (isset($response['reason'])) {
                    $result['reason'] = Mage::helper('M2ePro')->escapeJs($response['reason']);
                }
            } catch (Exception $exception) {
                $result['result'] = false;
                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
    }

    //########################################

    protected function getLicenseMessage(Ess_M2ePro_Model_Account $account)
    {
        try {
            $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'account', 'get', 'info', array(
                    'account' => $account->getChildObject()->getServerHash()
                )
            );

            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (Exception $e) {
            return '';
        }

        if (!isset($response['info']['status']) || empty($response['info']['note'])) {
            return '';
        }

        $status = (bool)$response['info']['status'];
        $note   = $response['info']['note'];

        if ($status) {
            return 'MagentoMessageObj.addNotice(\''.$note.'\');';
        }

        $errorMessage = Mage::helper('M2ePro')->__(
            'Work with this Account is currently unavailable for the following reason: <br/> %error_message%',
            array('error_message' => $note)
        );

        return 'MagentoMessageObj.addError(\''.$errorMessage.'\');';
    }

    //########################################

    protected function updateAccount($id, $data)
    {
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account');

        if (isset($id)) {
            $model->loadInstance($id);
            $model->addData($data);
            $model->setData('isEdit', true);
        } else {
            $model->setData($data);
            $model->setData('isEdit', false);
        }

        $model->save();
        $model->getChildObject()->save();

        return $model;
    }

    protected function sendDataToServer($model)
    {
        /** @var $accountObj Ess_M2ePro_Model_Account */
        $accountObj = $model;

        if (!$accountObj->isSetProcessingLock('server_synchronize')) {
            /** @var $dispatcherObject Ess_M2ePro_Model_Amazon_Connector_Dispatcher */
            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');

            $data = array(
                'title'            => $model->getTitle(),
                'marketplace_id'   => $model->getMarketplaceId(),
                'merchant_id'      => $model->getMerchantId(),
                'token'            => $model->getToken(),
                'related_store_id' => $model->getRelatedStoreId()
            );

            if (!$model->getData('isEdit')) {
                $connectorObj = $dispatcherObject->getConnector(
                    'account', 'add', 'entityRequester',
                    $data,
                    $model->getId()
                );
                $dispatcherObject->process($connectorObj);
            } else {
                $params = array_diff_assoc($data, $model->getOrigData());

                if (!empty($params)) {
                    $connectorObj = $dispatcherObject->getConnector(
                        'account', 'update', 'entityRequester',
                        $params,
                        $model->getId()
                    );
                    $dispatcherObject->process($connectorObj);
                }
            }
        }
    }

    //########################################
}
