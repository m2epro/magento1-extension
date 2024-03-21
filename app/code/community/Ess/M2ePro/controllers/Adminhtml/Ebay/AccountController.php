<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Account as EbayAccount;

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
            ->addJs('M2ePro/Ebay/Account.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addCss('M2ePro/css/Plugin/DropDown.css');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "configuration");

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

        Mage::helper('M2ePro/Data_Global')->setValue('license_message', $this->getLicenseMessage($account));

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $account);

        $this->_initAction();

        $this->setPageHelpLink(null, null, "account-set-up");

        $this->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs'))
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_account_edit'))
            ->renderLayout();
    }

    //########################################

    public function beforeGetSellApiTokenAction()
    {
        $accountId = $this->getRequest()->getParam('id', 0);
        $accountMode = (int)$this->getRequest()->getParam('mode', Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX);

        $mode = $accountMode == Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION ? 'production' : 'sandbox';

        try {
            $backUrl = $this->getUrl(
                '*/*/afterGetSellApiToken',
                array('mode' => $accountMode, 'id' => $accountId, '_current' => true)
            );

            $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'account', 'get', 'grantAccessUrl',
                array('back_url' => $backUrl, 'mode' => $mode),
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

        $this->_redirectUrl($response['url']);
    }

    public function afterGetSellApiTokenAction()
    {
        $authCode = base64_decode($this->getRequest()->getParam('code'));
        $authCode === null && $this->_redirect('*/*/index');

        $accountId = (int)$this->getRequest()->getParam('id');
        $mode = (int)$this->getRequest()->getParam('mode');

        if ($accountId === 0) {
            try {
                $account = Mage::getModel('Ess_M2ePro_Model_Ebay_Account_Create')->create($authCode, $mode);
                $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('eBay Account has been added'));
            } catch (Exception $exception) {
                $this->_getSession()->addError($exception->getMessage());
                return $this->_redirect('*/*/index');
            }
        } else {
            try {
                /** @var Ess_M2ePro_Model_Account $account */
                $account = Mage::helper('M2ePro/Component_Ebay')->getObject('Account', $accountId);
                Mage::getModel('Ess_M2ePro_Model_Ebay_Account_Update')->updateCredentials($account, $authCode, $mode);
                $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('OAuth Token has been updated'));
            } catch (Exception $exception) {
                $this->_getSession()->addError($exception->getMessage());
                return $this->_redirect('*/*/edit', array('id' => $account->getId(), '_current' => true));
            }
        }

        if (Mage::helper('M2ePro/Component_Ebay_Category_Store')->isExistDeletedCategories()) {
            $url = $this->getUrl('*/adminhtml_ebay_category/index', array('filter' => base64_encode('state=0')));

            $this->_getSession()->addWarning(
                Mage::helper('M2ePro')->__(
                    'Some eBay Store Categories were deleted from eBay. Click '.
                    '<a target="_blank" href="%url%">here</a> to check.', $url
                )
            );
        }

        $this->_redirect('*/*/edit', array('id' => $account->getId(), '_current' => true));
        // ---------------------------------------
    }

    //########################################

    public function saveAction()
    {
        if (!$data = $this->getRequest()->getPost()) {
            return $this->indexAction();
        }

        $id = $this->getRequest()->getParam('id');

        $account = Mage::helper('M2ePro/Component_Ebay')->getObject('Account', $id);

        try {
            Mage::getModel('M2ePro/Ebay_Account_Builder')->build($account, $data);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'The Ebay access obtaining is currently unavailable.<br/>Reason: %error_message%',
                    $exception->getMessage()
                )
            );

            return $this->indexAction();
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Account was saved'));

        $this->_redirectUrl(
            Mage::helper('M2ePro')->getBackUrl(
                'list', array(), array('edit' => array('id' => $account->getId()))
            )
        );
    }

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
                /** @var Ess_M2ePro_Model_Ebay_Account_DeleteManager $deleteManager */
                $deleteManager = Mage::getModel('M2ePro/Ebay_Account_DeleteManager');
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
}
