<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

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
            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/Account.js')
            ->addJs('M2ePro/AccountGrid.js')
            ->addJs('M2ePro/Account.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Walmart/Account.js')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css');
        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "walmart-integration");

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
                    'M2ePro/adminhtml_walmart_configuration',
                    '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Walmart_Configuration_Tabs::TAB_ID_ACCOUNT)
                )
            )->renderLayout();
    }

    public function accountGridAction()
    {
        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_account_grid')->toHtml();
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
        if (!$data = $this->getRequest()->getPost()) {
            $this->_redirect('*/*/index');
        }

        $id = $this->getRequest()->getParam('id');

        try {
            $account = $id ? $this->updateAccount($id, $data) : $this->addAccount($data);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $error = 'The Walmart access obtaining is currently unavailable.<br/>Reason: %error_message%';
            $error = Mage::helper('M2ePro')->__($error, $exception->getMessage());

            $this->_getSession()->addError($error);

            return $this->indexAction();
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Account was saved'));

        /** @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $routerParams = array('id' => $account->getId());
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

        $deleted = 0;

        try {
            /** @var Ess_M2ePro_Model_Account $account */
            foreach ($accounts as $account) {
                /** @var Ess_M2ePro_Model_Walmart_Account_DeleteManager $deleteManager */
                $deleteManager = Mage::getModel('M2ePro/Walmart_Account_DeleteManager');
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

    public function checkAuthAction()
    {
        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' =>true)));
        $consumerId = $this->getRequest()->getParam('consumer_id', false);
        $privateKey = $this->getRequest()->getParam('private_key', false);
        $clientId = $this->getRequest()->getParam('client_id', false);
        $clientSecret = $this->getRequest()->getParam('client_secret', false);
        $marketplaceId = $this->getRequest()->getParam('marketplace_id', false);
        $result = array('result' => null);

        /** @var $marketplaceObject Ess_M2ePro_Model_Marketplace */
        $marketplaceObject = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Marketplace',
            $marketplaceId
        );

        if ($marketplaceId == Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA &&
            $consumerId && $privateKey) {
            $requestData = array(
                'marketplace' => $marketplaceObject->getNativeId(),
                'consumer_id' => $consumerId,
                'private_key' => $privateKey,
            );
        } elseif ($marketplaceId != Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA &&
            $clientId && $clientSecret) {
            $requestData = array(
                'marketplace'   => $marketplaceObject->getNativeId(),
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
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
                'account',
                'get',
                'info',
                array(
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
        $note = $response['info']['note'];

        if ($status) {
            return 'MessageObj.addNotice(\'' . $note . '\');';
        }

        $errorMessage = Mage::helper('M2ePro')->__(
            'Work with this Account is currently unavailable for the following reason: <br/> %error_message%',
            array('error_message' => $note)
        );

        return 'MessageObj.addError(\'' . $errorMessage . '\');';
    }

    //########################################

    protected function addAccount($data)
    {
        $searchField = empty($data['client_id']) ? 'consumer_id' : 'client_id';
        $searchValue = empty($data['client_id']) ? $data['consumer_id'] : $data['client_id'];

        if ($this->isAccountExists($searchField, $searchValue)) {
            throw new Ess_M2ePro_Model_Exception(
                'An account with the same Walmart Client ID already exists.'
            );
        }

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Walmart')->getModel('Account');

        Mage::getModel('M2ePro/Walmart_Account_Builder')->build($account, $data);

        try {
            $params = $this->getDataForServer($data);

            /** @var $dispatcherObject Ess_M2ePro_Model_Walmart_Connector_Dispatcher */
            $dispatcherObject = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

            /** @var Ess_M2ePro_Model_Walmart_Connector_Account_Add_EntityRequester $connectorObj */
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
        $account = Mage::helper('M2ePro/Component_Walmart')->getObject('Account', $id);

        $oldData = array_merge($account->getOrigData(), $account->getChildObject()->getOrigData());

        Mage::getModel('M2ePro/Walmart_Account_Builder')->build($account, $data);

        try {
            $params = $this->getDataForServer($data);

            if (!$this->isNeedSendDataToServer($params, $oldData)) {
                return $account;
            }

            /** @var $dispatcherObject Ess_M2ePro_Model_Walmart_Connector_Dispatcher */
            $dispatcherObject = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

            /** @var Ess_M2ePro_Model_Walmart_Connector_Account_Update_EntityRequester $connectorObj */
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
            Mage::getModel('M2ePro/Walmart_Account_Builder')->build($account, $oldData);

            throw $exception;
        }

        return $account;
    }

    //########################################

    protected function getDataForServer($data)
    {
        $params = array(
            'marketplace_id' => (int)$data['marketplace_id']
        );

        if ($data['marketplace_id'] == Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_US) {
            $params['client_id'] = $data['client_id'];
            $params['client_secret'] = $data['client_secret'];
        } else {
            $params['consumer_id'] = $data['consumer_id'];
            $params['private_key'] = $data['private_key'];
        }

        return $params;
    }

    protected function isNeedSendDataToServer($newData, $oldData)
    {
        $diff = array_diff_assoc($newData, $oldData);

        return !empty($diff);
    }

    protected function isAccountExists($search, $value)
    {
        /** @var Ess_M2ePro_Model_Resource_Account_Collection $collection */
        $collection = Mage::getModel('M2ePro/Walmart_Account')->getCollection()
            ->addFieldToFilter($search, $value);

        return $collection->getSize();
    }

    //########################################
}
