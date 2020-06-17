<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Account as Account;

class Ess_M2ePro_Adminhtml_Ebay_AccountController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Accounts'));

        $this->getLayout()->getBlock('head')
             ->setCanLoadExtJs(true)
             ->addJs('M2ePro/Plugin/ActionColumn.js')
             ->addJs('M2ePro/Grid.js')
             ->addJs('M2ePro/Account.js')
             ->addJs('M2ePro/AccountGrid.js')
             ->addJs('M2ePro/Ebay/Account.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "x/MQAJAQ");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/configuration'
        );
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
        $id = (int)$this->getRequest()->getParam('id');

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Ebay')->getModel('Account')->load($id);

        if (!$account->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->_redirect('*/adminhtml_ebay_account/index');
        }

        if ($id) {
            Mage::helper('M2ePro/Data_Global')->setValue('license_message', $this->getLicenseMessage($account));
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $account);

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/OwAJAQ");

        $this->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs'))
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
        $mode = $accountMode == Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION ? 'production' : 'sandbox';

        try {
            $backUrl = $this->getUrl('*/*/afterGetToken', array('_current' => true));

            $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'account', 'get', 'grandAccessUrl',
                array('back_url' => $backUrl, 'mode' => $mode),
                null, null, null, $mode
            );

            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
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
        $sessionId === null && $this->_redirect('*/*/index');
        // ---------------------------------------

        // Get account form data
        // ---------------------------------------
        Mage::helper('M2ePro/Data_Session')->setValue('get_token_account_token_session', $sessionId);
        // ---------------------------------------

        // Goto account add or edit page
        // ---------------------------------------
        $accountId = (int)Mage::helper('M2ePro/Data_Session')->getValue('get_token_account_id', true);

        if ($accountId == 0) {
            $this->_redirect('*/*/new', array('_current' => true));
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

    public function beforeGetSellApiTokenAction()
    {
        // Get and save form data
        // ---------------------------------------
        $accountId = $this->getRequest()->getParam('id', 0);
        $accountMode = (int)$this->getRequest()->getParam('mode', Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX);
        // ---------------------------------------

        // Get and save session id
        // ---------------------------------------
        $mode = $accountMode == Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION ? 'production' : 'sandbox';

        try {
            $backUrl = $this->getUrl('*/*/afterGetSellApiToken', array('_current' => true));

            $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'account', 'get', 'grandAccessUrl',
                array('back_url' => $backUrl, 'mode' => $mode, 'auth_type' => 'oauth'),
                null, null, null, $mode
            );

            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            $error = 'The eBay Sell token obtaining is currently unavailable.<br/>Reason: %error_message%';
            $error = Mage::helper('M2ePro')->__($error, $exception->getMessage());

            $this->_getSession()->addError($error);

            return $this->indexAction();
        }

        Mage::helper('M2ePro/Data_Session')->setValue('get_sell_api_token_account_id', $accountId);
        Mage::helper('M2ePro/Data_Session')->setValue('get_sell_api_token_account_mode', $accountMode);

        $this->_redirectUrl($response['url']);
        // ---------------------------------------
    }

    public function afterGetSellApiTokenAction()
    {
        // Get eBay session id
        // ---------------------------------------
        $sessionId = base64_decode($this->getRequest()->getParam('code'));
        $sessionId === null && $this->_redirect('*/*/index');
        // ---------------------------------------

        // Get account form data
        // ---------------------------------------
        Mage::helper('M2ePro/Data_Session')->setValue('get_sell_api_token_account_token_session', $sessionId);
        // ---------------------------------------

        // Goto account add or edit page
        // ---------------------------------------
        $accountId = (int)Mage::helper('M2ePro/Data_Session')->getValue('get_sell_api_token_account_id', true);

        if ($accountId <= 0) {
            $this->_redirect('*/*/index');
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Sell API token was successfully obtained'));
        $this->_redirect('*/*/edit', array('id' => $accountId, '_current' => true));
        // ---------------------------------------
    }

    //########################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->indexAction();
        }

        $id = $this->getRequest()->getParam('id');

        $data = $this->sendDataToServer($id, $post);

        $id = $this->updateAccount($id, $data);

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Account was successfully saved'));

        $this->_redirectUrl(
            Mage::helper('M2ePro')->getBackUrl(
                'list', array(), array('edit'=>array('id'=>$id, 'update_ebay_store' => null, '_current'=>true))
            )
        );
    }

    public function deleteAction()
    {
        $ids = $this->getRequestIds();

        if (empty($ids)) {
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
                $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
                $connectorObj = $dispatcherObject->getVirtualConnector(
                    'account', 'delete', 'entity',
                    array(), null, null, $account->getId()
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

    // ---------------------------------------

    protected function sendDataToServer($id, $data)
    {
        // Add or update server
        // ---------------------------------------

        $requestData = array(
            'mode'          => $data['mode'] == Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION ?
                               'production' : 'sandbox',
            'token_session' => $data['token_session']
        );

        if (isset($data['sell_api_token_session'])) {
            $requestData['sell_api_token_session'] = $data['sell_api_token_session'];
        }

        $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');

        if ((bool)$id) {

            /** @var Ess_M2ePro_Model_Account $model */
            $model = Mage::helper('M2ePro/Component_Ebay')->getObject('Account', $id);
            $requestData['title'] = $model->getTitle();

            $connectorObj = $dispatcherObject->getVirtualConnector(
                'account', 'update', 'entity', $requestData, null, null, $id
            );
        } else {
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'account', 'add', 'entity', $requestData, null, null, null
            );
        }

        try {
            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (Exception $e) {
            $response = array();
        }

        if (!isset($response['token_expired_date'])) {
            throw new Ess_M2ePro_Model_Exception('Account is not added or updated. Try again later.');
        }

        isset($response['hash']) && $data['server_hash'] = $response['hash'];
        isset($response['info']['UserID']) && $data['user_id'] = $response['info']['UserID'];

        $data['info'] = Mage::helper('M2ePro')->jsonEncode($response['info']);
        $data['token_expired_date'] = $response['token_expired_date'];

        if (isset($response['sell_api_token_expired_date'])) {
            $data['sell_api_token_expired_date'] = $response['sell_api_token_expired_date'];
        }

        return $data;
    }

    protected function updateAccount($id, $data)
    {
        // Change token
        // ---------------------------------------
        $isChangeTokenSession = false;
        if ((bool)$id) {
            $oldTokenSession = Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Account', $id)
                ->getChildObject()
                ->getTokenSession();
            $newTokenSession = $data['token_session'];
            if ($newTokenSession != $oldTokenSession) {
                $isChangeTokenSession = true;
            }
        } else {
            $isChangeTokenSession = true;
        }

        // Add or update model
        // ---------------------------------------
        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Account');
        if ($id !== null) {
            $model->load($id);
        }
        Mage::getModel('M2ePro/Ebay_Account_Builder')->build($model, $data);

        $id = $model->getId();

        // Update eBay store
        // ---------------------------------------
        if ($isChangeTokenSession || (int)$this->getRequest()->getParam('update_ebay_store')) {
            $ebayAccount = $model->getChildObject();
            $ebayAccount->updateEbayStoreInfo();

            if (Mage::helper('M2ePro/Component_Ebay_Category_Store')->isExistDeletedCategories()) {
                $url = $this->getUrl('*/adminhtml_ebay_category/index', array('filter' => base64_encode('state=0')));

                $this->_getSession()->addWarning(
                    Mage::helper('M2ePro')->__(
                        'Some eBay Store Categories were deleted from eBay. Click '.
                        '<a target="_blank" href="%url%">here</a> to check.', $url
                    )
                );
            }
        }

        // Update User Preferences
        // ---------------------------------------
        $model->getChildObject()->updateUserPreferences();

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
        $model = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account', $id);

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'ok' => (bool)$model->getChildObject()->hasFeedbackTemplate()
                )
            )
        );
    }

    public function feedbackTemplateEditAction()
    {
        $id = $this->getRequest()->getParam('id');
        $accountId = $this->getRequest()->getParam('account_id');
        $body = $this->getRequest()->getParam('body');

        $data = array('account_id'=>$accountId,'body'=>$body);

        $model = Mage::getModel('M2ePro/Ebay_Feedback_Template');
        $id === null && $model->setData($data);
        $id !== null && $model->load($id)->addData($data);
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

    protected function getLicenseMessage(Ess_M2ePro_Model_Account $account)
    {
        try {
            $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'account', 'get', 'info', array(
                'account' => $account->getChildObject()->getServerHash(),
                'channel' => Ess_M2ePro_Helper_Component_Ebay::NICK,
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
}
