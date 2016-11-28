<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Amazon_Account_RepricingController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    public function indexAction()
    {
        return $this->_redirect('*/adminhtml_common_account/index');
    }

    //########################################

    public function linkOrRegisterAction()
    {
        $accountId = $this->getRequest()->getParam('id');

        /** @var $account Ess_M2ePro_Model_Account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        $backUrl = Mage::helper('adminhtml')->getUrl(
            '*/adminhtml_common_amazon_account_repricing/link',
            array('id' => $account->getId())
        );

        /** @var $repricingAction Ess_M2ePro_Model_Amazon_Repricing_Action_Account */
        $repricingAction = Mage::getModel('M2ePro/Amazon_Repricing_Action_Account', $account);
        $serverRequestToken = $repricingAction->sendLinkActionData($backUrl);

        if ($serverRequestToken === false) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__(
                'M2E Pro cannot to connect to the Amazon Repricing Service. Please try again later.'
            ));
            return $this->indexAction();
        }

        $this->_redirectUrl(
            Mage::helper('M2ePro/Component_Amazon_Repricing')->prepareActionUrl(
                Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_ACCOUNT_LINK, $serverRequestToken
            )
        );
    }

    public function linkAction()
    {
        $accountId = $this->getRequest()->getParam('id');
        $token = $this->getRequest()->getParam('account_token');
        $email = $this->getRequest()->getParam('email');

        $status = $this->getRequest()->getParam('status');
        $messages = $this->getRequest()->getParam('messages', array());

        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        $this->addRepricingMessages($messages);

        if ($status == '1') {

            $accountRepricingModel = Mage::getModel('M2ePro/Amazon_Account_Repricing');

            $accountRepricingModel->setData(array(
                'account_id' => $accountId,
                'email' => $email,
                'token' => $token
            ));

            $accountRepricingModel->save();

            /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing_Synchronization_General */
            $repricing = Mage::getModel('M2ePro/Amazon_Repricing_Synchronization_General', $account);
            $repricing->run();
        }

        return $this->_redirectUrl($this->getUrl('*/adminhtml_common_amazon_account/edit', array(
            'id' => $accountId
        )).'#repricing');
    }

    //----------------------------------------

    public function openUnlinkPageAction()
    {
        $accountId = $this->getRequest()->getParam('id');

        /** @var $account Ess_M2ePro_Model_Account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if (!$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        $backUrl = Mage::helper('adminhtml')->getUrl(
            '*/adminhtml_common_amazon_account_repricing/unlink',
            array('id' => $account->getId())
        );

        /** @var $repricingAction Ess_M2ePro_Model_Amazon_Repricing_Action_Account */
        $repricingAction = Mage::getModel('M2ePro/Amazon_Repricing_Action_Account', $account);
        $serverRequestToken = $repricingAction->sendUnlinkActionData($backUrl);

        if ($serverRequestToken === false) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__(
                'M2E Pro cannot to connect to the Amazon Repricing Service. Please try again later.'
            ));
            return $this->indexAction();
        }

        $this->_redirectUrl(
            Mage::helper('M2ePro/Component_Amazon_Repricing')->prepareActionUrl(
                Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_ACCOUNT_UNLINK, $serverRequestToken
            )
        );
    }

    public function unlinkAction()
    {
        $accountId = $this->getRequest()->getParam('id');

        $status   = $this->getRequest()->getParam('status');
        $messages = $this->getRequest()->getParam('messages', array());

        /** @var $account Ess_M2ePro_Model_Account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Account', $accountId);

        $this->addRepricingMessages($messages);

        if ($status == '1') {

            /** @var $repricingSynchronization Ess_M2ePro_Model_Amazon_Repricing_Synchronization_General */
            $repricingSynchronization = Mage::getModel('M2ePro/Amazon_Repricing_Synchronization_General', $account);
            $repricingSynchronization->reset();

            $account->getChildObject()->getRepricing()->deleteInstance();
        }

        return $this->_redirectUrl(
            $this->getUrl('*/adminhtml_common_amazon_account/edit', array('id' => $accountId)).'#repricing'
        );
    }

    //########################################

    public function openManagementAction()
    {
        $accountId = $this->getRequest()->getParam('id');

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if (!$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        $this->_redirectUrl(Mage::helper('M2ePro/Component_Amazon_Repricing')->getManagementUrl($account));
    }

    //########################################

    private function addRepricingMessages($messages)
    {
        foreach ($messages as $message) {

            if ($message['type'] == 'notice') {
                $this->_getSession()->addNotice($message['text']);
            }

            if ($message['type'] == 'warning') {
                $this->_getSession()->addWarning($message['text']);
            }

            if ($message['type'] == 'error') {
                $this->_getSession()->addError($message['text']);
            }
        }
    }

    //########################################
}
