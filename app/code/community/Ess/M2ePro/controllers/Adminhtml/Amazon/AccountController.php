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
            ->addJs('M2ePro/Amazon/Account/Create.js')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css');
        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "accounts");
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

    public function createAction()
    {
        $accountTitle = (string)$this->getRequest()->getParam('title', '');
        $marketplaceId = (int)$this->getRequest()->getParam('marketplace_id', 0);

        if (empty($accountTitle)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'success' => false,
                        'message' => 'Title can\'t be empty.',
                    )
                )
            );
        }

        if (!$this->isAccountTitleUnique($accountTitle)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'success' => false,
                        'message' => 'Title must be unique.',
                    )
                )
            );
        }

        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = Mage::getModel('M2ePro/Marketplace')->load($marketplaceId);
        if (
            !$marketplace->getId()
            || $marketplace->getComponentMode() !== Ess_M2ePro_Helper_Component_Amazon::NICK
            || $marketplace->getStatus() !== Ess_M2ePro_Model_Marketplace::STATUS_ENABLE
        ) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'success' => false,
                        'message' => 'Unable to create account for this marketplace.',
                    )
                )
            );
        }

        try {
            $backUrl = $this->getUrl(
                '*/*/afterGetToken',
                array(
                    'marketplace_id' => $marketplaceId,
                    'title' => rawurlencode($accountTitle),
                )
            );

            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'account', 'get', 'authUrl',
                array('back_url' => $backUrl, 'marketplace' => $marketplace->getData('native_id'))
            );

            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (Exception $exception) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'success' => false,
                        'message' => Mage::helper('M2ePro')->__(
                            'The Amazon token obtaining is currently unavailable. Reason: %error_message%',
                            $exception->getMessage()
                        )
                    )
                )
            );
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'success' => true,
                    'url' => $response['url'],
                )
            )
        );
    }

    /**
     * @param string $title
     * @return bool
     */
    protected function isAccountTitleUnique($title)
    {
        /** @var Ess_M2ePro_Model_Resource_Account_Collection $accountCollection */
        $accountCollection = Mage::getModel('M2ePro/Account')->getCollection();
        $accountCollection->addFieldToFilter('title', $title);

        return !$accountCollection->getSize();
    }

    public function newAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_account_create'))
            ->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($id);

        if (!$id || !$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        Mage::helper('M2ePro/Data_Global')->setValue('license_message', $this->getLicenseMessage($account));

        Mage::helper('M2ePro/Data_Global')->setValue('model_account', $account);

        $this->_initAction()
             ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_account_edit_tabs'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_account_edit'))
             ->renderLayout();
    }

    //########################################

    public function beforeGetTokenAction()
    {
        $accountId = (int)$this->getRequest()->getParam('id', 0);
        $marketplaceId = (int)$this->getRequest()->getParam('marketplace_id', 0);

        $marketplace = Mage::getModel('M2ePro/Marketplace')->load($marketplaceId);
        try {
            $backUrl = $this->getUrl(
                '*/*/afterGetToken',
                array(
                    'marketplace_id' => $marketplaceId,
                    'account_id' => $accountId,
                )
            );

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

        $this->_redirectUrl($response['url']);
    }

    public function afterGetTokenAction()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params)) {
            return $this->indexAction();
        }

        $incorrectInput = false;
        $requiredFields = array(
            'selling_partner_id',
            'spapi_oauth_code',
            'marketplace_id'
        );

        foreach ($requiredFields as $requiredField) {
            if (!isset($params[$requiredField])) {
                $incorrectInput = true;
                break;
            }
        }

        if (!isset($params['title']) && !isset($params['account_id'])) {
            $incorrectInput = true;
        }

        if ($incorrectInput) {
            $error = Mage::helper('M2ePro')->__('The Amazon token obtaining is currently unavailable.');
            $this->_getSession()->addError($error);

            return $this->_redirect('*/*/new');
        }

        if (isset($params['title'])) {
            return $this->processNewAccount(
                (string)$params['selling_partner_id'],
                (string)$params['spapi_oauth_code'],
                rawurldecode((string)$params['title']),
                (int)$params['marketplace_id']
            );
        }

        return $this->processExistingAccount(
            (int)$params['account_id'],
            (string)$params['spapi_oauth_code'],
            (string)$params['selling_partner_id']
        );
    }

    /**
     * @param string $sellingPartnerId
     * @param string $spApiOAuthCode
     * @param string $title
     * @param int $marketplaceId
     */
    protected function processNewAccount($sellingPartnerId, $spApiOAuthCode, $title, $marketplaceId) {
        if ($this->isAccountExists($sellingPartnerId, $marketplaceId)) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'An account with the same Amazon Merchant ID and Marketplace already exists.'
                )
            );

            return $this->_redirect('*/*/index');
        }

        try {
            /** @var Ess_M2ePro_Model_Amazon_Account_Server_Create $serverCreateAccount */
            $serverCreateAccount = Mage::getModel('M2ePro/Amazon_Account_Server_Create');

            $result = $serverCreateAccount->process(
                $spApiOAuthCode,
                $sellingPartnerId,
                $marketplaceId
            );
        } catch (\Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'The Amazon access obtaining is currently unavailable.<br/>Reason: %error_message%',
                    $exception->getMessage()
                )
            );

            return $this->indexAction();
        }

        $account = $this->createAccount(
            array(
                'merchant_id' => $sellingPartnerId,
                'marketplace_id' => $marketplaceId,
                'title' => $title,
            ),
            $result
        );
        $accountId = (int)$account->getId();

        if ($accountId) {
            $this->_getSession()->addSuccess($this->__('Account was saved'));

            return $this->_redirect(
                '*/*/edit', array(
                    'id' => $accountId,
                )
            );
        }

        $this->_getSession()->addError(
            $this->__(
                'The account creation is currently unavailable.'
            )
        );

        return $this->_redirect('*/*/index');
    }

    /**
     * @param int $accountId
     * @param string $spApiOAuthCode
     * @param string $sellingPartnerId
     * @return Ess_M2ePro_Adminhtml_Amazon_AccountController|Mage_Adminhtml_Controller_Action|null
     */
    private function processExistingAccount($accountId, $spApiOAuthCode, $sellingPartnerId)
    {
        try {
            /** @var Ess_M2ePro_Model_Account $account */
            $account = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Account', $accountId);
            /** @var Ess_M2ePro_Model_Amazon_Account_Server_Update $serverUpdateAccount */
            $serverUpdateAccount = Mage::getModel('M2ePro/Amazon_Account_Server_Update');
            $serverUpdateAccount->process(
                $account->getChildObject(),
                $spApiOAuthCode,
                $sellingPartnerId
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
                'id' => $accountId,
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

        $id = (int)$this->getRequest()->getParam('id');
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getObject('Account', $id);

        if (empty($id) || !$account->getId()) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'Account does not exists.'
                )
            );

            return $this->indexAction();
        }

        $this->updateAccount($account, $data);

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

        /** @var Ess_M2ePro_Helper_Module_Wizard $wizardHelper */
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

                    if (empty($storeData['name']) || empty($storeData['address'])) {
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

    public function checkAction()
    {
        $id = $this->getRequest()->getParam('id', 0);

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($id);

        if (!$id || !$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));

            return $this->indexAction();
        }

        /** @var Ess_M2ePro_Model_Amazon_Connector_Dispatcher $dispatcherObject */
        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');

        /** @var Ess_M2ePro_Model_Amazon_Connector_Account_Check_EntityRequester $connectorObj */
        $connectorObj = $dispatcherObject->getConnector(
            'account',
            'check',
            'entityRequester',
            array('account_server_hash' => $account->getChildObject()->getServerHash())
        );

        $dispatcherObject->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        if ($responseData['status']) {
            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Amazon account token is valid.'));
        } else {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('Amazon account token is invalid. Please re-get token.')
            );
        }

        $this->_forward('edit');
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

        $deleted = 0;

        try {
            /** @var Ess_M2ePro_Model_Account $account */
            foreach ($accounts as $account) {
                /** @var Ess_M2ePro_Model_Amazon_Account_DeleteManager $deleteManager */
                $deleteManager = Mage::getModel('M2ePro/Amazon_Account_DeleteManager');
                $deleteManager->process($account);
                $deleted++;
            }

            $deleted && $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__('%amount% account(s) were deleted.', $deleted)
            );
        } catch (\Exception $exception) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__($exception->getMessage()));
        }

        $this->_redirect('*/*/index');
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

    // ----------------------------------------

    private function createAccount($data, Ess_M2ePro_Model_Amazon_Account_Server_Create_Result $serverResult)
    {
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account');
        /** @var Ess_M2ePro_Model_Amazon_Account_Builder $builder */
        $builder = Mage::getModel('M2ePro/Amazon_Account_Builder');

        $accountData = array_merge(
            $builder->getDefaultData(),
            $data
        );
        $accountData['magento_orders_settings']['tax']['excluded_states'] = implode(
            ',',
            $accountData['magento_orders_settings']['tax']['excluded_states']
        );
        $accountData['magento_orders_settings']['tax']['excluded_countries'] = implode(
            ',',
            $accountData['magento_orders_settings']['tax']['excluded_countries']
        );

        $builder->build($account, $accountData);

        /** @var Ess_M2ePro_Model_Amazon_Account $amazonAccount */
        $amazonAccount = $account->getChildObject();
        $amazonAccount->setServerHash($serverResult->getHash());
        $amazonAccount->setInfo($serverResult->getInfo());

        $amazonAccount->save();

        return $account;
    }

    private function updateAccount(Ess_M2ePro_Model_Account $account, $data)
    {
        /** @var Ess_M2ePro_Model_Amazon_Account_Builder $builder */
        $builder = Mage::getModel('M2ePro/Amazon_Account_Builder');
        $builder->build($account, $data);

        return $account;
    }

    // ----------------------------------------

    private function isAccountExists($merchantId, $marketplaceId)
    {
        /** @var Ess_M2ePro_Model_Resource_Account_Collection $collection */
        $collection = Mage::getModel('M2ePro/Amazon_Account')->getCollection()
            ->addFieldToFilter('merchant_id', $merchantId)
            ->addFieldToFilter('marketplace_id', $marketplaceId);

        return $collection->getSize();
    }

    // ----------------------------------------

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

    public function getExcludedCountriesPopupHtmlAction()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Amazon_Account_Edit_Tabs_Order_ExcludedCountries $block */
        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_account_edit_tabs_order_excludedCountries',
            '',
            array('selected_countries' => explode(',', $this->getRequest()->getParam('selected_countries')))
        );

        $this->getResponse()->setBody($block->toHtml());
    }
}
