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

        $model = $this->updateAccount($this->getRequest()->getParam('id'), $post);

        // Repricing
        // ---------------------------------------
        if (!empty($post['repricing']) && $model->getChildObject()->isRepricing()) {

            /** @var Ess_M2ePro_Model_Amazon_Account_Repricing $repricingModel */
            $repricingModel = $model->getChildObject()->getRepricing();

            $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Account_Repricing_SnapshotBuilder');
            $snapshotBuilder->setModel($repricingModel);

            $repricingOldData = $snapshotBuilder->getSnapshot();

            Mage::getModel('M2ePro/Amazon_Account_Repricing_Builder')->build($repricingModel, $post['repricing']);

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
            return 'MessageObj.addNotice(\''.$note.'\');';
        }

        $errorMessage = Mage::helper('M2ePro')->__(
            'Work with this Account is currently unavailable for the following reason: <br/> %error_message%',
            array('error_message' => $note)
        );

        return 'MessageObj.addError(\''.$errorMessage.'\');';
    }

    //########################################

    protected function updateAccount($id, $data)
    {
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account');

        if (isset($id)) {
            $model->loadInstance($id);
            $model->setData('isEdit', true);
        } else {
            $model->setData('isEdit', false);
        }

        Mage::getModel('M2ePro/Amazon_Account_Builder')->build($model, $data);

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
