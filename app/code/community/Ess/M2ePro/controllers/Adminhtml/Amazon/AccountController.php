<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

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
            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/Account.js')
            ->addJs('M2ePro/AccountGrid.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Amazon/Account.js')
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

        $id = (int)Mage::helper('M2ePro/Data_Session')->getValue('account_id', true);

        Mage::helper('M2ePro/Data_Session')->setValue('merchant_id', $params['Merchant']);
        Mage::helper('M2ePro/Data_Session')->setValue('mws_token', $params['MWSAuthToken']);

        if ((int)$id <= 0) {
            return $this->_redirect(
                '*/*/new',
                array('wizard' => (bool)$this->getRequest()->getParam('wizard', false))
            );
        }

        try {
            $this->updateAccount(
                $id,
                array(
                    'merchant_id' => $params['Merchant'],
                    'token'       => $params['MWSAuthToken']
                )
            );
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

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Token was saved'));

        return $this->_redirect(
            '*/*/edit',
            array(
                'id' => $id,
                'wizard' => (bool)$this->getRequest()->getParam('wizard', false)
            )
        );
    }

    //########################################

    public function saveAction()
    {
        if (!$data = $this->getRequest()->getPost()) {
            $this->_redirect('*/*/index');
        }

        $id = $this->getRequest()->getParam('id');

        try {
            $account = $id ? $this->updateAccount($id, $data) : $this->addAccount($data);
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

        // Repricing
        // ---------------------------------------
        if (!empty($data['repricing']) && $account->getChildObject()->isRepricing()) {
            /** @var Ess_M2ePro_Model_Amazon_Account_Repricing $repricingModel */
            $repricingModel = $account->getChildObject()->getRepricing();

            /** @var Ess_M2ePro_Model_Amazon_Account_Repricing_SnapshotBuilder $snapshotBuilder */
            $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Account_Repricing_SnapshotBuilder');
            $snapshotBuilder->setModel($repricingModel);

            $repricingOldData = $snapshotBuilder->getSnapshot();

            Mage::getModel('M2ePro/Amazon_Account_Repricing_Builder')->build($repricingModel, $data['repricing']);

            $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Account_Repricing_SnapshotBuilder');
            $snapshotBuilder->setModel($repricingModel);

            $repricingNewData = $snapshotBuilder->getSnapshot();

            /** @var Ess_M2ePro_Model_Amazon_Account_Repricing_Diff $diff */
            $diff = Mage::getModel('M2ePro/Amazon_Account_Repricing_Diff');
            $diff->setOldSnapshot($repricingOldData);
            $diff->setNewSnapshot($repricingNewData);

            /** @var Ess_M2ePro_Model_Amazon_Account_Repricing_AffectedListingsProducts $affectedListingsProducts */
            $affectedListingsProducts = Mage::getModel('M2ePro/Amazon_Account_Repricing_AffectedListingsProducts');
            $affectedListingsProducts->setModel($repricingModel);

            /** @var Ess_M2ePro_Model_Amazon_Account_Repricing_ChangeProcessor $changeProcessor */
            $changeProcessor = Mage::getModel('M2ePro/Amazon_Account_Repricing_ChangeProcessor');
            $changeProcessor->process($diff, $affectedListingsProducts->getData(array('id', 'status')));
        }

        // ---------------------------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Account was saved'));

        /** @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $routerParams = array('id' => $account->getId());
        if ($wizardHelper->isActive('installationAmazon') &&
            $wizardHelper->getStep('installationAmazon') == 'account') {
            $routerParams['wizard'] = true;
        }

        $this->_redirectUrl(
            Mage::helper('M2ePro')->getBackUrl(
                'list',
                array(),
                array('edit' => $routerParams)
            )
        );
    }

    public function isReadyForDocumentGenerationAction()
    {
        $id = $this->getRequest()->getParam('account_id');
        $newStoreMode = $this->getRequest()->getParam('new_store_mode');
        $newStoreId = $this->getRequest()->getParam('new_store_id');

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($id);

        if ($id && !$account->getId()) {
            $this->getResponse()->setBody('You should provide correct parameters.');
            return;
        }

        $result = true;

        $accountStoreMode = $account->getChildObject()->getSetting(
            'magento_orders_settings',
            array('listing', 'store_mode'),
            Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT
        );
        $accountStoreId = $account->getChildObject()->getMagentoOrdersListingsStoreId();

        if ($accountStoreMode != $newStoreMode) {
            $accountStoreMode = $newStoreMode;
            $accountStoreId = $newStoreId;
        }

        if ($accountStoreMode == Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM) {
            $storeData = Mage::getStoreConfig('general/store_information', $accountStoreId);

            if (empty($storeData['name']) || empty($storeData['address'])) {
                $result = false;
            }
        } else {
            /** @var Ess_M2ePro_Model_Resource_Listing_Collection $listingCollection */
            $listingCollection = Mage::getModel('M2ePro/Listing')->getCollection();
            $listingCollection->addFieldToFilter('account_id', $account->getId());

            if ($listingCollection->getSize() > 0) {
                foreach ($listingCollection->getItems() as $listing) {
                    /** @var Ess_M2ePro_Model_Listing $listing */
                    $storeData = Mage::getStoreConfig('general/store_information', $listing->getStoreId());

                    if (empty($storeData['name']) || empty($storeData['address '])) {
                        $result = false;
                        break;
                    }
                }
            } else {
                $storeData = Mage::getStoreConfig('general/store_information');

                if (empty($storeData['name']) || empty($storeData['address'])) {
                    $result = false;
                }
            }
        }

        $this->_addJsonContent(
            array(
                'success' => true,
                'result' => $result
            )
        );
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

            $account->deleteProcessings();
            $account->deleteProcessingLocks();
            $account->deleteInstance();

            $deleted++;
        }

        $deleted && $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('%amount% record(s) were deleted.', $deleted)
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
                    $result['reason'] = $response['reason'];
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
            return 'MessageObj.addNotice(\''.$note.'\');';
        }

        $errorMessage = Mage::helper('M2ePro')->__(
            'Work with this Account is currently unavailable for the following reason: <br/> %error_message%',
            array('error_message' => $note)
        );

        return 'MessageObj.addError(\''.$errorMessage.'\');';
    }

    //########################################

    protected function addAccount($data)
    {
        if ($this->isAccountExists($data['merchant_id'], $data['marketplace_id'])) {
            throw new Ess_M2ePro_Model_Exception(
                'An account with the same Amazon Merchant ID and Marketplace already exists.'
            );
        }

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account');

        Mage::getModel('M2ePro/Amazon_Account_Builder')->build($account, $data);

        try {
            $params = $this->getDataForServer($account);

            /** @var $dispatcherObject Ess_M2ePro_Model_Amazon_Connector_Dispatcher */
            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');

            /** @var Ess_M2ePro_Model_Amazon_Connector_Account_Add_EntityRequester $connectorObj */
            $connectorObj = $dispatcherObject->getConnector(
                'account',
                'add',
                'entityRequester',
                $params,
                $account
            );
            $dispatcherObject->process($connectorObj);
            $responseData = $connectorObj->getResponseData();

            $account->getChildObject()->addData(
                array(
                    'server_hash' => $responseData['hash'],
                    'info'        => Mage::helper('M2ePro')->jsonEncode($responseData['info'])
                )
            );
            $account->getChildObject()->save();
        } catch (\Exception $exception) {
            $account->deleteInstance();

            throw $exception;
        }

        return $account;
    }

    protected function updateAccount($id, $data)
    {
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getObject('Account', $id);

        $oldData = array_merge($account->getOrigData(), $account->getChildObject()->getOrigData());

        Mage::getModel('M2ePro/Amazon_Account_Builder')->build($account, $data);

        try {
            $params = $this->getDataForServer($account);

            if (!$this->isNeedSendDataToServer($params, $oldData)) {
                return $account;
            }

            /** @var $dispatcherObject Ess_M2ePro_Model_Amazon_Connector_Dispatcher */
            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');

            /** @var Ess_M2ePro_Model_Amazon_Connector_Account_Update_EntityRequester $connectorObj */
            $connectorObj = $dispatcherObject->getConnector(
                'account',
                'update',
                'entityRequester',
                $params,
                $account
            );
            $dispatcherObject->process($connectorObj);
            $responseData = $connectorObj->getResponseData();

            $account->getChildObject()->addData(
                array(
                    'info' => Mage::helper('M2ePro')->jsonEncode($responseData['info'])
                )
            );
            $account->getChildObject()->save();
        } catch (\Exception $exception) {
            Mage::getModel('M2ePro/Amazon_Account_Builder')->build($account, $oldData);

            throw $exception;
        }

        return $account;
    }

    //########################################

    protected function getDataForServer(Ess_M2ePro_Model_Account $account)
    {
        return array(
            'marketplace_id' => $account->getChildObject()->getMarketplaceId(),
            'merchant_id'    => $account->getChildObject()->getMerchantId(),
            'token'          => $account->getChildObject()->getToken(),
        );

    }

    protected function isNeedSendDataToServer($newData, $oldData)
    {
        $diff = array_diff_assoc($newData, $oldData);

        return !empty($diff);
    }

    protected function isAccountExists($merchantId, $marketplaceId)
    {
        /** @var Ess_M2ePro_Model_Resource_Account_Collection $account */
        $account = Mage::getModel('M2ePro/Amazon_Account')->getCollection()
            ->addFieldToFilter('merchant_id', $merchantId)
            ->addFieldToFilter('marketplace_id', $marketplaceId);

        return $account->getSize();
    }

    //########################################

    public function getExcludedStatesPopupHtmlAction()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Amazon_Account_Edit_Tabs_Order_ExcludedStates $block */
        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_account_edit_tabs_order_excludedStates',
            '',
            array('selected_states' => explode(',', $this->getRequest()->getParam('selected_states')))
        );

        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################
}
