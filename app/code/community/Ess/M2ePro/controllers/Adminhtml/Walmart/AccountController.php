<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Account as Account;

class Ess_M2ePro_Adminhtml_Walmart_AccountController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
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
            ->addJs('M2ePro/AccountHandler.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Walmart/AccountHandler.js')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css');
        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "x/L4taAQ");
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Walmart::MENU_ROOT_NODE_NICK . '/configuration'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_walmart_configuration', '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Walmart_Configuration_Tabs::TAB_ID_ACCOUNT)
                )
            )->renderLayout();
    }

    public function accountGridAction()
    {
        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_walmart_account_grid')->toHtml();
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
        $account = Mage::helper('M2ePro/Component_Walmart')->getModel('Account')->load($id);

        if ($id && !$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        $marketplaces = Mage::helper('M2ePro/Component_Walmart')->getMarketplacesAvailableForApiCreation();
        if ($marketplaces->getSize() <= 0) {
            $message = 'You should select and update at least one Walmart marketplace.';
            $this->_getSession()->addError(Mage::helper('M2ePro')->__($message));
            return $this->indexAction();
        }

        if ($id) {
            Mage::helper('M2ePro/Data_Global')->setValue('license_message', $this->getLicenseMessage($account));
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $account);

        $this->_initAction()
             ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_walmart_account_edit_tabs'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_walmart_account_edit'))
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
            'marketplace_id',
            'consumer_id',
            'client_id',
            'client_secret',
            'old_private_key'
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
            'mapping_sku_mode',
            'mapping_sku_priority',
            'mapping_sku_attribute',

            'mapping_upc_mode',
            'mapping_upc_priority',
            'mapping_upc_attribute',

            'mapping_gtin_mode',
            'mapping_gtin_priority',
            'mapping_gtin_attribute',

            'mapping_wpid_mode',
            'mapping_wpid_priority',
            'mapping_wpid_attribute',

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

        $temp = array(
            Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT,
            Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE,
            Account::OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID,
        );

        if (isset($tempData['mapping_sku_mode']) && in_array($tempData['mapping_sku_mode'], $temp)) {
            $mappingSettings['sku']['mode']     = (int)$tempData['mapping_sku_mode'];
            $mappingSettings['sku']['priority'] = (int)$tempData['mapping_sku_priority'];

            if ($tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['sku']['attribute'] = (string)$tempData['mapping_sku_attribute'];
            }
        }

        if (isset($tempData['mapping_upc_mode']) &&
            $tempData['mapping_upc_mode'] == Account::OTHER_LISTINGS_MAPPING_UPC_MODE_CUSTOM_ATTRIBUTE
        ) {
            $mappingSettings['upc']['mode']     = (int)$tempData['mapping_upc_mode'];
            $mappingSettings['upc']['priority'] = (int)$tempData['mapping_upc_priority'];

            $temp = Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_MAPPING_UPC_MODE_CUSTOM_ATTRIBUTE;
            if ($tempData['mapping_upc_mode'] == $temp) {
                $mappingSettings['upc']['attribute'] = (string)$tempData['mapping_upc_attribute'];
            }
        }

        if (isset($tempData['mapping_gtin_mode']) &&
            $tempData['mapping_gtin_mode'] == Account::OTHER_LISTINGS_MAPPING_GTIN_MODE_CUSTOM_ATTRIBUTE
        ) {
            $mappingSettings['gtin']['mode']     = (int)$tempData['mapping_gtin_mode'];
            $mappingSettings['gtin']['priority'] = (int)$tempData['mapping_gtin_priority'];

            if ($tempData['mapping_gtin_mode'] == Account::OTHER_LISTINGS_MAPPING_GTIN_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['gtin']['attribute'] = (string)$tempData['mapping_gtin_attribute'];
            }
        }

        if (isset($tempData['mapping_wpid_mode']) &&
            $tempData['mapping_wpid_mode'] == Account::OTHER_LISTINGS_MAPPING_WPID_MODE_CUSTOM_ATTRIBUTE
        ) {
            $mappingSettings['wpid']['mode']     = (int)$tempData['mapping_wpid_mode'];
            $mappingSettings['wpid']['priority'] = (int)$tempData['mapping_wpid_priority'];

            if ($tempData['mapping_wpid_mode'] == Account::OTHER_LISTINGS_MAPPING_WPID_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['wpid']['attribute'] = (string)$tempData['mapping_wpid_attribute'];
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

        $temp = Ess_M2ePro_Model_Walmart_Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_CUSTOM;
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

        $isEdit = $id !== null;

        // tab: vat calculation service
        // ---------------------------------------
        $keys = array(
            'is_vat_calculation_service_enabled',
            'is_magento_invoice_creation_disabled',
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        if (empty($data['is_vat_calculation_service_enabled'])) {
            $data['is_magento_invoice_creation_disabled'] = false;
        }

        // ---------------------------------------

        // Add or update model
        // ---------------------------------------
        $model = Mage::helper('M2ePro/Component_Walmart')->getModel('Account');
        $id === null && $model->setData($data);
        $id !== null && $model->loadInstance($id)->addData($data);
        $oldData = $model->getOrigData();
        $id = $model->save()->getId();
        // ---------------------------------------

        $model->getChildObject()->save();

        try {
            // Add or update server
            // ---------------------------------------

            /** @var $accountObj Ess_M2ePro_Model_Account */
            $accountObj = $model;

            if (!$accountObj->isSetProcessingLock('server_synchronize')) {

                /** @var $dispatcherObject Ess_M2ePro_Model_Walmart_Connector_Dispatcher */
                $dispatcherObject = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

                if ($post['marketplace_id'] == Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA) {
                    $requestData = array(
                        'title'            => $post['title'],
                        'marketplace_id'   => (int)$post['marketplace_id'],
                        'related_store_id' => (int)$post['related_store_id'],
                        'consumer_id'      => $post['consumer_id'],
                        'private_key'      => $post['old_private_key']
                    );
                } else {
                    $requestData = array(
                        'title'            => $post['title'],
                        'marketplace_id'   => (int)$post['marketplace_id'],
                        'related_store_id' => (int)$post['related_store_id'],
                        'consumer_id'      => $post['consumer_id'],
                        'client_id'        => $post['client_id'],
                        'client_secret'    => $post['client_secret'],
                    );
                }

                if (!$isEdit) {
                    $connectorObj = $dispatcherObject->getConnector(
                        'account', 'add', 'entityRequester', $requestData, $id
                    );
                    $dispatcherObject->process($connectorObj);
                } else {
                    $requestData = array_diff_assoc($requestData, $oldData);

                    if (!empty($requestData)) {
                        $connectorObj = $dispatcherObject->getConnector(
                            'account', 'update', 'entityRequester', $requestData, $id
                        );
                        $dispatcherObject->process($connectorObj);
                    }
                }
            }

            // ---------------------------------------
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $error = 'The Walmart access obtaining is currently unavailable.<br/>Reason: %error_message%';
            $error = Mage::helper('M2ePro')->__($error, $exception->getMessage());

            $this->_getSession()->addError($error);

            if (!$isEdit) {
                $model->deleteInstance();
            }

            return $this->indexAction();
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Account was successfully saved'));

        /** @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $routerParams = array('id' => $id);
        if ($wizardHelper->isActive('installationWalmart') &&
            $wizardHelper->getStep('installationWalmart') == 'account') {
            $routerParams['wizard'] = true;
        }

        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list', array(), array('edit' => $routerParams)));
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
                $dispatcherObject = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

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

        $tempString = Mage::helper('M2ePro')->__('%amount% record(s) were successfully deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__('%amount% record(s) are used in M2E Pro Listing(s).', $locked) . ' ';
        $tempString .= Mage::helper('M2ePro')->__('Account must not be in use to be deleted.');
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/*/index');
    }

    //########################################

    public function checkAuthAction()
    {
        $consumerId    = $this->getRequest()->getParam('consumer_id', false);
        $oldPrivateKey = $this->getRequest()->getParam('old_private_key', false);
        $clientId      = $this->getRequest()->getParam('client_id', false);
        $clientSecret  = $this->getRequest()->getParam('client_secret', false);
        $marketplaceId = $this->getRequest()->getParam('marketplace_id', false);
        $result        = array('result' => null);

        /** @var $marketplaceObject Ess_M2ePro_Model_Marketplace */
        $marketplaceObject = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Marketplace', $marketplaceId
        );

        if ($marketplaceId == Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA &&
            $consumerId && $oldPrivateKey) {
            $requestData = array(
                'marketplace' => $marketplaceObject->getNativeId(),
                'consumer_id' => $consumerId,
                'private_key' => $oldPrivateKey,
            );
        } elseif ($marketplaceId != Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA &&
                  $clientId && $clientSecret) {
            $requestData = array(
                'marketplace'   => $marketplaceObject->getNativeId(),
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'consumer_id'   => $consumerId
            );
        } else {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
        }

        try {
            $dispatcherObject = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('account', 'check', 'access', $requestData);
            $dispatcherObject->process($connectorObj);

            $response = $connectorObj->getResponseData();

            $result['result'] = isset($response['status']) ? $response['status']
                                                           : null;
            if (!empty($response['reason'])) {
                $result['reason'] = Mage::helper('M2ePro')->escapeJs($response['reason']);
            }
        } catch (Exception $exception) {
            $result['result'] = false;
            Mage::helper('M2ePro/Module_Exception')->process($exception);
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
}
